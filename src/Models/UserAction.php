<?php
namespace App\Models;

use PDO;

class UserAction
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // Create user action record
    public function create(int $userId, string $page, string $action): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO user_action (user_id, page, action) 
            VALUES (:user_id, :page, :action)
        ");
        return $stmt->execute([
            ':user_id' => $userId,
            ':page' => $page,
            ':action' => $action,
        ]);
    }

    // Find user action by ID
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM user_action WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    // Update user action
    public function update(int $id, string $page, string $action): bool
    {
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
    }

    // Delete user action
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM user_action WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    // Get filtered actions with pagination
    public function getFilteredActions(?int $userId, ?string $page, ?string $action, int $limit, int $offset): array
    {
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
    }

    // Count total filtered actions
    public function countFilteredActions(?int $userId, ?string $page, ?string $action): int
    {
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
    }

    // Get all users for dropdown in filters (Admin only)
    public function getUsers(): array
    {
        $stmt = $this->db->query("SELECT user_id, username FROM users ORDER BY username ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getActionsForUserAndPage(int $userId, string $page): array
    {
        $stmt = $this->db->prepare("
            SELECT action FROM user_page_actions
            WHERE user_id = :user_id AND page = :page
        ");
        $stmt->execute([
            ':user_id' => $userId,
            ':page' => $page,
        ]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);  // fetch as array of actions
    }

    public function updateActionsForUserAndPage(int $userId, string $page, array $actions): bool
    {
        try {
            $this->db->beginTransaction();

            // Delete existing actions
            $deleteStmt = $this->db->prepare("
                DELETE FROM user_page_actions WHERE user_id = :user_id AND page = :page
            ");
            $deleteStmt->execute([
                ':user_id' => $userId,
                ':page' => $page,
            ]);

            // Insert new actions
            $insertStmt = $this->db->prepare("
                INSERT INTO user_page_actions (user_id, page, action) VALUES (:user_id, :page, :action)
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

}
