<?php
namespace App\Controllers;

require_once __DIR__ . '/../Models/User.php';

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
                'cookie_lifetime' => 3600, // 1 hour
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
            . "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; "
            . "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; "
            . "font-src 'self' https://cdn.jsdelivr.net; "
            . "img-src 'self' data:;");
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
        error_log("handleLogin() called");

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo "This endpoint only accepts POST requests";
            exit;
        }

        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($username) || empty($password)) {
            error_log("Empty username or password");
            $_SESSION['login_error'] = 'Username and password are required';
            header('Location: ' . url('login'));
            exit;
        }

        $user = $this->userModel->findByUsername($username);

        if (!$user) {
            error_log("User not found: $username");
            $_SESSION['login_error'] = 'Invalid username or password';
            header('Location: ' . url('login'));
            exit;
        }

        if (!$this->userModel->verifyPassword($password, $user->password)) {
            error_log("Password mismatch for user: $username");
            $_SESSION['login_error'] = 'Invalid username or password';
            header('Location: ' . url('login'));
            exit;
        }

        // Login success
        error_log("Successful login for user: $username");
        $_SESSION['user_id'] = $user->user_id;
        $_SESSION['user_level'] = $user->level;

        // Log into activity_logs table
        $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        $description = "User {$username} has successfully logged in.";

        $stmt = $this->db->prepare("
            INSERT INTO activity_logs (user_id, action, description, ip_address)
            VALUES (:user_id, :action, :description, :ip_address)
        ");
        $stmt->execute([
            ':user_id' => $user->user_id,
            ':action' => 'logged in',
            ':description' => $description,
            ':ip_address' => $ipAddress
        ]);

        header('Location: ' . url('dashboard'));
        exit;
    }

    public function logout()
    {
        $this->startSession();

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
