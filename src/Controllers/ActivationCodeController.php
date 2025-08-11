<?php

namespace App\Controllers;

class ActivationCodeController
{
    private $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function index()
    {
        // Get pagination parameters
        $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        // Get filter parameters
        $search = trim($_GET['search'] ?? '');
        $status = $_GET['status'] ?? '';
        $sort = $_GET['sort'] ?? 'valid_to';
        $order = strtoupper($_GET['order'] ?? 'ASC');

        // Validate sort column
        $allowedSortColumns = ['valid_to', 'name', 'created_at', 'license', 'id'];
        if (!in_array($sort, $allowedSortColumns)) {
            $sort = 'valid_to';
        }

        // Validate order
        if (!in_array($order, ['ASC', 'DESC'])) {
            $order = 'ASC';
        }

        // Build WHERE conditions and parameters
        $whereConditions = [];
        $params = [];

        // Apply status filter
        if ($status === 'active') {
            $whereConditions[] = "valid_to > DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
        } elseif ($status === 'expired') {
            $whereConditions[] = "valid_to < CURDATE()";
        } elseif ($status === 'expiring') {
            $whereConditions[] = "valid_to BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
        }

        // Apply search filter
        if (!empty($search)) {
            $whereConditions[] = "(name LIKE ? OR license LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        // Build WHERE clause
        $whereClause = '';
        if (!empty($whereConditions)) {
            $whereClause = " WHERE " . implode(" AND ", $whereConditions);
        }

        // Count query
        $countSql = "SELECT COUNT(*) FROM projects_list" . $whereClause;
        $stmt = $this->pdo->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();

        // Main query with status calculation
        $sql = "SELECT *, 
                CASE 
                    WHEN valid_to < CURDATE() THEN 'expired'
                    WHEN valid_to BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 'expiring'
                    ELSE 'active'
                END as status
                FROM projects_list" . $whereClause . " ORDER BY $sort $order LIMIT ?, ?";

        // Add pagination parameters
        $mainParams = array_merge($params, [$offset, $perPage]);

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($mainParams);
        $codes = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Calculate pagination
        $totalPages = ceil($total / $perPage);
        $pagination = [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_records' => $total,
            'per_page' => $perPage,
            'has_prev' => $page > 1,
            'has_next' => $page < $totalPages
        ];




        // Load view
        require __DIR__ . '/../../views/activation_codes/index.php';

    }

    public function create()
    {
        require __DIR__ . '/../../views/activation_codes/create.php';
    }

    public function store()
    {
        header('Content-Type: application/json');

        $errors = [];
        $fullName = trim($_POST['name'] ?? '');
        $license = trim($_POST['license'] ?? '');
        $validFrom = $_POST['valid_from'] ?? '';
        $validTo = $_POST['valid_to'] ?? '';

        if (empty($fullName)) {
            $errors[] = 'Full name is required';
        } elseif (strlen($fullName) < 2) {
            $errors[] = 'Full name must be at least 2 characters';
        }

        if (empty($license)) {
            $errors[] = 'License is required';
        } elseif (strlen($license) < 5) {
            $errors[] = 'License must be at least 5 characters';
        }

        if (empty($validFrom) || !strtotime($validFrom)) {
            $errors[] = 'Valid from date is invalid';
        }

        if (empty($validTo) || !strtotime($validTo)) {
            $errors[] = 'Valid to date is invalid';
        } elseif (strtotime($validTo) <= strtotime($validFrom)) {
            $errors[] = 'Valid to date must be after valid from date';
        }

        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM projects_list WHERE license = ?");
        $stmt->execute([$license]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'License key already exists';
        }

        if (empty($errors)) {
            try {
                $stmt = $this->pdo->prepare("
            INSERT INTO projects_list 
            (name, license, valid_from, valid_to, created_at, updated_at)
            VALUES (?, ?, ?, ?, NOW(), NOW())
        ");
                $stmt->execute([$fullName, $license, $validFrom, $validTo]);

                $newId = $this->pdo->lastInsertId();
                $this->logActivity('create', $newId, [
                    'name' => $fullName,
                    'license' => $license,
                    'valid_from' => $validFrom,
                    'valid_to' => $validTo
                ]);

                echo json_encode(['success' => true]);
                exit;
            } catch (\PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
                exit;
            }
        }

        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }



    public function edit($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM projects_list WHERE id = ?");
        $stmt->execute([$id]);
        $code = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$code) {
            $_SESSION['errors'] = ['License not found'];
            header('Location: ' . url('activation-codes'));
            exit;

        }

        require __DIR__ . '/../../views/activation_codes/edit.php';
    }

    public function update($id)
    {
        // Validate input
        $errors = [];
        $fullName = trim($_POST['name'] ?? '');
        $license = trim($_POST['license'] ?? '');
        $validFrom = $_POST['valid_from'] ?? '';
        $validTo = $_POST['valid_to'] ?? '';

        // Validation rules
        if (empty($fullName)) {
            $errors[] = 'Full name is required';
        } elseif (strlen($fullName) < 2) {
            $errors[] = 'Full name must be at least 2 characters';
        }

        if (empty($license)) {
            $errors[] = 'License is required';
        } elseif (strlen($license) < 5) {
            $errors[] = 'License must be at least 5 characters';
        }

        if (empty($validFrom) || !strtotime($validFrom)) {
            $errors[] = 'Valid from date is invalid';
        }

        if (empty($validTo) || !strtotime($validTo)) {
            $errors[] = 'Valid to date is invalid';
        } elseif (strtotime($validTo) <= strtotime($validFrom)) {
            $errors[] = 'Valid to date must be after valid from date';
        }

        // Check for duplicate license (excluding current record)
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM projects_list WHERE license = ? AND id != ?");
        $stmt->execute([$license, $id]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'License key already exists';
        }

        if (empty($errors)) {
            try {
                $stmt = $this->pdo->prepare("
            UPDATE projects_list 
            SET name = ?, license = ?, valid_from = ?, valid_to = ?, updated_at = NOW()
            WHERE id = ?
        ");
                $stmt->execute([
                    $fullName,
                    $license,
                    $validFrom,
                    $validTo,
                    $id
                ]);

                $this->logActivity('update', $id, [
                    'name' => $fullName,
                    'license' => $license,
                    'valid_from' => $validFrom,
                    'valid_to' => $validTo
                ]);

                $_SESSION['success'] = 'License updated successfully';
                header('Location: ' . url('activation-codes'));
                exit;
            } catch (\PDOException $e) {
                $errors[] = 'Database error: ' . $e->getMessage();
            }
        }

        $_SESSION['errors'] = $errors;
        header('Location: ' . url('activation-codes/edit?id=' . urlencode($id)));
        exit;

    }

    public function delete($id)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT name FROM projects_list WHERE id = ?");
            $stmt->execute([$id]);
            $license = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$license) {
                $_SESSION['errors'] = ['License not found'];
                header('Location: ' . url('activation-codes'));
                exit;
            }

            $stmt = $this->pdo->prepare("DELETE FROM projects_list WHERE id = ?");
            $stmt->execute([$id]);

            $this->logActivity('delete', $id, ['name' => $license['name']]);

            $_SESSION['success'] = "License for {$license['name']} deleted successfully";
        } catch (\PDOException $e) {
            $_SESSION['errors'] = ['Error deleting license: ' . $e->getMessage()];
        }

        header('Location: ' . url('activation-codes'));
        exit;
    }



    public function export()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('activation-codes'));
            exit;
        }

        $format = $_POST['format'] ?? 'csv';
        $ids = $_POST['ids'] ?? '';
        $type = $_POST['type'] ?? 'all';

        // Build query based on export type
        $sql = "SELECT * FROM projects_list";
        $params = [];
        $where = [];

        if (!empty($ids)) {
            $idArray = explode(',', $ids);
            $idArray = array_map('intval', $idArray);
            $idArray = array_filter($idArray);

            if (!empty($idArray)) {
                $placeholders = str_repeat('?,', count($idArray) - 1) . '?';
                $where[] = "id IN ($placeholders)";
                $params = $idArray;
            }
        } else {
            // Apply type filter
            if ($type === 'active') {
                $where[] = "valid_to > DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
            } elseif ($type === 'expired') {
                $where[] = "valid_to < CURDATE()";
            } elseif ($type === 'expiring') {
                $where[] = "valid_to BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
            }
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY valid_to ASC";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if ($format === 'csv') {
                $this->exportCSV($data);
            } elseif ($format === 'pdf') {
                $this->exportPDF($data);
            } else {
                $_SESSION['errors'] = ['Invalid export format'];
                header('Location: ' . url('activation-codes'));
                exit;
            }
        } catch (\PDOException $e) {
            $_SESSION['errors'] = ['Export failed: ' . $e->getMessage()];
            header('Location: ' . url('activation-codes'));
            exit;
        }
    }

    private function exportCSV($data)
    {
        $filename = 'licenses_export_' . date('Y-m-d_H-i-s') . '.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // Add BOM for UTF-8
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Add headers
        fputcsv($output, [
            'ID',
            'Name',
            'License Key',
            'Valid From',
            'Valid To',
            'Status',
            'Days Until Expiry',
            'Created At',
            'Updated At'
        ]);

        // Add data
        foreach ($data as $row) {
            $validTo = strtotime($row['valid_to']);
            $now = time();
            $daysUntilExpiry = ceil(($validTo - $now) / (24 * 60 * 60));

            if ($validTo < $now) {
                $status = 'Expired';
            } elseif ($daysUntilExpiry <= 7) {
                $status = 'Expiring Soon';
            } else {
                $status = 'Active';
            }

            fputcsv($output, [
                $row['id'],
                $row['name'],
                $row['license'],
                $row['valid_from'],
                $row['valid_to'],
                $status,
                $daysUntilExpiry,
                $row['created_at'] ?? '',
                $row['updated_at'] ?? ''
            ]);
        }

        fclose($output);
        exit;
    }

    private function exportPDF($data)
    {
        // For PDF export, you would typically use a library like TCPDF or FPDF
        // For now, we'll create a simple HTML-to-PDF solution
        $filename = 'licenses_export_' . date('Y-m-d_H-i-s') . '.pdf';

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // Simple HTML output (in a real application, use a proper PDF library)
        echo '<html><head><title>License Export</title></head><body>';
        echo '<h1>License Export Report</h1>';
        echo '<p>Generated on: ' . date('Y-m-d H:i:s') . '</p>';
        echo '<table border="1" cellpadding="5" cellspacing="0">';
        echo '<tr><th>ID</th><th>Full Name</th><th>License</th><th>Valid From</th><th>Valid To</th><th>Status</th></tr>';

        foreach ($data as $row) {
            $validTo = strtotime($row['valid_to']);
            $now = time();
            $daysUntilExpiry = ceil(($validTo - $now) / (24 * 60 * 60));

            if ($validTo < $now) {
                $status = 'Expired';
            } elseif ($daysUntilExpiry <= 7) {
                $status = 'Expiring Soon';
            } else {
                $status = 'Active';
            }

            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['id']) . '</td>';
            echo '<td>' . htmlspecialchars($row['name']) . '</td>';
            echo '<td>' . htmlspecialchars($row['license']) . '</td>';
            echo '<td>' . htmlspecialchars($row['valid_from']) . '</td>';
            echo '<td>' . htmlspecialchars($row['valid_to']) . '</td>';
            echo '<td>' . htmlspecialchars($status) . '</td>';
            echo '</tr>';
        }

        echo '</table></body></html>';
        exit;
    }

    private function getStatistics()
    {
        $stats = [];

        try {
            // Total codes
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM projects_list");
            $stats['total'] = $stmt->fetchColumn();

            // Active codes (more than 7 days until expiry)
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM projects_list WHERE valid_to > DATE_ADD(CURDATE(), INTERVAL 7 DAY)");
            $stats['active'] = $stmt->fetchColumn();

            // Expired codes
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM projects_list WHERE valid_to < CURDATE()");
            $stats['expired'] = $stmt->fetchColumn();

            // Expiring soon (within 7 days)
            $stmt = $this->pdo->query("
                SELECT COUNT(*) 
                FROM projects_list 
                WHERE valid_to BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
            ");
            $stats['expiring'] = $stmt->fetchColumn();

            // Recent updates
            $stmt = $this->pdo->query("
                SELECT * FROM projects_list
                ORDER BY updated_at DESC 
                LIMIT 5
            ");
            $stats['recent'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Expiring details for alerts
            $stmt = $this->pdo->query("
                SELECT name, license, valid_to,
                DATEDIFF(valid_to, CURDATE()) as days_remaining
                FROM projects_list 
                WHERE valid_to BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                ORDER BY valid_to ASC
                LIMIT 10
            ");
            $stats['expiring_details'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\PDOException $e) {
            // Return default values if database error
            $stats = [
                'total' => 0,
                'active' => 0,
                'expired' => 0,
                'expiring' => 0,
                'recent' => [],
                'expiring_details' => []
            ];
        }

        return $stats;
    }
    private function getMonthlyLicenseData()
    {
        $data = [
            'labels' => [],
            'new_licenses' => [],
            'expired_licenses' => []
        ];

        try {
            // Get last 6 months names (including current month)
            $months = [];
            $monthNumbers = [];
            for ($i = 5; $i >= 0; $i--) {
                $monthNumbers[] = date('n', strtotime("-$i months"));
                $months[] = date('M', strtotime("-$i months"));
            }
            $data['labels'] = $months;

            // Get new licenses per month - match database format exactly
            $newLicensesQuery = "
            SELECT 
                MONTH(created_at) as month_num,
                DATE_FORMAT(created_at, '%b') AS month_abbr,
                COUNT(*) AS count
            FROM projects_list
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)
            GROUP BY MONTH(created_at), DATE_FORMAT(created_at, '%b')
            ORDER BY MONTH(created_at)
        ";

            $stmt = $this->pdo->query($newLicensesQuery);
            $newLicenses = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Get expired licenses per month
            $expiredLicensesQuery = "
            SELECT 
                MONTH(valid_to) as month_num,
                DATE_FORMAT(valid_to, '%b') AS month_abbr,
                COUNT(*) AS count
            FROM projects_list
            WHERE valid_to >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)
            AND valid_to < CURDATE()
            GROUP BY MONTH(valid_to), DATE_FORMAT(valid_to, '%b')
            ORDER BY MONTH(valid_to)
        ";

            $stmt = $this->pdo->query($expiredLicensesQuery);
            $expiredLicenses = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Initialize with zeros using month numbers as keys
            foreach ($monthNumbers as $num) {
                $data['new_licenses'][$num] = 0;
                $data['expired_licenses'][$num] = 0;
            }

            // Fill actual counts using month numbers
            foreach ($newLicenses as $row) {
                $monthNum = (int) $row['month_num'];
                $data['new_licenses'][$monthNum] = (int) $row['count'];
            }

            foreach ($expiredLicenses as $row) {
                $monthNum = (int) $row['month_num'];
                $data['expired_licenses'][$monthNum] = (int) $row['count'];
            }

            // Convert to sequential arrays in correct order
            $data['new_licenses'] = array_values($data['new_licenses']);
            $data['expired_licenses'] = array_values($data['expired_licenses']);

        } catch (\PDOException $e) {
            error_log("Error getting monthly data: " . $e->getMessage());
            // Provide fallback empty data
            $data['new_licenses'] = array_fill(0, 6, 0);
            $data['expired_licenses'] = array_fill(0, 6, 0);
        }

        return $data;
    }

    public function getFilteredCount($filter = 'all')
    {
        $sql = "SELECT COUNT(*) FROM projects_list";

        if ($filter === 'active') {
            $sql .= " WHERE valid_to > DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
        } elseif ($filter === 'expired') {
            $sql .= " WHERE valid_to < CURDATE()";
        } elseif ($filter === 'expiring') {
            $sql .= " WHERE valid_to BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
        }

        $stmt = $this->pdo->query($sql);
        return $stmt->fetchColumn();


    }



    public function datatable()
    {
        // Get request parameters
        $draw = $_POST['draw'] ?? 1;
        $start = $_POST['start'] ?? 0;
        $length = $_POST['length'] ?? 10;
        $search = $_POST['search']['value'] ?? '';
        $status = $_POST['status'] ?? '';
        $orderColumn = $_POST['order'][0]['column'] ?? 0;
        $orderDir = $_POST['order'][0]['dir'] ?? 'asc';

        // Base query with status and days_left calculation
        $baseQuery = "SELECT *, 
                 CASE 
                     WHEN valid_to < CURDATE() THEN 'expired'
                     WHEN valid_to BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 'expiring'
                     ELSE 'active'
                 END as status,
                 DATEDIFF(valid_to, CURDATE()) as days_left
                 FROM projects_list";

        // Build WHERE conditions
        $whereConditions = [];
        $params = [];

        // Apply search filter
        if (!empty($search)) {
            $whereConditions[] = "(name LIKE ? OR license LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        // Apply status filter - now properly aligned with the CASE statement
        if (!empty($status)) {
            switch ($status) {
                case 'active':
                    $whereConditions[] = "valid_to > DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
                    break;
                case 'expiring':
                    $whereConditions[] = "valid_to BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
                    break;
                case 'expired':
                    $whereConditions[] = "valid_to < CURDATE()";
                    break;
            }
        }

        // Combine WHERE conditions
        $whereClause = empty($whereConditions) ? '' : ' WHERE ' . implode(' AND ', $whereConditions);

        // Get total records count
        $totalRecordsQuery = "SELECT COUNT(*) FROM projects_list";
        $totalRecords = $this->pdo->query($totalRecordsQuery)->fetchColumn();

        // Get filtered count
        $filteredQuery = "SELECT COUNT(*) FROM projects_list" . $whereClause;
        $stmt = $this->pdo->prepare($filteredQuery);
        $stmt->execute($params);
        $filteredRecords = $stmt->fetchColumn();

        // Column mapping for ordering
        $columns = [
            0 => 'id',
            1 => 'name',
            2 => 'license',
            3 => 'valid_from',
            4 => 'valid_to',
            5 => 'status',
            6 => 'days_left'
        ];

        // Ordering
        $orderBy = '';
        if (isset($columns[$orderColumn])) {
            $orderColumnName = $columns[$orderColumn];
            $orderDir = strtoupper($orderDir) === 'ASC' ? 'ASC' : 'DESC';
            $orderBy = " ORDER BY $orderColumnName $orderDir";
        }

        // Build and execute final query
        $query = $baseQuery . $whereClause . $orderBy . " LIMIT ?, ?";
        $params[] = (int) $start;
        $params[] = (int) $length;

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Format the response
        $response = [
            "draw" => intval($draw),
            "recordsTotal" => intval($totalRecords),
            "recordsFiltered" => intval($filteredRecords),
            "data" => $data
        ];

        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    public function bulkUpdate()
    {
        header('Content-Type: application/json');

        try {
            $ids = $_POST['ids'] ?? [];
            $validTo = $_POST['valid_to'] ?? '';

            if (empty($ids) || empty($validTo)) {
                throw new \Exception('Invalid parameters');
            }

            if (!strtotime($validTo)) {
                throw new \Exception('Invalid date format');
            }

            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $sql = "UPDATE projects_list SET valid_to = ?, updated_at = NOW() WHERE id IN ($placeholders)";

            $params = array_merge([$validTo], $ids);
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            echo json_encode([
                'success' => true,
                'message' => 'Licenses updated successfully',
                'count' => $stmt->rowCount()
            ]);
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }

    public function bulkDelete()
    {
        header('Content-Type: application/json');

        try {
            $ids = $_POST['ids'] ?? [];

            if (empty($ids)) {
                throw new \Exception('No licenses selected');
            }

            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $sql = "DELETE FROM projects_list WHERE id IN ($placeholders)";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($ids);

            echo json_encode([
                'success' => true,
                'message' => 'Licenses deleted successfully',
                'count' => $stmt->rowCount()
            ]);
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }
    private function logActivity(string $actionType, ?int $licenseId = null, array $requestData = []): bool
    {
        if (!$this->isAuthenticated()) {
            error_log("Cannot log activity - user not authenticated");
            return false;
        }

        $userId = $_SESSION['user_id'];
        $ipAddress = $this->getClientIp();

        // Determine action and description based on type
        switch ($actionType) {
            case 'create':
                $action = 'CREATE_LICENSE';
                $description = 'Created a new license';
                break;
            case 'update':
                $action = 'UPDATE_LICENSE';
                $description = 'Updated a license';
                break;
            case 'delete':
                $action = 'DELETE_LICENSE';
                $description = 'Deleted a license';
                break;
            case 'export':
                $action = 'EXPORT_LICENSES';
                $description = 'Exported license data';
                break;
            default:
                $action = 'UNKNOWN_ACTION';
                $description = 'Performed an action';
        }

        // Add license info if available
        if ($licenseId) {
            $description .= " (ID: {$licenseId})";
        }

        // Add request data details
        if (!empty($requestData)) {
            $dataSummary = json_encode($requestData);
            $description .= " | Data: " . substr($dataSummary, 0, 200); // Limit to 200 chars
        }

        try {
            $stmt = $this->pdo->prepare("
            INSERT INTO activity_logs 
            (user_id, action, description, ip_address) 
            VALUES 
            (?, ?, ?, ?)
        ");
            return $stmt->execute([$userId, $action, $description, $ipAddress]);
        } catch (\PDOException $e) {
            error_log("Failed to log activity: " . $e->getMessage());
            return false;
        }
    }

    private function getClientIp(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        }
        return $ip;
    }

    private function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
}