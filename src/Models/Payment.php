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

    /**
     * Get all payments (active only)
     */
    public function getAll(): array
    {
        $sql = "SELECT * FROM payments WHERE is_deleted = 0 ORDER BY created_at DESC";
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

    public function delete(int $id): bool
    {
        $sql = "UPDATE payments SET is_deleted = 1 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}
