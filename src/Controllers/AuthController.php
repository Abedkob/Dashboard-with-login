<?php
namespace App\Controllers;
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Models\User;
use PDO;

class AuthController
{
    private $userModel;
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->userModel = new User($db);
        $this->startSession();
    }

    private function startSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start([
                'name' => 'AUTH_SESSID',
                'cookie_lifetime' => 3600,
                'cookie_httponly' => true,
                'cookie_secure' => isset($_SERVER['HTTPS']),
                'cookie_samesite' => 'Strict'
            ]);
        }
    }

    private function setSecurityHeaders()
    {
        header("X-Content-Type-Options: nosniff");
        header("X-Frame-Options: SAMEORIGIN");
        header("X-XSS-Protection: 1; mode=block");
        header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
        header("Content-Security-Policy: default-src 'self'; "
            . "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; "
            . "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://code.jquery.com; "
            . "font-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; "
            . "img-src 'self' data: https://api.qrserver.com;");
    }

    public function showLogin()
    {
        $this->setSecurityHeaders();
        $error = $_SESSION['login_error'] ?? null;
        unset($_SESSION['login_error']);
        require __DIR__ . '/../../views/auth/login.php';
    }

    public function handleLogin()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo "Method not allowed";
            exit;
        }

        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($username) || empty($password)) {
            $_SESSION['login_error'] = 'Username and password are required';
            header('Location: ' . url('login'));
            exit;
        }

        $user = $this->userModel->findByUsername($username);

        if (!$user || !$this->userModel->verifyPassword($password, $user->password)) {
            $_SESSION['login_error'] = 'Invalid username or password';
            header('Location: ' . url('login'));
            exit;
        }

        // Save user id and level in session
        $_SESSION['user_id'] = $user->user_id;
        $_SESSION['user_level'] = $user->level;

        $ga = new \PHPGangsta_GoogleAuthenticator();

        // If user has 2FA enabled but no secret, create one and enable
        if (empty($user->twofa_secret)) {
            $secret = $ga->createSecret();
            $this->userModel->updateTwoFASecret($user->user_id, $secret);
            $user->twofa_secret = $secret;
            $user->twofa_enabled = 1;
        }

        // Redirect to 2FA page if enabled
        if (!empty($user->twofa_enabled) && $user->twofa_enabled == 1) {
            header('Location: ' . url('2fa'));
            exit;
        }

        // Log login
        $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        $description = "User {$username} logged in.";

        $stmt = $this->db->prepare("
            INSERT INTO activity_logs (user_id, action, description, ip_address)
            VALUES (:user_id, 'logged in', :description, :ip_address)
        ");
        $stmt->execute([
            ':user_id' => $user->user_id,
            ':description' => $description,
            ':ip_address' => $ipAddress
        ]);

        header('Location: ' . url('dashboard'));
        exit;
    }

    public function show2FA()
    {
        $this->setSecurityHeaders();

        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . url('login'));
            exit;
        }

        $user = $this->userModel->find($_SESSION['user_id']);
        if (!$user) {
            header('Location: ' . url('login'));
            exit;
        }

        $ga = new \PHPGangsta_GoogleAuthenticator();

        // If user has no 2FA secret, generate and store it
        if (empty($user->twofa_secret)) {
            $secret = $ga->createSecret();
            $this->userModel->updateTwoFASecret($user->user_id, $secret);
            $user->twofa_secret = $secret;
        }

        $qrCodeUrl = $ga->getQRCodeGoogleUrl('lenovo/practice_php (' . $user->username . ')', $user->twofa_secret);

        require __DIR__ . '/../../views/auth/2fa.php';
    }

    public function verify2FA()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo "Method not allowed";
            exit;
        }

        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . url('login'));
            exit;
        }

        $user = $this->userModel->find($_SESSION['user_id']);
        if (!$user || empty($user->twofa_secret)) {
            header('Location: ' . url('login'));
            exit;
        }

        $code = trim($_POST['code'] ?? '');

        $ga = new \PHPGangsta_GoogleAuthenticator();

        $checkResult = $ga->verifyCode($user->twofa_secret, $code, 2); // 2 = 2*30sec tolerance

        if ($checkResult) {
            $_SESSION['2fa_verified'] = true;
            header('Location: ' . url('dashboard'));
            exit;
        } else {
            $_SESSION['2fa_error'] = "Invalid authentication code";
            header('Location: ' . url('2fa'));
            exit;
        }
    }

    public function logout()
    {
        $this->startSession();

        // Capture user ID before clearing session
        $userId = $_SESSION['user_id'] ?? null;

        // Get IP address (respecting proxies)
        $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';

        if ($userId) {
            // Log logout action
            $description = "User ID {$userId} logged out.";

            $stmt = $this->db->prepare("
            INSERT INTO activity_logs (user_id, action, description, ip_address)
            VALUES (:user_id, 'logged out', :description, :ip_address)
        ");
            $stmt->execute([
                ':user_id' => $userId,
                ':description' => $description,
                ':ip_address' => $ipAddress
            ]);
        }

        // Clear session data
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();

        header('Location: ' . url('login'));
        exit;
    }


}
