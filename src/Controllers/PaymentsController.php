<?php

namespace App\Controllers;

require_once __DIR__ . '/../Models/Payment.php';

use App\Models\Payment;
use PDO;
use Exception; // Import Exception class

class PaymentsController
{
    private Payment $paymentModel;

    public function __construct(PDO $db)
    {
        $this->paymentModel = new Payment($db);
    }

    // Create a new payment (expects data from POST)
    public function create(array $requestData): void
    {
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
                $this->logActivity('create', $requestData['license_id'] ?? null, $requestData);
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
        $payments = $this->paymentModel->getAll();
        require __DIR__ . '/../../views/payments_manager/index.php';
    }

    // DataTable AJAX endpoint
    public function datatable(): void
    {
        // Get request parameters
        $draw = $_POST['draw'] ?? 1;
        $start = $_POST['start'] ?? 0;
        $length = $_POST['length'] ?? 10;
        $search = $_POST['search']['value'] ?? '';
        $clientFilter = $_POST['client_filter'] ?? '';

        error_log("PaymentsController::datatable called. Draw: {$draw}, Start: {$start}, Length: {$length}, Search: '{$search}', Client Filter: '{$clientFilter}'");

        // Get data from model
        $result = $this->paymentModel->getDataTableData($start, $length, $search, $clientFilter, $_POST['order'] ?? []);

        // Prepare response
        $response = [
            "draw" => intval($draw),
            "recordsTotal" => $result['totalRecords'],
            "recordsFiltered" => $result['filteredRecords'],
            "data" => $result['data']
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
                $this->logActivity('update', $id, $requestData);
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
                $this->logActivity('delete', $id);
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
        error_log("PaymentsController::createPaymentForLicenseForm called.");

        // Validate license_id exists
        $licenseId = $_GET['license_id'] ?? null;
        if (!$licenseId) {
            error_log("Error: Missing license_id parameter");
            header('Location: /payments'); // Redirect if no license_id
            exit;
        }

        // Get license details for validation
        try {
            $stmt = $this->paymentModel->getDb()->prepare("
            SELECT id, name FROM projects_list WHERE id = ?
        ");
            $stmt->execute([$licenseId]);
            $license = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$license) {
                error_log("Error: Invalid license_id: {$licenseId}");
                header('Location: /payments'); // Redirect if invalid license
                exit;
            }

            // Make data available to view
            $GLOBALS['pdo'] = $this->paymentModel->getDb();
            $licenseName = $license['name'] ?? 'N/A';
            $availableClients = $this->paymentModel->getAllClients();

            // Log the form access
            $this->logActivity('view_payment_form', $licenseId, [
                'license_name' => $licenseName
            ]);

            require __DIR__ . '/../../views/payments_manager/create_payment_for_license.php';

        } catch (\PDOException $e) {
            error_log("Database error in createPaymentForLicenseForm: " . $e->getMessage());
            header('Location: /payments?error=db_error');
            exit;
        }
    }

    // New: Method to handle creation of payment for a license
    public function createPaymentForLicense(array $requestData): void
    {
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

    private function logActivity($actionType, $licenseId = null, $requestData = []): void
    {

        // Sanitize action type
        $actionMap = [
            'create' => 'Create Payment',
            'update' => 'Update Payment',
            'delete' => 'Delete Payment',
            'view_payment_form' => 'View Payment Form',
            'create_license_payment' => 'Create License Payment',
            1 => 'Create Payment',
            2 => 'Update Payment',
            3 => 'Delete Payment'
        ];

        $action = $actionMap[strtolower($actionType)] ?? 'Unknown Action';

        // Get client IP safely
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        foreach (['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR'] as $key) {
            if (!empty($_SERVER[$key])) {
                $ipAddress = is_array($_SERVER[$key])
                    ? $_SERVER[$key][0]
                    : explode(',', $_SERVER[$key])[0];
                break;
            }
        }

        // Build description safely
        $descriptionParts = [];
        if ($licenseId !== null) {
            $descriptionParts[] = "License ID: " . htmlspecialchars($licenseId, ENT_QUOTES);
        }

        if (!empty($requestData)) {
            $sanitizedData = array_map(function ($value) {
                return is_scalar($value) ? htmlspecialchars($value, ENT_QUOTES) : '[complex data]';
            }, $requestData);
            $descriptionParts[] = "Data: " . substr(json_encode($sanitizedData), 0, 500);
        }

        $description = implode(' | ', $descriptionParts) ?: 'No additional info';

        try {
            $stmt = $this->paymentModel->getDb()->prepare("
            INSERT INTO activity_logs 
            (user_id, action, description, ip_address, created_at)
            VALUES 
            (:user_id, :action, :description, :ip_address, NOW())
        ");

            $stmt->execute([
                ':user_id' => $_SESSION['user_id'],
                ':action' => $action,
                ':description' => $description,
                ':ip_address' => $ipAddress
            ]);

            error_log("Activity logged: {$action} for license {$licenseId}");

        } catch (\Exception $e) {
            error_log("Failed to log activity: " . $e->getMessage());
            // Consider adding error handling or notification here
        }
    }

}
