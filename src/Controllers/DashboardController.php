<?php
namespace App\Controllers;

use Exception;

class DashboardController
{
    private $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
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

        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) 
                FROM user_actions 
                WHERE user_id = ? AND page = ? AND action = ?
            ");
            $stmt->execute([$userId, $page, $action]);
            return $stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            error_log("Permission check error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Require permission for current user - throws exception if not authorized
     */
    private function requirePermission(string $page, string $action): void
    {
        if (!$this->hasPermission($page, $action)) {
            throw new Exception("Access denied. You don't have permission to $action on $page");
        }
    }

    public function index()
    {
        try {
            // Check permission to view dashboard
            $this->requirePermission('Dashboard', 'view');

            // Check specific permissions for UI elements
            $canRenewLicenses = $this->hasPermission('Dashboard', 'renew licenses');
            $canViewLicenses = $this->hasPermission('License Manager', 'view');
            $canEditLicenses = $this->hasPermission('License Manager', 'update');
            $canViewPayments = $this->hasPermission('Payments', 'view');

            // Initialize statistics
            $stats = [
                'total' => 0,
                'active' => 0,
                'expired' => 0,
                'expiring' => 0,
                'recent' => [],
                'expiring_details' => [],
                'monthly_data' => $this->getMonthlyLicenseData(),
                'payments' => $canViewPayments ? $this->getPaymentStatistics() : $this->getEmptyPaymentStats(),
                'monthly_revenue' => $canViewPayments ? $this->getMonthlyRevenueData() : $this->getEmptyRevenueData()
            ];

            // Only load license data if user has permission
            if ($canViewLicenses) {
                $stats = $this->loadLicenseStatistics($stats);
            }

            // Pass permissions to view
            $permissions = [
                'canRenewLicenses' => $canRenewLicenses,
                'canViewLicenses' => $canViewLicenses,
                'canEditLicenses' => $canEditLicenses,
                'canViewPayments' => $canViewPayments
            ];

            // Debug output
            error_log("Dashboard permissions: " . print_r($permissions, true));
            error_log("Monthly Data: " . print_r($stats['monthly_data'], true));
            error_log("Payment Stats: " . print_r($stats['payments'], true));
            error_log("Monthly Revenue: " . print_r($stats['monthly_revenue'], true));

            // Load view
            require __DIR__ . '/../../views/dashboard.php';

        } catch (Exception $e) {
            error_log("Dashboard access error: " . $e->getMessage());

            // Show access denied page
            $errorMessage = $e->getMessage();
            require __DIR__ . '/../../views/errors/403.php';
            return;
        }
    }

    private function loadLicenseStatistics($stats)
    {
        try {
            // Total codes
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM projects_list");
            $stats['total'] = $stmt->fetchColumn();

            // Active codes (valid for more than 7 days)
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM projects_list WHERE valid_to > DATE_ADD(CURDATE(), INTERVAL 7 DAY)");
            $stats['active'] = $stmt->fetchColumn();

            // Expired codes
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM projects_list WHERE valid_to < CURDATE()");
            $stats['expired'] = $stmt->fetchColumn();

            // Expiring soon (within next 7 days, including today)
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
            $stats['recent'] = $stmt->fetchAll();

            // Expiring details for alerts
            $stmt = $this->pdo->query("
                SELECT 
                    id,
                    name, 
                    license, 
                    valid_to, 
                    DATEDIFF(valid_to, CURDATE()) as days_remaining 
                FROM projects_list 
                WHERE valid_to BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                ORDER BY valid_to ASC 
                LIMIT 10
            ");
            $stats['expiring_details'] = $stmt->fetchAll();

        } catch (\PDOException $e) {
            error_log("Database error in DashboardController: " . $e->getMessage());
            $_SESSION['error'] = "Error loading dashboard data";
        }

        return $stats;
    }

    private function getPaymentStatistics()
    {
        $stats = [
            'total_payments' => 0,
            'total_revenue' => 0,
            'this_month_revenue' => 0,
            'this_month_payments' => 0,
            'average_payment' => 0,
            'top_paying_clients' => [],
            'recent_payments' => []
        ];

        try {
            // Total payments count
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM payments WHERE is_deleted = 0");
            $stats['total_payments'] = $stmt->fetchColumn();

            // Total revenue
            $stmt = $this->pdo->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE is_deleted = 0");
            $stats['total_revenue'] = (float) $stmt->fetchColumn();

            // This month's revenue
            $stmt = $this->pdo->query("
                SELECT COALESCE(SUM(amount), 0) 
                FROM payments 
                WHERE is_deleted = 0 
                AND MONTH(payment_date) = MONTH(CURDATE()) 
                AND YEAR(payment_date) = YEAR(CURDATE())
            ");
            $stats['this_month_revenue'] = (float) $stmt->fetchColumn();

            // This month's payment count
            $stmt = $this->pdo->query("
                SELECT COUNT(*) 
                FROM payments 
                WHERE is_deleted = 0 
                AND MONTH(payment_date) = MONTH(CURDATE()) 
                AND YEAR(payment_date) = YEAR(CURDATE())
            ");
            $stats['this_month_payments'] = $stmt->fetchColumn();

            // Average payment amount
            if ($stats['total_payments'] > 0) {
                $stats['average_payment'] = $stats['total_revenue'] / $stats['total_payments'];
            }

            // Top paying clients (last 6 months)
            $stmt = $this->pdo->query("
                SELECT 
                    pl.name as client_name,
                    COALESCE(SUM(p.amount), 0) as total_paid,
                    COUNT(p.id) as payment_count
                FROM payments p
                LEFT JOIN projects_list pl ON p.client_id = pl.id
                WHERE p.is_deleted = 0 
                AND p.payment_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                GROUP BY p.client_id, pl.name
                HAVING total_paid > 0
                ORDER BY total_paid DESC
                LIMIT 5
            ");
            $stats['top_paying_clients'] = $stmt->fetchAll();

            // Recent payments
            $stmt = $this->pdo->query("
                SELECT 
                    p.*,
                    pl.name as client_name
                FROM payments p
                LEFT JOIN projects_list pl ON p.client_id = pl.id
                WHERE p.is_deleted = 0
                ORDER BY p.payment_date DESC, p.created_at DESC
                LIMIT 5
            ");
            $stats['recent_payments'] = $stmt->fetchAll();

        } catch (\PDOException $e) {
            error_log("Error getting payment statistics: " . $e->getMessage());
        }

        return $stats;
    }

    private function getEmptyPaymentStats()
    {
        return [
            'total_payments' => 0,
            'total_revenue' => 0,
            'this_month_revenue' => 0,
            'this_month_payments' => 0,
            'average_payment' => 0,
            'top_paying_clients' => [],
            'recent_payments' => []
        ];
    }

    private function getMonthlyRevenueData()
    {
        $data = [
            'labels' => [],
            'revenue' => [],
            'payment_count' => []
        ];

        try {
            // Get last 6 months including current month
            $currentMonth = (int) date('n');
            $currentYear = (int) date('Y');
            $months = [];
            $monthYearPairs = [];

            for ($i = 5; $i >= 0; $i--) {
                $month = $currentMonth - $i;
                $year = $currentYear;

                if ($month < 1) {
                    $month += 12;
                    $year--;
                }

                $monthYearPairs[] = [
                    'month' => $month,
                    'year' => $year
                ];
                $months[] = date('M', mktime(0, 0, 0, $month, 1, $year));
            }

            $data['labels'] = $months;

            // Monthly revenue query
            $revenueQuery = "
                SELECT 
                    MONTH(payment_date) as month_num,
                    YEAR(payment_date) as year_num,
                    COALESCE(SUM(amount), 0) as total_revenue,
                    COUNT(*) as payment_count
                FROM payments 
                WHERE is_deleted = 0 
                AND payment_date >= DATE_SUB(
                    DATE_FORMAT(NOW(), '%Y-%m-01'), 
                    INTERVAL 5 MONTH
                )
                GROUP BY YEAR(payment_date), MONTH(payment_date)
                ORDER BY YEAR(payment_date), MONTH(payment_date)
            ";

            $stmt = $this->pdo->query($revenueQuery);
            $revenueData = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Initialize with zeros
            $data['revenue'] = array_fill(0, 6, 0);
            $data['payment_count'] = array_fill(0, 6, 0);

            // Map query results
            foreach ($revenueData as $row) {
                $monthNum = (int) $row['month_num'];
                $yearNum = (int) $row['year_num'];

                foreach ($monthYearPairs as $index => $pair) {
                    if ($pair['month'] == $monthNum && $pair['year'] == $yearNum) {
                        $data['revenue'][$index] = (float) $row['total_revenue'];
                        $data['payment_count'][$index] = (int) $row['payment_count'];
                        break;
                    }
                }
            }

        } catch (\PDOException $e) {
            error_log("Error getting monthly revenue data: " . $e->getMessage());
            $data['revenue'] = array_fill(0, 6, 0);
            $data['payment_count'] = array_fill(0, 6, 0);
        }

        return $data;
    }

    private function getEmptyRevenueData()
    {
        return [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            'revenue' => [0, 0, 0, 0, 0, 0],
            'payment_count' => [0, 0, 0, 0, 0, 0]
        ];
    }

    private function getMonthlyLicenseData()
    {
        $data = [
            'labels' => [],
            'new_licenses' => [],
            'expired_licenses' => []
        ];

        try {
            // Get last 6 months including current month
            $currentMonth = (int) date('n');
            $currentYear = (int) date('Y');
            $months = [];
            $monthYearPairs = [];

            for ($i = 5; $i >= 0; $i--) {
                $month = $currentMonth - $i;
                $year = $currentYear;

                if ($month < 1) {
                    $month += 12;
                    $year--;
                }

                $monthYearPairs[] = [
                    'month' => $month,
                    'year' => $year
                ];
                $months[] = date('M', mktime(0, 0, 0, $month, 1, $year));
            }

            $data['labels'] = $months;

            // New licenses
            $newLicensesQuery = "
                SELECT 
                    MONTH(created_at) as month_num,
                    YEAR(created_at) as year_num,
                    COUNT(*) AS count
                FROM projects_list
                WHERE created_at >= DATE_SUB(
                    DATE_FORMAT(NOW(), '%Y-%m-01'), 
                    INTERVAL 5 MONTH
                )
                GROUP BY YEAR(created_at), MONTH(created_at)
                ORDER BY YEAR(created_at), MONTH(created_at)
            ";

            $stmt = $this->pdo->query($newLicensesQuery);
            $newLicenses = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Expired licenses
            $expiredLicensesQuery = "
                SELECT 
                    MONTH(valid_to) as month_num,
                    YEAR(valid_to) as year_num,
                    COUNT(*) AS count
                FROM projects_list
                WHERE valid_to >= DATE_SUB(
                    DATE_FORMAT(NOW(), '%Y-%m-01'), 
                    INTERVAL 5 MONTH
                )
                AND valid_to <= NOW()
                GROUP BY YEAR(valid_to), MONTH(valid_to)
                ORDER BY YEAR(valid_to), MONTH(valid_to)
            ";

            $stmt = $this->pdo->query($expiredLicensesQuery);
            $expiredLicenses = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Initialize with zeros
            $data['new_licenses'] = array_fill(0, 6, 0);
            $data['expired_licenses'] = array_fill(0, 6, 0);

            // Map query results
            foreach ($newLicenses as $row) {
                $monthNum = (int) $row['month_num'];
                $yearNum = (int) $row['year_num'];

                foreach ($monthYearPairs as $index => $pair) {
                    if ($pair['month'] == $monthNum && $pair['year'] == $yearNum) {
                        $data['new_licenses'][$index] = (int) $row['count'];
                        break;
                    }
                }
            }

            foreach ($expiredLicenses as $row) {
                $monthNum = (int) $row['month_num'];
                $yearNum = (int) $row['year_num'];

                foreach ($monthYearPairs as $index => $pair) {
                    if ($pair['month'] == $monthNum && $pair['year'] == $yearNum) {
                        $data['expired_licenses'][$index] = (int) $row['count'];
                        break;
                    }
                }
            }

        } catch (\PDOException $e) {
            error_log("Error getting monthly data: " . $e->getMessage());
            $data['new_licenses'] = array_fill(0, 6, 0);
            $data['expired_licenses'] = array_fill(0, 6, 0);
        }

        return $data;
    }
}
