<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

class Wishlist
{
    private static function findWishlistId(int $userId): ?int
    {
        $db = getDB();
        $stmt = $db->prepare('SELECT id FROM wishlists WHERE user_id = :user_id LIMIT 1');
        $stmt->execute([':user_id' => $userId]);
        $wishlistId = $stmt->fetchColumn();

        return $wishlistId ? (int)$wishlistId : null;
    }

    private static function getOrCreateWishlistId(int $userId): int
    {
        $wishlistId = self::findWishlistId($userId);
        if ($wishlistId !== null) {
            return $wishlistId;
        }

        $db = getDB();
        $create = $db->prepare('INSERT INTO wishlists (user_id) VALUES (:user_id)');
        $create->execute([':user_id' => $userId]);
        return (int)$db->lastInsertId();
    }

    public static function add(int $userId, int $productId): bool
    {
        $wishlistId = self::getOrCreateWishlistId($userId);
        $db = getDB();
        $stmt = $db->prepare('
            INSERT IGNORE INTO wishlist_items (wishlist_id, product_id)
            VALUES (:wishlist_id, :product_id)
        ');
        return $stmt->execute([
            ':wishlist_id' => $wishlistId,
            ':product_id' => $productId,
        ]);
    }

    public static function remove(int $userId, int $productId): bool
    {
        $wishlistId = self::getOrCreateWishlistId($userId);
        $db = getDB();
        $stmt = $db->prepare('DELETE FROM wishlist_items WHERE wishlist_id = :wishlist_id AND product_id = :product_id');
        return $stmt->execute([
            ':wishlist_id' => $wishlistId,
            ':product_id' => $productId,
        ]);
    }

    public static function hasProduct(int $userId, int $productId): bool
    {
        $wishlistId = self::findWishlistId($userId);
        if ($wishlistId === null) {
            return false;
        }

        $db = getDB();
        $stmt = $db->prepare('
            SELECT 1
            FROM wishlist_items
            WHERE wishlist_id = :wishlist_id AND product_id = :product_id
            LIMIT 1
        ');
        $stmt->execute([
            ':wishlist_id' => $wishlistId,
            ':product_id' => $productId,
        ]);

        return (bool)$stmt->fetchColumn();
    }

    public static function toggle(int $userId, int $productId): bool
    {
        if (self::hasProduct($userId, $productId)) {
            self::remove($userId, $productId);
            return false;
        }

        self::add($userId, $productId);
        return true;
    }

    public static function countByUser(int $userId): int
    {
        $wishlistId = self::findWishlistId($userId);
        if ($wishlistId === null) {
            return 0;
        }

        $db = getDB();
        $stmt = $db->prepare('SELECT COUNT(*) FROM wishlist_items WHERE wishlist_id = :wishlist_id');
        $stmt->execute([':wishlist_id' => $wishlistId]);
        return (int)$stmt->fetchColumn();
    }

    public static function getProductIdsByUser(int $userId): array
    {
        $wishlistId = self::findWishlistId($userId);
        if ($wishlistId === null) {
            return [];
        }

        $db = getDB();
        $stmt = $db->prepare('SELECT product_id FROM wishlist_items WHERE wishlist_id = :wishlist_id');
        $stmt->execute([':wishlist_id' => $wishlistId]);
        $rows = $stmt->fetchAll();

        return array_map(static fn(array $row): int => (int)$row['product_id'], $rows);
    }

    public static function getItemsByUser(int $userId): array
    {
        $wishlistId = self::findWishlistId($userId);
        if ($wishlistId === null) {
            return [];
        }

        $db = getDB();
        $stmt = $db->prepare('
            SELECT
                p.id AS product_id,
                p.name,
                p.description,
                p.price,
                p.stock,
                p.category,
                p.image_url,
                p.age_min_months,
                p.age_max_months,
                wi.created_at
            FROM wishlist_items wi
            INNER JOIN products p ON p.id = wi.product_id
            WHERE wi.wishlist_id = :wishlist_id
            ORDER BY wi.created_at DESC
        ');
        $stmt->execute([':wishlist_id' => $wishlistId]);
        return $stmt->fetchAll();
    }
}
