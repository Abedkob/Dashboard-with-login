<?php

namespace App\Controllers;

require_once __DIR__ . '/../Models/Payment.php';
require_once __DIR__ . '/../Models/UserAction.php';

use App\Models\Payment;
use App\Models\UserAction;
use PDO;
use Exception; // Import Exception class

class PaymentsController
{
    private Payment $paymentModel;
    private UserAction $userActionModel;

    public function __construct(PDO $db)
    {
        $this->paymentModel = new Payment($db);
        $this->userActionModel = new UserAction($db);
    }

    /**
     * Check if current user has permission for a specific action
     */
    private function hasPermission(string $page, string $action): bool
    {
        // Admin users have all permissions
        if (($_SESSION['user_level'] ?? 0) === 1) {
            return true;
        }

        // Check if user is authenticated
        if (!isset($_SESSION['user_id'])) {
            return false;
        }

        $userId = (int) $_SESSION['user_id'];
        return $this->userActionModel->hasPermission($userId, $page, $action);
    }

    /**
     * Require permission - throws exception if not authorized
     */
    private function requirePermission(string $page, string $action): void
    {
        if (!$this->hasPermission($page, $action)) {
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                if (headers_sent()) {
                    echo json_encode(['error' => 'Authentication required']);
                } else {
                    header('Location: /login');
                }
                exit;
            } else {
                http_response_code(403);
                if (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false) {
                    echo json_encode(['error' => "Access denied. You don't have permission to $action payments"]);
                } else {
                    echo "Access denied. You don't have permission to $action payments.";
                }
                exit;
            }
        }
    }

    // Create a new payment (expects data from POST)
    public function create(array $requestData): void
    {
        // Check permission first
        $this->requirePermission('Payments', 'create');

        header('Content-Type: application/json'); // Ensure JSON response for AJAX
        error_log("PaymentsController::create called with data: " . print_r($requestData, true));

        // Validate required fields
        if (
            empty($requestData['client_id']) || empty($requestData['amount']) ||
            empty($requestData['method']) || empty($requestData['payment_date'])
        ) {
            error_log("Create validation failed: Missing required fields.");
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }

        try {
            $success = $this->paymentModel->create($requestData);
            if ($success) {
                error_log("Payment created successfully.");
                echo json_encode(['message' => 'Payment created successfully']);
            } else {
                error_log("Failed to create payment in model.");
                http_response_code(500);
                echo json_encode(['error' => 'Failed to create payment']);
            }
        } catch (Exception $e) {
            error_log("Server error during create: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
        }
    }

    // Read all payments
    public function index(): void
    {
        // Check permission to view payments
        $this->requirePermission('Payments', 'view');

        $payments = $this->paymentModel->getAll();

        // Pass permission variables to view
        $canCreate = $this->hasPermission('Payments', 'create');
        $canUpdate = $this->hasPermission('Payments', 'update');
        $canDelete = $this->hasPermission('Payments', 'delete');

        require __DIR__ . '/../../views/payments_manager/index.php';
    }

    // DataTable AJAX endpoint
    public function datatable(): void
    {
        // Check permission to view payments
        $this->requirePermission('Payments', 'view');

        // Get request parameters
        $draw = $_POST['draw'] ?? 1;
        $start = $_POST['start'] ?? 0;
        $length = $_POST['length'] ?? 10;
        $search = $_POST['search']['value'] ?? '';
        $clientFilter = $_POST['client_filter'] ?? '';

        error_log("PaymentsController::datatable called. Draw: {$draw}, Start: {$start}, Length: {$length}, Search: '{$search}', Client Filter: '{$clientFilter}'");

        // Get data from model
        $result = $this->paymentModel->getDataTableData($start, $length, $search, $clientFilter, $_POST['order'] ?? []);

        // Check permissions for UI elements
        $canUpdate = $this->hasPermission('Payments', 'update');
        $canDelete = $this->hasPermission('Payments', 'delete');

        // Prepare response
        $response = [
            "draw" => intval($draw),
            "recordsTotal" => $result['totalRecords'],
            "recordsFiltered" => $result['filteredRecords'],
            "data" => $result['data'],
            "permissions" => [
                'canUpdate' => $canUpdate,
                'canDelete' => $canDelete
            ]
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    // Get unique clients for filter dropdown
    public function getClients(): void
    {
        error_log("PaymentsController::getClients called.");
        $this->paymentModel->getClientsForFilter();
    }

    // Read single payment by ID
    public function show(int $id): void
    {
        error_log("PaymentsController::show called for ID: " . $id);
        $payment = $this->paymentModel->find($id);
        if ($payment) {
            echo json_encode($payment);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Payment not found']);
        }
    }

    // Show edit form
    public function edit(): void
    {
        $this->requirePermission('Payments', 'update');

        $id = $_GET['id'] ?? 0;
        error_log("PaymentsController::edit called for ID: " . $id);
        $payment = $this->paymentModel->find($id);
        if (!$payment) {
            error_log("Edit form: Payment with ID {$id} not found.");
            echo '<div class="alert alert-danger">Payment not found</div>';
            return;
        }
        // Make PDO available globally for the view
        $GLOBALS['pdo'] = $this->paymentModel->getDb();
        // Corrected path to load edit.php from views/payments_manager/
        require __DIR__ . '/../../views/payments_manager/edit.php';
    }

    // Update payment by ID (expects data from POST/PUT)
    public function update(int $id): void
    {
        $this->requirePermission('Payments', 'update');

        header('Content-Type: application/json');
        error_log("PaymentsController::update called for ID: " . $id);
        error_log("Received POST data: " . print_r($_POST, true)); // Log all POST data

        try {
            // Collect POST data
            $requestData = [
                'client_id' => $_POST['client_id'] ?? null,
                'amount' => $_POST['amount'] ?? null,
                'method' => $_POST['method'] ?? null,
                'payment_date' => $_POST['payment_date'] ?? null,
                'note' => $_POST['note'] ?? null,
            ];
            error_log("Processed requestData for update: " . print_r($requestData, true)); // Log processed data

            // Validate required fields
            if (
                empty($requestData['client_id']) || empty($requestData['amount']) ||
                empty($requestData['method']) || empty($requestData['payment_date'])
            ) {
                error_log("Update validation failed: Missing required fields.");
                http_response_code(400);
                echo json_encode(['error' => 'Missing required fields']);
                return;
            }

            // Check if payment exists
            $exists = $this->paymentModel->find($id);
            if (!$exists) {
                error_log("Payment with ID {$id} not found for update.");
                http_response_code(404);
                echo json_encode(['error' => 'Payment not found']);
                return;
            }

            // Perform update
            $success = $this->paymentModel->update($id, $requestData);
            if ($success) {
                error_log("Payment ID {$id} updated successfully.");
                echo json_encode(['message' => 'Payment updated successfully']);
            } else {
                error_log("Failed to update payment ID {$id} in model.");
                http_response_code(500);
                echo json_encode(['error' => 'Failed to update payment']);
            }
        } catch (Exception $e) {
            error_log("Server error during update for ID {$id}: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
        }
    }

    // Soft delete payment by ID
    public function delete(int $id): void
    {
        $this->requirePermission('Payments', 'delete');

        header('Content-Type: application/json'); // Ensure JSON response for AJAX
        error_log("PaymentsController::delete called for ID: " . $id);

        try {
            // Check if payment exists
            $payment = $this->paymentModel->find($id);
            if (!$payment) {
                error_log("Payment with ID {$id} not found for deletion.");
                http_response_code(404);
                echo json_encode(['error' => 'Payment not found']);
                return;
            }

            // Soft delete the payment
            $success = $this->paymentModel->deletePayment($id);
            if ($success) {
                error_log("Payment ID {$id} deleted successfully.");
                echo json_encode(['message' => 'Payment deleted successfully']);
            } else {
                error_log("Failed to delete payment ID {$id} in model.");
                http_response_code(500);
                echo json_encode(['error' => 'Failed to delete payment']);
            }
        } catch (Exception $e) {
            error_log("Server error during delete for ID {$id}: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
        }
    }

    // For client search
    public function searchClients(): void
    {
        error_log("PaymentsController::searchClients called.");
        // Delegate the search to the payment model
        $this->paymentModel->searchClients();
    }

    public function getAvailableClients(): void
    {
        error_log("PaymentsController::getAvailableClients called.");
        $this->paymentModel->getAvailableClient();
    }

    public function validateClient(): void
    {
        $clientId = $_GET['client_id'] ?? null;
        error_log("PaymentsController::validateClient called for client ID: " . $clientId);

        if (!$clientId) {
            error_log("Validate client: Client ID is required.");
            echo json_encode(['valid' => false, 'error' => 'Client ID is required']);
            exit;
        }

        // Delegate to model
        $result = $this->paymentModel->validateClient($clientId);
        error_log("Validate client result: " . print_r($result, true));
        echo json_encode($result);
        exit;
    }

    public function getPayment(): void
    {
        $paymentId = $_GET['id'] ?? 0;
        error_log("PaymentsController::getPayment called for ID: " . $paymentId);

        try {
            $payment = $this->paymentModel->find($paymentId);
            if (!$payment) {
                error_log("Get payment: Payment with ID {$paymentId} not found.");
                http_response_code(404);
                echo json_encode(['error' => 'Payment not found']);
                return;
            }
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => $payment
            ]);
        } catch (Exception $e) {
            error_log("Server error during getPayment for ID {$paymentId}: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
        }
        exit;
    }

    public function createForm()
    {
        $this->requirePermission('Payments', 'create');

        error_log("PaymentsController::createForm called.");
        // Make PDO available globally for the view
        $GLOBALS['pdo'] = $this->paymentModel->getDb();
        // Load the create form view
        require __DIR__ . '/../../views/payments_manager/create.php';
    }

    public function getClientsJson(): void
    {
        header('Content-Type: application/json');
        error_log("PaymentsController::getClientsJson called.");
        try {
            $clients = $this->paymentModel->getAllClients(); // make sure this returns an array
            echo json_encode($clients);
        } catch (Exception $e) {
            error_log("Failed to fetch clients in getClientsJson: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch clients', 'details' => $e->getMessage()]);
        }
    }

    // New: Method to load the form for creating payment for a license
    public function createPaymentForLicenseForm(): void
    {
        $this->requirePermission('Payments', 'create');

        error_log("PaymentsController::createPaymentForLicenseForm called.");
        // Make PDO available globally for the view
        $GLOBALS['pdo'] = $this->paymentModel->getDb();

        // Pass license_id (which is projects_list.id) and license_name to the view
        $licenseId = $_GET['license_id'] ?? null;
        $licenseName = $_GET['license_name'] ?? 'N/A';

        // These variables will be available in the required view
        // For the client dropdown, we need all available clients
        $availableClients = $this->paymentModel->getAllClients(); // Assuming this method exists and returns all clients

        require __DIR__ . '/../../views/payments_manager/create_payment_for_license.php';
    }

    // New: Method to handle creation of payment for a license
    public function createPaymentForLicense(array $requestData): void
    {
        $this->requirePermission('Payments', 'create');

        header('Content-Type: application/json');
        error_log("PaymentsController::createPaymentForLicense called with data: " . print_r($requestData, true));

        // Validate required fields
        if (
            empty($requestData['client_id']) || empty($requestData['amount']) ||
            empty($requestData['method']) || empty($requestData['payment_date'])
        ) {
            error_log("Create payment for license validation failed: Missing required fields.");
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }

        // Map the license_id from the request (which is projects_list.id) to client_id for the payment model
        $requestData['client_id'] = $requestData['license_id'];
        unset($requestData['license_id']); // Remove license_id as it's not a column in payments table

        try {
            $success = $this->paymentModel->create($requestData); // Use the existing create method
            if ($success) {
                error_log("Payment for license created successfully. Client ID (License ID): " . $requestData['client_id']);
                echo json_encode(['message' => 'Payment created successfully']);
            } else {
                error_log("Failed to create payment for license in model.");
                http_response_code(500);
                echo json_encode(['error' => 'Failed to create payment']);
            }
        } catch (Exception $e) {
            error_log("Server error during create payment for license: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
        }
    }
}
