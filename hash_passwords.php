<?php
// hash_passwords.php - Run once to convert all plain passwords to bcrypt hashes

$dbConfig = require __DIR__ . '/config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset=utf8mb4",
        $dbConfig['username'],
        $dbConfig['password']
    );

    $stmt = $pdo->query("SELECT user_id, password FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($users as $user) {
        $plainPassword = $user['password'];

        // Skip if already hashed (check length and format)
        if (strlen($plainPassword) === 60 && preg_match('/^\$2[ayb]\$.{56}$/', $plainPassword)) {
            echo "User {$user['user_id']} password already hashed. Skipping.\n";
            continue;
        }

        // Hash the plain password
        $hashed = password_hash($plainPassword, PASSWORD_BCRYPT);

        $update = $pdo->prepare("UPDATE users SET password = :password WHERE user_id = :id");
        $update->execute([
            ':password' => $hashed,
            ':id' => $user['user_id']
        ]);

        echo "User {$user['user_id']} password hashed.\n";
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
