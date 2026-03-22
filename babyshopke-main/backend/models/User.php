<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

class User
{
    public static function findByEmail(string $email): ?array
    {
        $db = getDB();
        $stmt = $db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public static function findById(int $id): ?array
    {
        $db = getDB();
        $stmt = $db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public static function create(string $fullName, string $email, string $password, string $role = 'user'): int
    {
        $db = getDB();
        $stmt = $db->prepare('
            INSERT INTO users (full_name, email, password_hash, role)
            VALUES (:full_name, :email, :password_hash, :role)
        ');
        $stmt->execute([
            ':full_name' => $fullName,
            ':email' => strtolower($email),
            ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ':role' => $role,
        ]);

        return (int)$db->lastInsertId();
    }

    public static function updateName(int $id, string $fullName): bool
    {
        $db = getDB();
        $stmt = $db->prepare('UPDATE users SET full_name = :full_name WHERE id = :id');
        return $stmt->execute([
            ':id' => $id,
            ':full_name' => $fullName,
        ]);
    }

    public static function verifyPassword(string $plainPassword, string $passwordHash): bool
    {
        return password_verify($plainPassword, $passwordHash);
    }
}
