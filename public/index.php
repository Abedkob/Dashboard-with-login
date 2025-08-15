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

// Permission checking function
function requirePermission($route)
{
    global $pdo;

    if (!isAuthenticated()) {
        header('Location: ' . url('login'));
        exit;
    }

    // Create UserActionController instance for permission checking
    require_once __DIR__ . '/../src/Controllers/UserActionController.php';
    $userActionController = new App\Controllers\UserActionController($pdo);

    if (!$userActionController->checkRoutePermission($route)) {
        http_response_code(403);
        echo "Access denied. You don't have permission to access this page.";
        exit;
    }
}

// Get the requested URI path
$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove any trailing slash for consistent matching
$request = rtrim($request, '/');
$requestMethod = $_SERVER['REQUEST_METHOD'];
// Remove base path from request for permission checking
$routeForPermission = str_replace($basePath, '', $request);
if ($routeForPermission === '') {
    $routeForPermission = '/';
}

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
        requirePermission('/dashboard');
        require __DIR__ . '/../src/Controllers/DashboardController.php';
        (new App\Controllers\DashboardController($pdo))->index();
        break;

    case $basePath . '/activation-codes':
        requireAuth();
        requirePermission('/activation-codes');
        require __DIR__ . '/../src/Controllers/ActivationCodeController.php';
        (new App\Controllers\ActivationCodeController($pdo))->index();
        break;

    case $basePath . '/activation-codes/create':
        requireAuth();
        requirePermission('/activation-codes/create');
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
        requirePermission('/activation-codes');
        require __DIR__ . '/../src/Controllers/ActivationCodeController.php';
        (new App\Controllers\ActivationCodeController($pdo))->export();
        break;

    case $basePath . '/activation-codes/edit':
        requireAuth();
        requirePermission('/activation-codes/edit');
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
        requirePermission('/activation-codes/delete');
        require __DIR__ . '/../src/Controllers/ActivationCodeController.php';
        (new App\Controllers\ActivationCodeController($pdo))->delete($_GET['id'] ?? null);
        break;

    case $basePath . '/activation-codes/datatable':
        requireAuth();
        requirePermission('/activation-codes');
        require __DIR__ . '/../src/Controllers/ActivationCodeController.php';
        (new App\Controllers\ActivationCodeController($pdo))->datatable();
        break;

    case $basePath . '/activation-codes/bulk-update':
        requireAuth();
        requirePermission('/activation-codes/edit');
        require __DIR__ . '/../src/Controllers/ActivationCodeController.php';
        (new App\Controllers\ActivationCodeController($pdo))->bulkUpdate();
        break;

    case $basePath . '/activation-codes/bulk-delete':
        requireAuth();
        requirePermission('/activation-codes/delete');
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
        requirePermission('/payments-manager');
        require __DIR__ . '/../src/Controllers/PaymentsController.php';
        (new App\Controllers\PaymentsController($pdo))->index();
        break;

    case $basePath . '/payments-manager/create':
        requireAuth();
        requirePermission('/payments-manager/create');
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
        requirePermission('/payments-manager');
        require __DIR__ . '/../src/Controllers/PaymentsController.php';
        (new App\Controllers\PaymentsController($pdo))->datatable();
        break;

    case $basePath . '/payments-manager/get-clients':
        requireAuth();
        requirePermission('/payments-manager');
        require __DIR__ . '/../src/Controllers/PaymentsController.php';
        (new App\Controllers\PaymentsController($pdo))->getClients();
        break;

    case $basePath . '/payments-manager/edit':
        requireAuth();
        requirePermission('/payments-manager/edit');
        require __DIR__ . '/../src/Controllers/PaymentsController.php';
        $controller = new App\Controllers\PaymentsController($pdo);
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $controller->edit();
        }
        break;

    case $basePath . '/payments-manager/update':
        requireAuth();
        requirePermission('/payments-manager/edit');
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
        requirePermission('/payments-manager/delete');
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
        requirePermission('/payments-manager');
        require __DIR__ . '/../src/Controllers/PaymentsController.php';
        (new App\Controllers\PaymentsController($pdo))->getAvailableClients();
        break;

    case $basePath . '/payments-manager/validate-client':
        requireAuth();
        requirePermission('/payments-manager');
        require __DIR__ . '/../src/Controllers/PaymentsController.php';
        (new App\Controllers\PaymentsController($pdo))->validateClient();
        break;

    case $basePath . '/payments-manager/search-clients':
        requireAuth();
        requirePermission('/payments-manager');
        require __DIR__ . '/../src/Controllers/PaymentsController.php';
        (new App\Controllers\PaymentsController($pdo))->searchClients();
        break;

    case $basePath . '/payments-manager/get-payment':
        requireAuth();
        requirePermission('/payments-manager');
        require __DIR__ . '/../src/Controllers/PaymentsController.php';
        (new App\Controllers\PaymentsController($pdo))->getPayment();
        break;

    case $basePath . '/payments-manager/create-payment-for-license-form':
        requireAuth();
        requirePermission('/payments-manager/create');
        require __DIR__ . '/../src/Controllers/PaymentsController.php';
        (new App\Controllers\PaymentsController($pdo))->createPaymentForLicenseForm();
        break;

    case $basePath . '/payments-manager/create-payment-for-license':
        requireAuth();
        requirePermission('/payments-manager/create');
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
        requirePermission('/logs');
        require __DIR__ . '/../src/Controllers/LogsController.php';
        (new App\Controllers\LogsController($pdo))->index();
        break;

    case $basePath . '/logs/datatable':
        requireAuth();
        requirePermission('/logs');
        require __DIR__ . '/../src/Controllers/LogsController.php';
        (new App\Controllers\LogsController($pdo))->datatable();
        break;

    case $basePath . '/logs/get-actions':
        requireAuth();
        requirePermission('/logs');
        require __DIR__ . '/../src/Controllers/LogsController.php';
        (new App\Controllers\LogsController($pdo))->getActions();
        break;

    case $basePath . '/logs/get-description':
        requireAuth();
        requirePermission('/logs');
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

    case $basePath . '/user-actions/create-form':
        requireAuth();
        requirePermission('/user-actions/create');
        require __DIR__ . '/../src/Controllers/UserActionController.php';
        $userActionController = new App\Controllers\UserActionController($pdo);
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $userActionController->createForm();
        } else {
            http_response_code(405);
            echo 'Method Not Allowed';
            exit;
        }
        break;

    case $basePath . '/user-actions/store-batch':
        requireAuth();
        requirePermission('/user-actions/create'); // enforce permission
        require __DIR__ . '/../src/Controllers/UserActionController.php';
        $userActionController = new App\Controllers\UserActionController($pdo); // instantiate
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userActionController->storeBatch();
        } else {
            http_response_code(405);
            echo 'Method Not Allowed';
            exit;
        }
        break;


    case $basePath . '/user-actions/track-page-view':
        requireAuth();
        require __DIR__ . '/../src/Controllers/UserActionController.php';
        (new App\Controllers\UserActionController($pdo))->trackPageView($_GET['page'] ?? '');
        break;

    case $basePath . '/user-actions/activity-logs':
        requireAuth();
        requirePermission('/user-actions/activity-logs');
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

    case $basePath . '/user-actions/delete':
        requireAuth();
        requirePermission('/user-actions/delete');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo "Method Not Allowed";
            exit;
        }
        require __DIR__ . '/../src/Controllers/UserActionController.php';
        (new App\Controllers\UserActionController($pdo))->delete();
        break;

    case $basePath . '/user-actions/permissions':
        requireAuth();
        require __DIR__ . '/../src/Controllers/UserActionController.php';
        (new App\Controllers\UserActionController($pdo))->getUserPermissions();
        break;

    default:
        http_response_code(404);
        echo "Page not found: " . htmlspecialchars($request);
        break;
}
?>