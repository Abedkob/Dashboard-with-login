<?php

namespace App\Models;

use PDO;
use PDOException;

class UserAction
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Create user action record with detailed error handling
     */
    public function create(int $userId, string $page, string $action): array
    {
        try {
            // First, let's check if the table exists and has the right structure
            $this->verifyTableStructure();

            // Check if user exists
            $userCheck = $this->db->prepare("SELECT COUNT(*) FROM users WHERE user_id = :user_id");
            $userCheck->execute([':user_id' => $userId]);
            if ($userCheck->fetchColumn() == 0) {
                return ['success' => false, 'error' => "User with ID $userId does not exist"];
            }

            // Prepare the insert statement
            $stmt = $this->db->prepare("
                INSERT INTO user_action (user_id, page, action) 
                VALUES (:user_id, :page, :action)
            ");

            $result = $stmt->execute([
                ':user_id' => $userId,
                ':page' => $page,
                ':action' => $action,
            ]);

            if ($result) {
                $insertId = $this->db->lastInsertId();
                error_log("UserAction::create() - Successfully created record with ID: $insertId");
                return ['success' => true, 'id' => $insertId];
            } else {
                $errorInfo = $stmt->errorInfo();
                error_log("UserAction::create() - Execute failed: " . print_r($errorInfo, true));
                return ['success' => false, 'error' => 'Database execute failed: ' . $errorInfo[2]];
            }

        } catch (PDOException $e) {
            error_log("UserAction::create() - PDOException: " . $e->getMessage());
            return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
        } catch (\Exception $e) {
            error_log("UserAction::create() - Exception: " . $e->getMessage());
            return ['success' => false, 'error' => 'General error: ' . $e->getMessage()];
        }
    }

    /**
     * Check if user has permission for a specific page and action
     */
    public function hasPermission(int $userId, string $page, string $action): bool
    {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM user_action 
                WHERE user_id = :user_id AND page = :page AND action = :action
            ");
            $stmt->execute([
                ':user_id' => $userId,
                ':page' => $page,
                ':action' => $action
            ]);

            return $stmt->fetchColumn() > 0;
        } catch (\Exception $e) {
            error_log("UserAction::hasPermission() - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all permissions for a user
     */
    public function getUserPermissions(int $userId): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT page, action FROM user_action 
                WHERE user_id = :user_id
                ORDER BY page, action
            ");
            $stmt->execute([':user_id' => $userId]);

            $permissions = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (!isset($permissions[$row['page']])) {
                    $permissions[$row['page']] = [];
                }
                $permissions[$row['page']][] = $row['action'];
            }

            return $permissions;
        } catch (\Exception $e) {
            error_log("UserAction::getUserPermissions() - " . $e->getMessage());
            return [];
        }
    }

    /**
     * Verify table structure exists
     */
    private function verifyTableStructure(): void
    {
        try {
            // Check if table exists
            $stmt = $this->db->query("SHOW TABLES LIKE 'user_action'");
            if ($stmt->rowCount() == 0) {
                // Create the table if it doesn't exist
                $this->createTable();
            } else {
                // Check if required columns exist
                $stmt = $this->db->query("DESCRIBE user_action");
                $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
                error_log("UserAction table columns: " . implode(', ', $columns));

                $requiredColumns = ['id', 'user_id', 'page', 'action'];
                foreach ($requiredColumns as $column) {
                    if (!in_array($column, $columns)) {
                        throw new \Exception("Missing required column: $column");
                    }
                }
            }
        } catch (\Exception $e) {
            error_log("UserAction::verifyTableStructure() - " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create the user_action table
     */
    private function createTable(): void
    {
        $sql = "
            CREATE TABLE user_action (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                page VARCHAR(255) NOT NULL,
                action VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user_id (user_id),
                INDEX idx_page (page),
                INDEX idx_action (action),
                INDEX idx_user_page_action (user_id, page, action)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";

        $this->db->exec($sql);
        error_log("UserAction::createTable() - Created user_action table");
    }

    /**
     * Get actions for user and page
     */
    public function getActionsForUserAndPage(int $userId, string $page): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT action FROM user_action
                WHERE user_id = :user_id AND page = :page
            ");
            $stmt->execute([
                ':user_id' => $userId,
                ':page' => $page,
            ]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        } catch (\Exception $e) {
            error_log("UserAction::getActionsForUserAndPage() - " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update actions for user and page
     */
    public function updateActionsForUserAndPage(int $userId, string $page, array $actions): bool
    {
        try {
            $this->db->beginTransaction();

            // Delete existing actions
            $deleteStmt = $this->db->prepare("
                DELETE FROM user_action WHERE user_id = :user_id AND page = :page
            ");
            $deleteStmt->execute([
                ':user_id' => $userId,
                ':page' => $page,
            ]);

            // Insert new actions
            $insertStmt = $this->db->prepare("
                INSERT INTO user_action (user_id, page, action) VALUES (:user_id, :page, :action)
            ");

            foreach ($actions as $action) {
                $insertStmt->execute([
                    ':user_id' => $userId,
                    ':page' => $page,
                    ':action' => $action,
                ]);
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Failed to update user actions: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Find user action by ID
     */
    public function findById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM user_action WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (\Exception $e) {
            error_log("UserAction::findById() - " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update user action
     */
    public function update(int $id, string $page, string $action): bool
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE user_action 
                SET page = :page, action = :action 
                WHERE id = :id
            ");
            return $stmt->execute([
                ':page' => $page,
                ':action' => $action,
                ':id' => $id,
            ]);
        } catch (\Exception $e) {
            error_log("UserAction::update() - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete user action
     */
    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM user_action WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (\Exception $e) {
            error_log("UserAction::delete() - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get filtered actions with pagination
     */
    public function getFilteredActions(?int $userId, ?string $page, ?string $action, int $limit, int $offset): array
    {
        try {
            $sql = "SELECT ua.*, u.username 
                    FROM user_action ua
                    JOIN users u ON ua.user_id = u.user_id
                    WHERE 1=1";
            $params = [];

            if ($userId !== null) {
                $sql .= " AND ua.user_id = :user_id";
                $params[':user_id'] = $userId;
            }
            if ($page) {
                $sql .= " AND ua.page = :page";
                $params[':page'] = $page;
            }
            if ($action) {
                $sql .= " AND ua.action = :action";
                $params[':action'] = $action;
            }

            $sql .= " ORDER BY ua.id DESC LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($sql);

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("UserAction::getFilteredActions() - " . $e->getMessage());
            return [];
        }
    }

    /**
     * Count total filtered actions
     */
    public function countFilteredActions(?int $userId, ?string $page, ?string $action): int
    {
        try {
            $sql = "SELECT COUNT(*) FROM user_action WHERE 1=1";
            $params = [];

            if ($userId !== null) {
                $sql .= " AND user_id = :user_id";
                $params[':user_id'] = $userId;
            }
            if ($page) {
                $sql .= " AND page = :page";
                $params[':page'] = $page;
            }
            if ($action) {
                $sql .= " AND action = :action";
                $params[':action'] = $action;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return (int) $stmt->fetchColumn();
        } catch (\Exception $e) {
            error_log("UserAction::countFilteredActions() - " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get all users for dropdown
     */
    public function getUsers(): array
    {
        try {
            $stmt = $this->db->query("SELECT user_id, username FROM users ORDER BY username ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("UserAction::getUsers() - " . $e->getMessage());
            return [];
        }
    }
}
