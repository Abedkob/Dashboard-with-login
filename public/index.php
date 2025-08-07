<?php
// index.php - Front Controller
session_start();

// Enable error reporting & logging (disable display in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../log/php-error.log');
error_reporting(E_ALL);

// Detect Base Path dynamically
$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');

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
    // Test connection
    $pdo->query("SELECT 1")->fetchColumn();
    // Make PDO globally available
    $GLOBALS['pdo'] = $pdo;
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage()); // Log DB connection errors
    die("Database connection failed: " . $e->getMessage());
}

// Authentication helper
function isAuthenticated()
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireAuth()
{
    if (!isAuthenticated()) {
        header('Location: ' . url('login'));
        exit;
    }
}

// Parse request URI
$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove base path from request
if (strpos($request, $basePath) === 0) {
    $request = substr($request, strlen($basePath));
}
$request = '/' . trim($request, '/');

// Debug log
error_log("Routing request: " . $request);

// Routing
switch ($request) {
    case '/':
    case '/login':
        if (isAuthenticated()) {
            header('Location: ' . url('dashboard'));
            exit;
        }
        require __DIR__ . '/../src/Controllers/AuthController.php';
        (new App\Controllers\AuthController($pdo))->showLogin();
        break;
    case '/login/submit':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo "Method Not Allowed";
            exit;
        }
        require __DIR__ . '/../src/Controllers/AuthController.php';
        (new App\Controllers\AuthController($pdo))->handleLogin();
        break;
    case '/dashboard':
        requireAuth();
        require __DIR__ . '/../src/Controllers/DashboardController.php';
        (new App\Controllers\DashboardController($pdo))->index();
        break;
    case '/activation-codes':
        requireAuth();
        require __DIR__ . '/../src/Controllers/ActivationCodeController.php';
        (new App\Controllers\ActivationCodeController($pdo))->index();
        break;
    case '/activation-codes/create':
        requireAuth();
        require __DIR__ . '/../src/Controllers/ActivationCodeController.php';
        $controller = new App\Controllers\ActivationCodeController($pdo);
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $controller->create();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->store();
        }
        break;
    case '/export':
        requireAuth();
        require __DIR__ . '/../src/Controllers/ActivationCodeController.php';
        (new App\Controllers\ActivationCodeController($pdo))->export();
        break;
    case '/activation-codes/edit':
        requireAuth();
        require __DIR__ . '/../src/Controllers/ActivationCodeController.php';
        $controller = new App\Controllers\ActivationCodeController($pdo);
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $controller->edit($_GET['id'] ?? null);
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->update($_GET['id'] ?? null);
        }
        break;
    case '/activation-codes/delete':
        requireAuth();
        require __DIR__ . '/../src/Controllers/ActivationCodeController.php';
        (new App\Controllers\ActivationCodeController($pdo))->delete($_GET['id'] ?? null);
        break;
    case '/activation-codes/datatable':
        requireAuth();
        require __DIR__ . '/../src/Controllers/ActivationCodeController.php';
        (new App\Controllers\ActivationCodeController($pdo))->datatable();
        break;
    case '/activation-codes/bulk-update':
        requireAuth();
        require __DIR__ . '/../src/Controllers/ActivationCodeController.php';
        (new App\Controllers\ActivationCodeController($pdo))->bulkUpdate();
        break;
    case '/activation-codes/bulk-delete':
        requireAuth();
        require __DIR__ . '/../src/Controllers/ActivationCodeController.php';
        (new App\Controllers\ActivationCodeController($pdo))->bulkDelete();
        break;
    case '/logout':
        require __DIR__ . '/../src/Controllers/AuthController.php';
        (new App\Controllers\AuthController($pdo))->logout();
        break;

    // ===== PAYMENTS MANAGER ROUTES =====
    case '/payments-manager':
        requireAuth();
        require __DIR__ . '/../src/Controllers/PaymentsController.php';
        (new App\Controllers\PaymentsController($pdo))->index();
        break;
    case '/payments-manager/create':
        requireAuth();
        require __DIR__ . '/../src/Controllers/PaymentsController.php';
        $controller = new App\Controllers\PaymentsController($pdo);
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $controller->createForm(); // This should load your form
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->create($_POST);
        }
        break;
    case '/payments-manager/datatable':
        requireAuth();
        require __DIR__ . '/../src/Controllers/PaymentsController.php';
        (new App\Controllers\PaymentsController($pdo))->datatable();
        break;
    case '/payments-manager/get-clients':
        requireAuth();
        require __DIR__ . '/../src/Controllers/PaymentsController.php';
        (new App\Controllers\PaymentsController($pdo))->getClients();
        break;
    case '/payments-manager/edit':
        requireAuth();
        require __DIR__ . '/../src/Controllers/PaymentsController.php';
        $controller = new App\Controllers\PaymentsController($pdo);
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $controller->edit(); // This method will load the view
        }
        break;
    case '/payments-manager/update':
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
    case '/payments-manager/delete':
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
    // Additional payment routes for your existing methods
    case '/payments-manager/available-clients':
        requireAuth();
        require __DIR__ . '/../src/Controllers/PaymentsController.php';
        (new App\Controllers\PaymentsController($pdo))->getAvailableClients();
        break;
    case '/payments-manager/validate-client':
        requireAuth();
        require __DIR__ . '/../src/Controllers/PaymentsController.php';
        (new App\Controllers\PaymentsController($pdo))->validateClient();
        break;
    case '/payments-manager/search-clients':
        requireAuth();
        require __DIR__ . '/../src/Controllers/PaymentsController.php';
        (new App\Controllers\PaymentsController($pdo))->searchClients();
        break;
    case '/payments-manager/get-payment':
        requireAuth();
        require __DIR__ . '/../src/Controllers/PaymentsController.php';
        (new App\Controllers\PaymentsController($pdo))->getPayment();
        break;

    // New: Routes for creating payment for a license
    case '/payments-manager/create-payment-for-license-form':
        requireAuth();
        require __DIR__ . '/../src/Controllers/PaymentsController.php';
        (new App\Controllers\PaymentsController($pdo))->createPaymentForLicenseForm();
        break;
    case '/payments-manager/create-payment-for-license':
        requireAuth();
        require __DIR__ . '/../src/Controllers/PaymentsController.php';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new App\Controllers\PaymentsController($pdo))->createPaymentForLicense($_POST);
        } else {
            http_response_code(405);
            echo "Method Not Allowed";
        }
        break;

    default:
        http_response_code(404);
        echo "Page not found: " . htmlspecialchars($request);
        break;
}
