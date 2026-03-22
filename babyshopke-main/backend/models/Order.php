<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/Cart.php';
require_once __DIR__ . '/Product.php';

class Order
{
    public static function createFromCart(
        int $userId,
        string $fullName,
        string $phone,
        string $address,
        string $deliveryOption,
        string $paymentMethod,
        ?int $familyId = null,
        ?int $childId = null
    ): int {
        $db = getDB();
        $items = Cart::getItems($userId);

        if (empty($items)) {
            throw new RuntimeException('Cart is empty.');
        }

        $db->beginTransaction();

        try {
            $total = 0.0;
            $validatedItems = [];

            foreach ($items as $item) {
                $product = Product::getByIdForUpdate((int)$item['product_id']);
                if (!$product) {
                    throw new RuntimeException('A product in your cart no longer exists.');
                }

                $qty = (int)$item['qty'];
                if ((int)$product['stock'] < $qty) {
                    throw new RuntimeException('Insufficient stock for ' . $product['name'] . '.');
                }

                $price = (float)$product['price'];
                $lineTotal = $price * $qty;
                $total += $lineTotal;

                $validatedItems[] = [
                    'product_id' => (int)$product['id'],
                    'price' => $price,
                    'qty' => $qty,
                ];
            }

            $orderStmt = $db->prepare('
                INSERT INTO orders (
                    user_id,
                    family_id,
                    child_id,
                    total_amount,
                    payment_method,
                    delivery_option,
                    status,
                    full_name,
                    phone,
                    address
                ) VALUES (
                    :user_id,
                    :family_id,
                    :child_id,
                    :total_amount,
                    :payment_method,
                    :delivery_option,
                    :status,
                    :full_name,
                    :phone,
                    :address
                )
            ');
            $orderStmt->execute([
                ':user_id' => $userId,
                ':family_id' => $familyId,
                ':child_id' => $childId,
                ':total_amount' => $total,
                ':payment_method' => $paymentMethod,
                ':delivery_option' => $deliveryOption,
                ':status' => 'pending',
                ':full_name' => $fullName,
                ':phone' => $phone,
                ':address' => $address,
            ]);
            $orderId = (int)$db->lastInsertId();

            $itemStmt = $db->prepare('
                INSERT INTO order_items (order_id, product_id, price, qty)
                VALUES (:order_id, :product_id, :price, :qty)
            ');
            $stockStmt = $db->prepare('
                UPDATE products
                SET stock = stock - :qty
                WHERE id = :product_id AND stock >= :qty
            ');

            foreach ($validatedItems as $item) {
                $itemStmt->execute([
                    ':order_id' => $orderId,
                    ':product_id' => $item['product_id'],
                    ':price' => $item['price'],
                    ':qty' => $item['qty'],
                ]);

                $stockStmt->execute([
                    ':product_id' => $item['product_id'],
                    ':qty' => $item['qty'],
                ]);

                if ($stockStmt->rowCount() === 0) {
                    throw new RuntimeException('Stock changed before checkout completed. Please try again.');
                }
            }

            Cart::clear($userId);
            $db->commit();

            return $orderId;
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    public static function getByUser(int $userId): array
    {
        $db = getDB();
        $stmt = $db->prepare('SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC, id DESC');
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public static function getAll(): array
    {
        $db = getDB();
        return $db->query('
            SELECT o.*, u.full_name AS user_name, u.email AS user_email
            FROM orders o
            INNER JOIN users u ON u.id = o.user_id
            ORDER BY o.created_at DESC, o.id DESC
        ')->fetchAll();
    }

    public static function getById(int $orderId): ?array
    {
        $db = getDB();
        $stmt = $db->prepare('
            SELECT o.*, u.full_name AS user_name, u.email AS user_email
            FROM orders o
            INNER JOIN users u ON u.id = o.user_id
            WHERE o.id = :id
            LIMIT 1
        ');
        $stmt->execute([':id' => $orderId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function getByIdForUser(int $orderId, int $userId): ?array
    {
        $db = getDB();
        $stmt = $db->prepare('
            SELECT * FROM orders
            WHERE id = :id AND user_id = :user_id
            LIMIT 1
        ');
        $stmt->execute([
            ':id' => $orderId,
            ':user_id' => $userId,
        ]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function getItems(int $orderId): array
    {
        $db = getDB();
        $stmt = $db->prepare('
            SELECT
                oi.*,
                p.name,
                p.image_url,
                p.category
            FROM order_items oi
            INNER JOIN products p ON p.id = oi.product_id
            WHERE oi.order_id = :order_id
            ORDER BY oi.id ASC
        ');
        $stmt->execute([':order_id' => $orderId]);
        return $stmt->fetchAll();
    }

    public static function updateStatus(int $orderId, string $status): bool
    {
        $allowed = ['pending', 'paid', 'shipped', 'delivered'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }

        $db = getDB();
        $stmt = $db->prepare('UPDATE orders SET status = :status WHERE id = :id');
        return $stmt->execute([
            ':status' => $status,
            ':id' => $orderId,
        ]);
    }

    public static function countAll(): int
    {
        $db = getDB();
        return (int)$db->query('SELECT COUNT(*) FROM orders')->fetchColumn();
    }
}
