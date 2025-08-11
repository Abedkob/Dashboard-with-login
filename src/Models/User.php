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
    public $twofa_secret;
    public $twofa_enabled;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function find($user_id)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE user_id = :user_id");
        $stmt->execute(['user_id' => (int) $user_id]);
        return $stmt->fetch(PDO::FETCH_OBJ);  // <- change here
    }

    public function findByUsername($username)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function create($data)
    {
        $sql = "INSERT INTO users (username, password, level, twofa_secret, twofa_enabled) 
                VALUES (:username, :password, :level, :twofa_secret, :twofa_enabled)";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'username' => trim(filter_var($data['username'], FILTER_SANITIZE_STRING)),
            'password' => password_hash(trim($data['password']), PASSWORD_BCRYPT),
            'level' => (int) ($data['level'] ?? 1),
            'twofa_secret' => $data['twofa_secret'] ?? null,
            'twofa_enabled' => $data['twofa_enabled'] ?? 0,
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
            'twofa_secret' => $data['twofa_secret'] ?? null,
            'twofa_enabled' => isset($data['twofa_enabled']) ? (int) $data['twofa_enabled'] : 0,
            'user_id' => (int) $user_id
        ];

        $sql = "UPDATE users SET 
                username = :username,
                level = :level,
                twofa_secret = :twofa_secret,
                twofa_enabled = :twofa_enabled,
                updated_at = CURRENT_TIMESTAMP";

        if (!empty($data['password'])) {
            $fields['password'] = password_hash(trim($data['password']), PASSWORD_BCRYPT);
            $sql .= ", password = :password";
        }

        $sql .= " WHERE user_id = :user_id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($fields);
    }

    public function updateTwoFASecret($user_id, $secret)
    {
        $stmt = $this->db->prepare("UPDATE users SET twofa_secret = :secret, twofa_enabled = 1, updated_at = CURRENT_TIMESTAMP WHERE user_id = :user_id");
        return $stmt->execute([
            ':secret' => $secret,
            ':user_id' => $user_id
        ]);
    }
}
