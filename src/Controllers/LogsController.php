<?php
namespace App\Controllers;

use PDO;
use Exception;

class LogsController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function index(): void
    {
        // Load the view
        require __DIR__ . '/../../views/logs/index.php';
    }

    public function datatable(): void
    {
        try {
            // Read POST parameters safely
            $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
            $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
            $length = isset($_POST['length']) ? intval($_POST['length']) : 10;
            $search = $_POST['search']['value'] ?? '';
            $orderColumn = $_POST['order'][0]['column'] ?? 0;
            $orderDir = strtoupper($_POST['order'][0]['dir'] ?? 'DESC');

            // Map column indexes to DB columns
            $columns = [
                0 => 'activity_logs.id',
                1 => 'users.username',
                2 => 'activity_logs.action',
                3 => 'activity_logs.description',
                4 => 'activity_logs.ip_address',
                5 => 'activity_logs.created_at'
            ];

            $baseQuery = "SELECT 
            activity_logs.*,
            users.username AS user_name
            FROM activity_logs
            LEFT JOIN users ON activity_logs.user_id = users.user_id";

            $whereConditions = [];
            $params = [];

            // Search filter
            if (!empty($search)) {
                $whereConditions[] = "(
                activity_logs.action LIKE ? OR 
                activity_logs.description LIKE ? OR 
                activity_logs.ip_address LIKE ? OR
                users.username LIKE ?
            )";
                $searchTerm = "%$search%";
                $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            }

            // Date filters
            if (!empty($_POST['date_from'])) {
                $whereConditions[] = "activity_logs.created_at >= ?";
                $params[] = $_POST['date_from'];
            }
            if (!empty($_POST['date_to'])) {
                $whereConditions[] = "activity_logs.created_at <= ?";
                $params[] = $_POST['date_to'] . ' 23:59:59';
            }

            // **Add action filter here**
            if (!empty($_POST['action_filter'])) {
                $whereConditions[] = "activity_logs.action = ?";
                $params[] = $_POST['action_filter'];
            }

            $whereClause = '';
            if (!empty($whereConditions)) {
                $whereClause = ' WHERE ' . implode(' AND ', $whereConditions);
            }

            // Total records count (no filters)
            $countQuery = "SELECT COUNT(*) FROM activity_logs";
            $totalRecords = (int) $this->pdo->query($countQuery)->fetchColumn();

            // Filtered records count
            $filteredCountQuery = "SELECT COUNT(*) FROM activity_logs LEFT JOIN users ON activity_logs.user_id = users.user_id" . $whereClause;
            $stmt = $this->pdo->prepare($filteredCountQuery);
            $stmt->execute($params);
            $filteredRecords = (int) $stmt->fetchColumn();

            // Ordering
            $orderBy = " ORDER BY activity_logs.id DESC"; // default order
            if (isset($columns[$orderColumn])) {
                $col = $columns[$orderColumn];
                $dir = ($orderDir === 'ASC') ? 'ASC' : 'DESC';
                $orderBy = " ORDER BY $col $dir";
            }

            // Main query with filters, order, and limit
            $query = $baseQuery . $whereClause . $orderBy . " LIMIT " . $start . ", " . $length;

            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Format data for DataTables
            $data = [];
            foreach ($rows as $row) {
                $data[] = [
                    'id' => $row['id'],
                    'user' => $row['user_name'] ?? 'System',
                    'action' => $row['action'],
                    'description' => mb_strimwidth($row['description'], 0, 100, '...'),
                    'ip_address' => $row['ip_address'],
                    'created_at' => date('Y-m-d H:i:s', strtotime($row['created_at'])),
                    'full_description' => $row['description']
                ];
            }

            $response = [
                "draw" => $draw,
                "recordsTotal" => $totalRecords,
                "recordsFiltered" => $filteredRecords,
                "data" => $data
            ];

            $json = json_encode($response);
            if ($json === false) {
                error_log("JSON encode error: " . json_last_error_msg());
                $json = json_encode([
                    "draw" => $draw,
                    "recordsTotal" => 0,
                    "recordsFiltered" => 0,
                    "data" => []
                ]);
            }

            header('Content-Type: application/json');
            echo $json;
            exit;
        } catch (Exception $ex) {
            error_log("LogsController datatable error: " . $ex->getMessage());
            header('Content-Type: application/json');
            echo json_encode([
                "draw" => intval($_POST['draw'] ?? 0),
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => [],
                "error" => "Server error"
            ]);
            exit;
        }
    }

    public function getActions(): void
    {
        // The list of actions you want to show (static, as per your description)
        $actions = [
            'logged in',
            'logged out',
            'Update Payment',
            'UPDATE_LICENSE',
            'DELETE_LICENSE',
            'Delete Payment',
            'CREATE_LICENSE',
            'Create Payment'
        ];

        header('Content-Type: application/json');
        echo json_encode($actions);
        exit;
    }


    // New method to get descriptions (sample simple query)
    public function getDescription(): void
    {
        try {
            // Check if 'id' is sent via POST
            if (empty($_POST['id'])) {
                throw new Exception('Log ID is required');
            }

            $logId = intval($_POST['id']);

            // Prepare and execute the query to get description of this log only
            $stmt = $this->pdo->prepare("SELECT description FROM activity_logs WHERE id = ?");
            $stmt->execute([$logId]);

            $log = $stmt->fetch(PDO::FETCH_ASSOC);

            header('Content-Type: application/json');

            if ($log && isset($log['description'])) {
                echo json_encode(['description' => $log['description']]);
            } else {
                echo json_encode(['description' => 'No description available']);
            }
            exit;
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'error' => 'Failed to fetch description',
                'message' => $e->getMessage()
            ]);
            exit;
        }
    }

}


