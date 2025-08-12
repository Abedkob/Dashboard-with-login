<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';

// Enable error reporting & logging (disable display in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../log/php-error.log');
error_reporting(E_ALL);

// Set your fixed base path here:
$basePath = '/Practice_php/public';

// URL helper function
function url($path = '')
{
    global $basePath;
    return $basePath . '/' . ltrim($path, '/');
}

// Load DB config and connect
try {
    $dbConfig = require __DIR__ . '/../config/database.php';
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset=utf8mb4",
        $dbConfig['username'],
        $dbConfig['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    $pdo->query("SELECT 1")->fetchColumn();
    $GLOBALS['pdo'] = $pdo;
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed: " . $e->getMessage());
}

function isAuthenticated()
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])
        && isset($_SESSION['2fa_verified']) && $_SESSION['2fa_verified'] === true;
}

function requireAuth()
{
    if (!isAuthenticated()) {
        header('Location: ' . url('login'));
        exit;
    }
}

// Get the requested URI path
$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove any trailing slash for consistent matching
$request = rtrim($request, '/');

// Routing with full basePath prefix
switch ($request) {
    case $basePath:
    case $basePath . '/':
    case $basePath . '/login':
        if (isAuthenticated()) {
            header('Location: ' . url('dashboard'));
            exit;
        }
        require __DIR__ . '/../src/Controllers/AuthController.php';
        (new App\Controllers\AuthController($pdo))->showLogin();
        break;

    case $basePath . '/login/submit':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo "Method Not Allowed";
            exit;
        }
        require __DIR__ . '/../src/Controllers/AuthController.php';
        (new App\Controllers\AuthController($pdo))->handleLogin();
        break;

    case $basePath . '/dashboard':
        requireAuth();
        require __DIR__ . '/../src/Controllers/DashboardController.php';
        (new App\Controllers\DashboardController($pdo))->index();
        break;

    case $basePath . '/activation-codes':
        requireAuth();
        require __DIR__ . '/../src/Controllers/ActivationCodeController.php';
        (new App\Controllers\ActivationCodeController($pdo))->index();
        break;

    case $basePath . '/activation-codes/create':
        requireAuth();
        require __DIR__ . '/../src/Controllers/ActivationCodeController.php';
        $controller = new App\Controllers\ActivationCodeController($pdo);
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $controller->create();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->store();
        }
        break;

    case $basePath . '/export':
        requireAuth();
        require __DIR__ . '/../src/Controllers/ActivationCodeController.php';
        (new App\Controllers\ActivationCodeController($pdo))->export();
        break;

    case $basePath . '/activation-codes/edit':
        requireAuth();
        require __DIR__ . '/../src/Controllers/ActivationCodeController.php';
        $controller = new App\Controllers\ActivationCodeController($pdo);
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $controller->edit($_GET['id'] ?? null);
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->update($_GET['id'] ?? null);
        }
        break;

    case $basePath . '/activation-codes/delete':
        requireAuth();
        require __DIR__ . '/../src/Controllers/ActivationCodeController.php';
        (new App\Controllers\ActivationCodeController($pdo))->delete($_GET['id'] ?? null);
        break;

    case $basePath . '/activation-codes/datatable':
        requireAuth();
        require __DIR__ . '/../src/Controllers/ActivationCodeController.php';
        (new App\Controllers\ActivationCodeController($pdo))->datatable();
        break;

    case $basePath . '/activation-codes/bulk-update':
        requireAuth();
        require __DIR__ . '/../src/Controllers/ActivationCodeController.php';
        (new App\Controllers\ActivationCodeController($pdo))->bulkUpdate();
        break;

    case $basePath . '/activation-codes/bulk-delete':
        requireAuth();
        require __DIR__ . '/../src/Controllers/ActivationCodeController.php';
        (new App\Controllers\ActivationCodeController($pdo))->bulkDelete();
        break;

    case $basePath . '/logout':
        require __DIR__ . '/../src/Controllers/AuthController.php';
        (new App\Controllers\AuthController($pdo))->logout();
        break;

    // Payments Manager Routes
    case $basePath . '/payments-manager':
        requireAuth();
        require __DIR__ . '/../src/Controllers/PaymentsController.php';
        (new App\Controllers\PaymentsController($pdo))->index();
        break;

    case $basePath . '/payments-manager/create':
        requireAuth();
        require __DIR__ . '/../src/Controllers/PaymentsController.php';
        $controller = new App\Controllers\PaymentsController($pdo);
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $controller->createForm();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->create($_POST);
        }
        break;

    case $basePath . '/payments-manager/datatable':
        requireAuth();
        require __DIR__ . '/../src/Controllers/PaymentsController.php';
        (new App\Controllers\PaymentsController($pdo))->datatable();
        break;

    case $basePath . '/payments-manager/get-clients':
        requireAuth();
        require __DIR__ . '/../src/Controllers/PaymentsController.php';
        (new App\Controllers\PaymentsController($pdo))->getClients();
        break;

    case $basePath . '/payments-manager/edit':
        requireAuth();
        require __DIR__ . '/../src/Controllers/PaymentsController.php';
        $controller = new App\Controllers\PaymentsController($pdo);
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $controller->edit();
        }
        break;

    case $basePath . '/payments-manager/update':
        requireAuth();
        require __DIR__ . '/../src/Controllers/PaymentsController.php';
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing payment ID']);
            exit;
        }
        (new App\Controllers\PaymentsController($pdo))->update((int) $id);
        break;

    case $basePath . '/payments-manager/delete':
        requireAuth();
        require __DIR__ . '/../src/Controllers/PaymentsController.php';
        $id = $_POST['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing payment ID']);
            exit;
        }
        (new App\Controllers\PaymentsController($pdo))->delete((int) $id);
        break;

    case $basePath . '/payments-manager/available-clients':
        requireAuth();
        require __DIR__ . '/../src/Controllers/PaymentsController.php';
        (new App\Controllers\PaymentsController($pdo))->getAvailableClients();
        break;

    case $basePath . '/payments-manager/validate-client':
        requireAuth();
        require __DIR__ . '/../src/Controllers/PaymentsController.php';
        (new App\Controllers\PaymentsController($pdo))->validateClient();
        break;

    case $basePath . '/payments-manager/search-clients':
        requireAuth();
        require __DIR__ . '/../src/Controllers/PaymentsController.php';
        (new App\Controllers\PaymentsController($pdo))->searchClients();
        break;

    case $basePath . '/payments-manager/get-payment':
        requireAuth();
        require __DIR__ . '/../src/Controllers/PaymentsController.php';
        (new App\Controllers\PaymentsController($pdo))->getPayment();
        break;

    case $basePath . '/payments-manager/create-payment-for-license-form':
        requireAuth();
        require __DIR__ . '/../src/Controllers/PaymentsController.php';
        (new App\Controllers\PaymentsController($pdo))->createPaymentForLicenseForm();
        break;

    case $basePath . '/payments-manager/create-payment-for-license':
        requireAuth();
        require __DIR__ . '/../src/Controllers/PaymentsController.php';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new App\Controllers\PaymentsController($pdo))->createPaymentForLicense($_POST);
        } else {
            http_response_code(405);
            echo "Method Not Allowed";
        }
        break;

    // Logs Routes
    case $basePath . '/logs':
        requireAuth();
        require __DIR__ . '/../src/Controllers/LogsController.php';
        (new App\Controllers\LogsController($pdo))->index();
        break;

    case $basePath . '/logs/datatable':
        requireAuth();
        require __DIR__ . '/../src/Controllers/LogsController.php';
        (new App\Controllers\LogsController($pdo))->datatable();
        break;

    case $basePath . '/logs/get-actions':
        requireAuth();
        require __DIR__ . '/../src/Controllers/LogsController.php';
        (new App\Controllers\LogsController($pdo))->getActions();
        break;

    case $basePath . '/logs/get-description':
        requireAuth();
        require __DIR__ . '/../src/Controllers/LogsController.php';
        (new App\Controllers\LogsController($pdo))->getDescription();
        break;

    // Two Factor Authentication Routes
    case $basePath . '/2fa':
        require __DIR__ . '/../src/Controllers/AuthController.php';
        (new App\Controllers\AuthController($pdo))->show2FA();
        break;

    case $basePath . '/2fa/verify':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo "Method Not Allowed";
            exit;
        }
        require __DIR__ . '/../src/Controllers/AuthController.php';
        (new App\Controllers\AuthController($pdo))->verify2FA();
        break;

    // UserActionController routes - FIXED ROUTING
    case $basePath . '/user-actions/track-page-view':
        requireAuth();
        require __DIR__ . '/../src/Controllers/UserActionController.php';
        (new App\Controllers\UserActionController($pdo))->trackPageView($_GET['page'] ?? '');
        break;

    case $basePath . '/user-actions/activity-logs':
        requireAuth();
        require __DIR__ . '/../src/Controllers/UserActionController.php';
        (new App\Controllers\UserActionController($pdo))->showActivityLogs();
        break;

    case $basePath . '/user-actions/track-action':
        requireAuth();
        require __DIR__ . '/../src/Controllers/UserActionController.php';
        (new App\Controllers\UserActionController($pdo))->trackAction(
            $_GET['page'] ?? '',
            $_GET['action'] ?? ''
        );
        break;

    // FIXED: Create form route - now matches JavaScript call
    case $basePath . '/user-actions/create-form':
        requireAuth();
        require __DIR__ . '/../src/Controllers/UserActionController.php';
        (new App\Controllers\UserActionController($pdo))->createForm();
        break;

    case $basePath . '/user-actions/store':
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            exit;
        }
        require __DIR__ . '/../src/Controllers/UserActionController.php';
        (new App\Controllers\UserActionController($pdo))->store();
        break;

    case $basePath . '/user-actions/update':
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo "Method Not Allowed";
            exit;
        }
        $id = $_POST['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo "Missing ID";
            exit;
        }
        require __DIR__ . '/../src/Controllers/UserActionController.php';
        (new App\Controllers\UserActionController($pdo))->update((int) $id, $_POST);
        break;

    case $basePath . '/user-actions/delete':
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo "Method Not Allowed";
            exit;
        }
        require __DIR__ . '/../src/Controllers/UserActionController.php';
        (new App\Controllers\UserActionController($pdo))->delete();
        break;

    case $basePath . '/user-actions/get-user-actions':
        requireAuth();
        require __DIR__ . '/../src/Controllers/UserActionController.php';
        (new App\Controllers\UserActionController($pdo))->getUserActions();
        break;

    case $basePath . '/user-actions/update-user-actions':
        requireAuth();
        require __DIR__ . '/../src/Controllers/UserActionController.php';
        (new App\Controllers\UserActionController($pdo))->updateUserActions();
        break;

    default:
        http_response_code(404);
        echo "Page not found: " . htmlspecialchars($request);
        break;
}
?>