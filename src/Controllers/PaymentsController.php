<?php

namespace App\Controllers;
require_once __DIR__ . '/../Models/Payment.php';
use App\Models\Payment;

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

    // Update payment by ID (expects data from POST/PUT)
    public function update(int $id): void
    {
        // Collect POST data (the rest of the fields)
        $requestData = [
            'client_id' => $_POST['client_id'] ?? null,
            'amount' => $_POST['amount'] ?? null,
            'method' => $_POST['method'] ?? null,
            'payment_date' => $_POST['payment_date'] ?? null,
            'note' => $_POST['note'] ?? null,
        ];

        // Validate required fields (simplified)
        if (empty($requestData['client_id']) || empty($requestData['amount']) || empty($requestData['method']) || empty($requestData['payment_date'])) {
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
    }

    // Soft delete payment by ID
    public function delete(int $id): void
    {
        $exists = $this->paymentModel->find($id);
        if (!$exists) {
            http_response_code(404);
            echo json_encode(['error' => 'Payment not found']);
            return;
        }

        $success = $this->paymentModel->delete($id);
        if ($success) {
            echo json_encode(['message' => 'Payment deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete payment']);
        }
    }
    // For client sear
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


}
