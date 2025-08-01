<?php
namespace App\Models;

use PDO;

class User
{
    private $db;

    public $user_id;
    public $username;
    public $password;
    public $created_at;
    public $updated_at;
    public $level;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function find($user_id)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE user_id = :user_id");
        $stmt->execute(['user_id' => (int) $user_id]);
        return $stmt->fetchObject(__CLASS__);
    }

    public function findByUsername($username): mixed
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function create($data)
    {
        $sql = "INSERT INTO users (username, password, level) 
                VALUES (:username, :password, :level)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'username' => trim(filter_var($data['username'], FILTER_SANITIZE_STRING)),
            'password' => password_hash(trim($data['password']), PASSWORD_BCRYPT),
            'level' => (int) ($data['level'] ?? 1)
        ]);
    }

    public function verifyPassword($inputPassword, $hashedPassword)
    {
        return password_verify(trim($inputPassword), $hashedPassword);
    }

    public function update($user_id, $data)
    {
        $fields = [
            'username' => trim(filter_var($data['username'], FILTER_SANITIZE_STRING)),
            'level' => (int) $data['level'],
            'user_id' => (int) $user_id
        ];

        $sql = "UPDATE users SET 
                username = :username,
                level = :level,
                updated_at = CURRENT_TIMESTAMP";

        // Only update password if provided
        if (!empty($data['password'])) {
            $fields['password'] = password_hash(trim($data['password']), PASSWORD_BCRYPT);
            $sql .= ", password = :password";
        }

        $sql .= " WHERE user_id = :user_id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($fields);
    }
}
