<?php

namespace App\Controllers;

class DashboardController
{
    private $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function index()
    {
        // Initialize statistics
        $stats = [
            'total' => 0,
            'active' => 0,
            'expired' => 0,
            'expiring' => 0,
            'recent' => [],
            'expiring_details' => [],
            'monthly_data' => $this->getMonthlyLicenseData()
        ];

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


            header('Location: ' . url('login'));
            exit;
        }

        // Debug output for monthly data
        error_log("Monthly Data: " . print_r($stats['monthly_data'], true));

        // Load view
        require __DIR__ . '/../../views/dashboard.php';
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
