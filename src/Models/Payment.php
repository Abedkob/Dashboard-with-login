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
     * Get the database connection
     */
    public function getDb(): PDO
    {
        return $this->db;
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

    /**
     * Get data for DataTables with server-side processing
     */
    public function getDataTableData(int $start, int $length, string $search, string $clientFilter, array $order): array
    {
        // Build base query
        $baseQuery = "SELECT p.*, pl.name as client_name 
                      FROM payments p 
                      LEFT JOIN projects_list pl ON p.client_id = pl.id 
                      WHERE p.is_deleted = 0";

        // Build WHERE conditions
        $whereConditions = [];
        $params = [];

        // Apply search filter
        if (!empty($search)) {
            $whereConditions[] = "(p.id LIKE ? OR pl.name LIKE ? OR p.method LIKE ? OR p.note LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        // Apply client filter
        if (!empty($clientFilter)) {
            $whereConditions[] = "p.client_id = ?";
            $params[] = $clientFilter;
        }

        // Combine WHERE conditions
        $whereClause = '';
        if (!empty($whereConditions)) {
            $whereClause = ' AND ' . implode(' AND ', $whereConditions);
        }

        // Get total records count
        $totalRecordsQuery = "SELECT COUNT(*) FROM payments WHERE is_deleted = 0";
        $stmt = $this->db->query($totalRecordsQuery);
        $totalRecords = $stmt->fetchColumn();

        // Get filtered count
        $filteredQuery = "SELECT COUNT(*) FROM payments p LEFT JOIN projects_list pl ON p.client_id = pl.id WHERE p.is_deleted = 0" . $whereClause;
        $stmt = $this->db->prepare($filteredQuery);
        $stmt->execute($params);
        $filteredRecords = $stmt->fetchColumn();

        // Ordering
        $orderColumn = $order[0]['column'] ?? 0;
        $orderDir = $order[0]['dir'] ?? 'desc';
        $orderColumnName = '';

        // Map DataTables column index to database column
        $columns = [
            0 => 'p.id',
            1 => 'pl.name',
            2 => 'p.amount',
            3 => 'p.method',
            4 => 'p.payment_date',
            5 => 'p.note',
            6 => 'p.created_at'
        ];

        if (isset($columns[$orderColumn])) {
            $orderColumnName = $columns[$orderColumn];
            $orderDir = strtoupper($orderDir) === 'ASC' ? 'ASC' : 'DESC';
        }

        // Build final query
        $query = $baseQuery . $whereClause;
        if (!empty($orderColumnName)) {
            $query .= " ORDER BY $orderColumnName $orderDir";
        }
        $query .= " LIMIT ?, ?";

        $params[] = (int) $start;
        $params[] = (int) $length;

        // Execute query
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'totalRecords' => $totalRecords,
            'filteredRecords' => $filteredRecords,
            'data' => $data
        ];
    }
    public function getAllClients(): array
    {
        $stmt = $this->db->query("SELECT id, name FROM projects_list ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Get unique clients for filter dropdown
     */
    public function getClientsForFilter(): void
    {
        $sql = "SELECT DISTINCT p.client_id, pl.name as client_name 
                FROM payments p 
                LEFT JOIN projects_list pl ON p.client_id = pl.id 
                WHERE p.is_deleted = 0 AND pl.name IS NOT NULL 
                ORDER BY pl.name";

        $stmt = $this->db->query($sql);
        $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode($clients);
        exit;
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
                SET client_id = :client_id, amount = :amount, method = :method, 
                    payment_date = :payment_date, note = :note, updated_at = NOW()
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
