<?php
namespace App\Controllers;

require_once __DIR__ . '/../Models/Payment.php';
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use PDO;

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
        // Validate required fields
        if (
            empty($requestData['client_id']) || empty($requestData['amount']) ||
            empty($requestData['method']) || empty($requestData['payment_date'])
        ) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }

        $success = $this->paymentModel->create($requestData);
        if ($success) {
            echo json_encode(['message' => 'Payment created successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create payment']);
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
        $this->paymentModel->getClientsForFilter();
    }

    // Read single payment by ID
    public function show(int $id): void
    {
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
        $payment = $this->paymentModel->find($id);

        if (!$payment) {
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

        try {
            // Collect POST data
            $requestData = [
                'client_id' => $_POST['client_id'] ?? null,
                'amount' => $_POST['amount'] ?? null,
                'method' => $_POST['method'] ?? null,
                'payment_date' => $_POST['payment_date'] ?? null,
                'note' => $_POST['note'] ?? null,
            ];

            // Validate required fields
            if (
                empty($requestData['client_id']) || empty($requestData['amount']) ||
                empty($requestData['method']) || empty($requestData['payment_date'])
            ) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing required fields']);
                return;
            }

            // Check if payment exists
            $exists = $this->paymentModel->find($id);
            if (!$exists) {
                http_response_code(404);
                echo json_encode(['error' => 'Payment not found']);
                return;
            }

            // Perform update
            $success = $this->paymentModel->update($id, $requestData);
            if ($success) {
                echo json_encode(['message' => 'Payment updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to update payment']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
        }
    }

    // Soft delete payment by ID
    public function delete(int $id): void
    {
        try {
            // Check if payment exists
            $payment = $this->paymentModel->find($id);
            if (!$payment) {
                http_response_code(404);
                echo json_encode(['error' => 'Payment not found']);
                return;
            }

            // Soft delete the payment
            $success = $this->paymentModel->deletePayment($id);
            if ($success) {
                echo json_encode(['message' => 'Payment deleted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to delete payment']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
        }
    }

    // For client search
    public function searchClients(): void
    {
        // Delegate the search to the payment model
        $this->paymentModel->searchClients();
    }

    public function getAvailableClients(): void
    {
        $this->paymentModel->getAvailableClient();
    }

    public function validateClient(): void
    {
        // Get client_id from GET parameters
        $clientId = $_GET['client_id'] ?? null;
        if (!$clientId) {
            echo json_encode(['valid' => false, 'error' => 'Client ID is required']);
            exit;
        }

        // Delegate to model
        $result = $this->paymentModel->validateClient($clientId);
        echo json_encode($result);
        exit;
    }

    public function getPayment(): void
    {
        $paymentId = $_GET['id'] ?? 0;
        try {
            $payment = $this->paymentModel->find($paymentId);
            if (!$payment) {
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
            http_response_code(500);
            echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
        }
        exit;
    }
    public function createForm()
    {
        // Make PDO available globally for the view
        $GLOBALS['pdo'] = $this->paymentModel->getDb();

        // Load the create form view
        require __DIR__ . '/../../views/payments_manager/create.php';
    }
    public function getClientsJson(): void
    {
        header('Content-Type: application/json');

        try {
            $clients = $this->paymentModel->getAllClients(); // make sure this returns an array
            echo json_encode($clients);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch clients', 'details' => $e->getMessage()]);
        }
    }

}
