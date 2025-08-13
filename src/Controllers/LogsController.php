<?php
namespace App\Controllers;

use PDO;
use Exception;
use App\Models\UserAction;

class LogsController
{
    private PDO $pdo;
    private UserAction $userActionModel;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->userActionModel = new UserAction($pdo);
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
                if ($this->isAjaxRequest()) {
                    header('Content-Type: application/json');
                    echo json_encode(['error' => 'Authentication required']);
                    exit;
                } else {
                    header('Location: /login');
                    exit;
                }
            } else {
                http_response_code(403);
                if ($this->isAjaxRequest()) {
                    header('Content-Type: application/json');
                    echo json_encode(['error' => 'Access denied. You do not have permission to perform this action.']);
                    exit;
                } else {
                    $_SESSION['errors'] = ['Access denied. You do not have permission to view activity logs.'];
                    header('Location: /dashboard');
                    exit;
                }
            }
        }
    }

    /**
     * Check if request is AJAX
     */
    private function isAjaxRequest(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    public function index(): void
    {
        try {
            // Check permission to view activity logs
            $this->requirePermission('Activity logs', 'view');

            // Pass permission variables to view
            $canView = $this->hasPermission('Activity logs', 'view');
            $canExport = $this->hasPermission('Activity logs', 'view'); // Export requires view permission

            // Load the view
            require __DIR__ . '/../../views/logs/index.php';
        } catch (Exception $e) {
            error_log("LogsController::index() - " . $e->getMessage());
            http_response_code(403);
            $_SESSION['errors'] = ['Access denied: ' . $e->getMessage()];
            header('Location: /dashboard');
            exit;
        }
    }

    public function datatable(): void
    {
        try {
            // Check permission for viewing logs
            $this->requirePermission('Activity logs', 'view');

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

            // For non-admin users, only show their own logs
            if (($_SESSION['user_level'] ?? 0) !== 1) {
                $whereConditions[] = "activity_logs.user_id = ?";
                $params[] = $_SESSION['user_id'];
            }

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

            // Action filter
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
            if (($_SESSION['user_level'] ?? 0) !== 1) {
                $countQuery .= " WHERE user_id = " . (int) $_SESSION['user_id'];
            }
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

            // Include permission information in response
            $canView = $this->hasPermission('Activity logs', 'view');

            $response = [
                "draw" => $draw,
                "recordsTotal" => $totalRecords,
                "recordsFiltered" => $filteredRecords,
                "data" => $data,
                "permissions" => [
                    'canView' => $canView,
                    'canExport' => $canView // Export requires view permission
                ]
            ];

            $json = json_encode($response);
            if ($json === false) {
                error_log("JSON encode error: " . json_last_error_msg());
                $json = json_encode([
                    "draw" => $draw,
                    "recordsTotal" => 0,
                    "recordsFiltered" => 0,
                    "data" => [],
                    "permissions" => [
                        'canView' => false,
                        'canExport' => false
                    ]
                ]);
            }

            header('Content-Type: application/json');
            echo $json;
            exit;
        } catch (Exception $ex) {
            error_log("LogsController datatable error: " . $ex->getMessage());
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode([
                "draw" => intval($_POST['draw'] ?? 0),
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => [],
                "error" => "Access denied: " . $ex->getMessage(),
                "permissions" => [
                    'canView' => false,
                    'canExport' => false
                ]
            ]);
            exit;
        }
    }

    public function getActions(): void
    {
        try {
            // Check permission to view activity logs
            $this->requirePermission('Activity logs', 'view');

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
        } catch (Exception $e) {
            error_log("LogsController::getActions() - " . $e->getMessage());
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode([
                'error' => 'Access denied',
                'message' => $e->getMessage()
            ]);
            exit;
        }
    }

    public function getDescription(): void
    {
        try {
            // Check permission to view activity logs
            $this->requirePermission('Activity logs', 'view');

            // Check if 'id' is sent via POST
            if (empty($_POST['id'])) {
                throw new Exception('Log ID is required');
            }

            $logId = intval($_POST['id']);

            // For non-admin users, ensure they can only view their own logs
            $query = "SELECT activity_logs.*, users.username AS user_name 
                     FROM activity_logs 
                     LEFT JOIN users ON activity_logs.user_id = users.user_id 
                     WHERE activity_logs.id = ?";
            $params = [$logId];

            if (($_SESSION['user_level'] ?? 0) !== 1) {
                $query .= " AND activity_logs.user_id = ?";
                $params[] = $_SESSION['user_id'];
            }

            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            $log = $stmt->fetch(PDO::FETCH_ASSOC);

            header('Content-Type: application/json');

            if ($log) {
                echo json_encode([
                    'id' => $log['id'],
                    'user' => $log['user_name'] ?? 'System',
                    'action' => $log['action'],
                    'description' => $log['description'],
                    'ip_address' => $log['ip_address'],
                    'created_at' => $log['created_at']
                ]);
            } else {
                echo json_encode([
                    'error' => 'Log not found or access denied',
                    'description' => 'No description available'
                ]);
            }
            exit;
        } catch (Exception $e) {
            error_log("LogsController::getDescription() - " . $e->getMessage());
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode([
                'error' => 'Access denied',
                'message' => $e->getMessage(),
                'description' => 'Access denied'
            ]);
            exit;
        }
    }
}
