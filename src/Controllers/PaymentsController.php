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
        // Validate required fields (simplified)
        if (empty($requestData['client_id']) || empty($requestData['amount']) || empty($requestData['method']) || empty($requestData['payment_date'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }

        $success = $this->paymentModel->create($requestData);
        if ($success) {
            http_response_code(201);
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
    public function update(int $id, array $requestData): void
    {
        // Validate required fields (simplified)
        if (empty($requestData['client_id']) || empty($requestData['amount']) || empty($requestData['method']) || empty($requestData['payment_date'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }

        $exists = $this->paymentModel->find($id);
        if (!$exists) {
            http_response_code(404);
            echo json_encode(['error' => 'Payment not found']);
            return;
        }

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
}
