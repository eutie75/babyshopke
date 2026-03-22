<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

class Family
{
    public static function create(string $familyName, int $ownerUserId): int
    {
        $db = getDB();
        $stmt = $db->prepare('
            INSERT INTO families (owner_user_id, family_name)
            VALUES (:owner_user_id, :family_name)
        ');
        $stmt->execute([
            ':owner_user_id' => $ownerUserId,
            ':family_name' => $familyName,
        ]);
        $familyId = (int)$db->lastInsertId();

        $memberStmt = $db->prepare('
            INSERT INTO family_members (family_id, user_id, member_role)
            VALUES (:family_id, :user_id, :member_role)
        ');
        $memberStmt->execute([
            ':family_id' => $familyId,
            ':user_id' => $ownerUserId,
            ':member_role' => 'owner',
        ]);

        return $familyId;
    }

    public static function getById(int $familyId): ?array
    {
        $db = getDB();
        $stmt = $db->prepare('SELECT * FROM families WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $familyId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function getUserFamily(int $userId): ?array
    {
        $db = getDB();
        $stmt = $db->prepare('
            SELECT f.*
            FROM families f
            INNER JOIN family_members fm ON fm.family_id = f.id
            WHERE fm.user_id = :user_id
            LIMIT 1
        ');
        $stmt->execute([':user_id' => $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function getMembers(int $familyId): array
    {
        $db = getDB();
        $stmt = $db->prepare('
            SELECT u.id, u.full_name, u.email, fm.member_role
            FROM family_members fm
            INNER JOIN users u ON u.id = fm.user_id
            WHERE fm.family_id = :family_id
            ORDER BY fm.created_at ASC
        ');
        $stmt->execute([':family_id' => $familyId]);
        return $stmt->fetchAll();
    }

    public static function addChild(int $familyId, string $childName, string $dob): int
    {
        $db = getDB();
        $stmt = $db->prepare('
            INSERT INTO children (family_id, child_name, dob)
            VALUES (:family_id, :child_name, :dob)
        ');
        $stmt->execute([
            ':family_id' => $familyId,
            ':child_name' => $childName,
            ':dob' => $dob,
        ]);

        return (int)$db->lastInsertId();
    }

    public static function getChildren(int $familyId): array
    {
        $db = getDB();
        $stmt = $db->prepare('SELECT * FROM children WHERE family_id = :family_id ORDER BY dob DESC, id DESC');
        $stmt->execute([':family_id' => $familyId]);
        return $stmt->fetchAll();
    }

    public static function getChild(int $childId): ?array
    {
        $db = getDB();
        $stmt = $db->prepare('SELECT * FROM children WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $childId]);
        $child = $stmt->fetch();
        return $child ?: null;
    }

    public static function getChildForUser(int $childId, int $userId): ?array
    {
        $db = getDB();
        $stmt = $db->prepare('
            SELECT c.*
            FROM children c
            INNER JOIN families f ON f.id = c.family_id
            INNER JOIN family_members fm ON fm.family_id = f.id
            WHERE c.id = :child_id
              AND fm.user_id = :user_id
            LIMIT 1
        ');
        $stmt->execute([
            ':child_id' => $childId,
            ':user_id' => $userId,
        ]);

        $child = $stmt->fetch();
        return $child ?: null;
    }

    public static function childAgeMonths(string $dob): int
    {
        $birthDate = new DateTimeImmutable($dob);
        $today = new DateTimeImmutable('today');
        $diff = $birthDate->diff($today);

        return max(0, ($diff->y * 12) + $diff->m);
    }
}
