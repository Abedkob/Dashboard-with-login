<?php
namespace App\Models;

use PDO;

class Payment
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Create a new payment record
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO payments (client_id, amount, method, payment_date, note, created_at, is_deleted)
                VALUES (:client_id, :amount, :method, :payment_date, :note, NOW(), 0)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':client_id' => $data['client_id'],
            ':amount' => $data['amount'],
            ':method' => $data['method'],
            ':payment_date' => $data['payment_date'],
            ':note' => $data['note'] ?? null
        ]);
    }
    public function searchClients(): void
    {
        $term = $_GET['term'] ?? '';

        // Use parameterized query with two placeholders
        $sql = "SELECT id, name 
            FROM projects_list 
            WHERE name LIKE :term1 OR id LIKE :term2 
            LIMIT 10";

        $stmt = $this->db->prepare($sql);

        // Bind parameters safely
        $stmt->execute([
            ':term1' => "%$term%",
            ':term2' => "%$term%"
        ]);

        $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: application/json'); // Ensure JSON response
        echo json_encode($clients);
        exit;
    }


    /**
     * Get all payments (active only)
     */
    public function getAll(): array
    {
        $sql = "SELECT 
                payments.*, 
                projects_list.name as Client 
            FROM 
                payments
            LEFT JOIN 
                projects_list ON payments.client_id = projects_list.id
            WHERE 
                payments.is_deleted = 0 
            ORDER BY 
                payments.created_at DESC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array
    {
        $sql = "SELECT * FROM payments WHERE id = :id AND is_deleted = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE payments 
                SET client_id = :client_id, amount = :amount, method = :method, payment_date = :payment_date, note = :note
                WHERE id = :id AND is_deleted = 0";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':client_id' => $data['client_id'],
            ':amount' => $data['amount'],
            ':method' => $data['method'],
            ':payment_date' => $data['payment_date'],
            ':note' => $data['note'] ?? null,
            ':id' => $id
        ]);
    }

    public function deletePayment($id): bool
    {
        $sql = "UPDATE payments SET is_deleted = 1 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function getAvailableClient(): void
    {
        $sql = "SELECT id, name 
        FROM projects_list 
        ORDER BY name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($clients);
        exit;
    }

    public function validateClient(int $clientId): array
    {
        $sql = "SELECT id, name FROM projects_list WHERE id = :client_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':client_id' => $clientId]);
        $client = $stmt->fetch(PDO::FETCH_ASSOC);

        return $client
            ? ['valid' => true, 'client_name' => $client['name']]
            : ['valid' => false];
    }
}
