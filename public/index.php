<?php
// index.php - Front Controller

session_start();

// Enable error reporting & logging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../log/php-error.log');
error_reporting(E_ALL);

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

    // Test connection
    $pdo->query("SELECT 1")->fetchColumn();
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Function to check if user is authenticated
function isAuthenticated()
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Function to redirect to login if not authenticated
function requireAuth()
{
    if (!isAuthenticated()) {
        header('Location: /Practice_php/public/login');
        exit;
    }
}

// Parse request URI
$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = '/Practice_php/public';
if (strpos($request, $basePath) === 0) {
    $request = substr($request, strlen($basePath));
}
$request = '/' . trim($request, '/');

error_log("Routing request: " . $request);

// Routing
switch ($request) {
    case '/':
    case '/login':
        // If already logged in, redirect to dashboard
        if (isAuthenticated()) {
            header('Location: /Practice_php/public/dashboard');
            exit;
        }
        require __DIR__ . '/../src/Controllers/AuthController.php';
        $controller = new App\Controllers\AuthController($pdo);
        $controller->showLogin();
        break;

    case '/login/submit':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo "Method Not Allowed";
            exit;
        }
        require __DIR__ . '/../src/Controllers/AuthController.php';
        $controller = new App\Controllers\AuthController($pdo);
        $controller->handleLogin();
        break;

    case '/dashboard':
        requireAuth(); // Check authentication before proceeding
        require __DIR__ . '/../src/Controllers/DashboardController.php';
        $controller = new App\Controllers\DashboardController($pdo);
        $controller->index();
        break;

    case '/activation-codes':
        requireAuth();
        require __DIR__ . '/../src/Controllers/ActivationCodeController.php';
        $controller = new App\Controllers\ActivationCodeController($pdo);
        $controller->index();
        break;

    case '/activation-codes/create':
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            require __DIR__ . '/../src/Controllers/ActivationCodeController.php';
            $controller = new App\Controllers\ActivationCodeController($pdo);
            $controller->create();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require __DIR__ . '/../src/Controllers/ActivationCodeController.php';
            $controller = new App\Controllers\ActivationCodeController($pdo);
            $controller->store();
        }
        break;

    case '/export':
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'GET' || $_SERVER['REQUEST_METHOD'] === 'POST') {
            require __DIR__ . '/../src/Controllers/ActivationCodeController.php';
            $controller = new App\Controllers\ActivationCodeController($pdo);
            $controller->export();
        }
        break;

    case '/activation-codes/edit':
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            require __DIR__ . '/../src/Controllers/ActivationCodeController.php';
            $controller = new App\Controllers\ActivationCodeController($pdo);
            $controller->edit($_GET['id']);
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require __DIR__ . '/../src/Controllers/ActivationCodeController.php';
            $controller = new App\Controllers\ActivationCodeController($pdo);
            $controller->update($_GET['id']);
        }
        break;

    case '/activation-codes/delete':
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require __DIR__ . '/../src/Controllers/ActivationCodeController.php';
            $controller = new App\Controllers\ActivationCodeController($pdo);
            $controller->delete($_GET['id']);
        }
        break;

    case '/logout':
        require __DIR__ . '/../src/Controllers/AuthController.php';
        $controller = new App\Controllers\AuthController($pdo);
        $controller->logout();
        break;
    case '/activation-codes/datatable':
        requireAuth();
        require __DIR__ . '/../src/Controllers/ActivationCodeController.php';
        $controller = new App\Controllers\ActivationCodeController($pdo);
        $controller->datatable();
        break;
    case '/activation-codes/bulk-update':
        requireAuth();
        require __DIR__ . '/../src/Controllers/ActivationCodeController.php';
        $controller = new App\Controllers\ActivationCodeController($pdo);
        $controller->bulkUpdate();
        break;

    case '/activation-codes/bulk-delete':
        requireAuth();
        require __DIR__ . '/../src/Controllers/ActivationCodeController.php';
        $controller = new App\Controllers\ActivationCodeController($pdo);
        $controller->bulkDelete();
        break;

    default:
        http_response_code(404);
        echo "Page not found";
        break;
}