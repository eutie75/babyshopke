<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/Product.php';

class Cart
{
    private static function getOrCreateCartId(int $userId): int
    {
        $db = getDB();
        $stmt = $db->prepare('SELECT id FROM carts WHERE user_id = :user_id LIMIT 1');
        $stmt->execute([':user_id' => $userId]);
        $cartId = $stmt->fetchColumn();

        if ($cartId) {
            return (int)$cartId;
        }

        $create = $db->prepare('INSERT INTO carts (user_id) VALUES (:user_id)');
        $create->execute([':user_id' => $userId]);
        return (int)$db->lastInsertId();
    }

    private static function getGuestCart(): array
    {
        $cart = $_SESSION['guest_cart'] ?? [];
        return is_array($cart) ? $cart : [];
    }

    private static function saveGuestCart(array $cart): void
    {
        $_SESSION['guest_cart'] = $cart;
    }

    public static function addProduct(int $productId, int $qty = 1, ?int $userId = null): array
    {
        $qty = max(1, $qty);
        $product = Product::getById($productId);

        if (!$product) {
            return ['success' => false, 'message' => 'Product not found.'];
        }

        if ((int)$product['stock'] <= 0) {
            return ['success' => false, 'message' => 'This product is out of stock.'];
        }

        if ($userId === null) {
            $cart = self::getGuestCart();
            $currentQty = (int)($cart[$productId] ?? 0);
            $nextQty = $currentQty + $qty;

            if ($nextQty > (int)$product['stock']) {
                return ['success' => false, 'message' => 'Requested quantity exceeds available stock.'];
            }

            $cart[$productId] = $nextQty;
            self::saveGuestCart($cart);
            return ['success' => true, 'message' => 'Added to cart.'];
        }

        $db = getDB();
        $cartId = self::getOrCreateCartId($userId);
        $stmt = $db->prepare('SELECT qty FROM cart_items WHERE cart_id = :cart_id AND product_id = :product_id LIMIT 1');
        $stmt->execute([
            ':cart_id' => $cartId,
            ':product_id' => $productId,
        ]);
        $currentQty = (int)($stmt->fetchColumn() ?: 0);
        $nextQty = $currentQty + $qty;

        if ($nextQty > (int)$product['stock']) {
            return ['success' => false, 'message' => 'Requested quantity exceeds available stock.'];
        }

        if ($currentQty > 0) {
            $update = $db->prepare('UPDATE cart_items SET qty = :qty WHERE cart_id = :cart_id AND product_id = :product_id');
            $update->execute([
                ':qty' => $nextQty,
                ':cart_id' => $cartId,
                ':product_id' => $productId,
            ]);
        } else {
            $insert = $db->prepare('INSERT INTO cart_items (cart_id, product_id, qty) VALUES (:cart_id, :product_id, :qty)');
            $insert->execute([
                ':cart_id' => $cartId,
                ':product_id' => $productId,
                ':qty' => $nextQty,
            ]);
        }

        return ['success' => true, 'message' => 'Added to cart.'];
    }

    public static function setQty(int $productId, int $qty, ?int $userId = null): array
    {
        $qty = max(1, $qty);
        $product = Product::getById($productId);

        if (!$product) {
            return ['success' => false, 'message' => 'Product not found.'];
        }

        if ($qty > (int)$product['stock']) {
            return ['success' => false, 'message' => 'Requested quantity exceeds available stock.'];
        }

        if ($userId === null) {
            $cart = self::getGuestCart();
            if (!isset($cart[$productId])) {
                return ['success' => false, 'message' => 'Item not found in cart.'];
            }
            $cart[$productId] = $qty;
            self::saveGuestCart($cart);
            return ['success' => true, 'message' => 'Cart updated.'];
        }

        $db = getDB();
        $cartId = self::getOrCreateCartId($userId);
        $update = $db->prepare('UPDATE cart_items SET qty = :qty WHERE cart_id = :cart_id AND product_id = :product_id');
        $update->execute([
            ':qty' => $qty,
            ':cart_id' => $cartId,
            ':product_id' => $productId,
        ]);

        if ($update->rowCount() === 0) {
            return ['success' => false, 'message' => 'Item not found in cart.'];
        }

        return ['success' => true, 'message' => 'Cart updated.'];
    }

    public static function increase(int $productId, ?int $userId = null): array
    {
        $items = self::getItems($userId);
        foreach ($items as $item) {
            if ((int)$item['product_id'] === $productId) {
                return self::setQty($productId, (int)$item['qty'] + 1, $userId);
            }
        }
        return self::addProduct($productId, 1, $userId);
    }

    public static function decrease(int $productId, ?int $userId = null): array
    {
        $items = self::getItems($userId);
        foreach ($items as $item) {
            if ((int)$item['product_id'] === $productId) {
                $currentQty = (int)$item['qty'];
                if ($currentQty <= 1) {
                    return ['success' => false, 'message' => 'Quantity cannot go below 1. Use remove instead.'];
                }
                return self::setQty($productId, $currentQty - 1, $userId);
            }
        }
        return ['success' => false, 'message' => 'Item not found in cart.'];
    }

    public static function remove(int $productId, ?int $userId = null): bool
    {
        if ($userId === null) {
            $cart = self::getGuestCart();
            unset($cart[$productId]);
            self::saveGuestCart($cart);
            return true;
        }

        $db = getDB();
        $cartId = self::getOrCreateCartId($userId);
        $stmt = $db->prepare('DELETE FROM cart_items WHERE cart_id = :cart_id AND product_id = :product_id');
        return $stmt->execute([
            ':cart_id' => $cartId,
            ':product_id' => $productId,
        ]);
    }

    public static function clear(?int $userId = null): bool
    {
        if ($userId === null) {
            unset($_SESSION['guest_cart']);
            return true;
        }

        $db = getDB();
        $cartId = self::getOrCreateCartId($userId);
        $stmt = $db->prepare('DELETE FROM cart_items WHERE cart_id = :cart_id');
        return $stmt->execute([':cart_id' => $cartId]);
    }

    public static function getItems(?int $userId = null): array
    {
        if ($userId === null) {
            $cart = self::getGuestCart();
            $items = [];
            foreach ($cart as $productId => $qty) {
                $product = Product::getById((int)$productId);
                if (!$product) {
                    continue;
                }
                $items[] = [
                    'product_id' => (int)$product['id'],
                    'name' => $product['name'],
                    'price' => (float)$product['price'],
                    'image_url' => $product['image_url'],
                    'stock' => (int)$product['stock'],
                    'qty' => (int)$qty,
                    'subtotal' => (float)$product['price'] * (int)$qty,
                ];
            }
            return $items;
        }

        $db = getDB();
        $cartId = self::getOrCreateCartId($userId);
        $stmt = $db->prepare('
            SELECT
                p.id AS product_id,
                p.name,
                p.price,
                p.image_url,
                p.stock,
                ci.qty,
                (p.price * ci.qty) AS subtotal
            FROM cart_items ci
            INNER JOIN products p ON p.id = ci.product_id
            WHERE ci.cart_id = :cart_id
            ORDER BY ci.id DESC
        ');
        $stmt->execute([':cart_id' => $cartId]);
        return $stmt->fetchAll();
    }

    public static function count(?int $userId = null): int
    {
        $items = self::getItems($userId);
        $total = 0;
        foreach ($items as $item) {
            $total += (int)$item['qty'];
        }
        return $total;
    }

    public static function totals(?int $userId = null): array
    {
        $items = self::getItems($userId);
        $subtotal = 0.0;
        foreach ($items as $item) {
            $subtotal += (float)$item['subtotal'];
        }

        return [
            'subtotal' => $subtotal,
            'total' => $subtotal,
        ];
    }

    public static function syncGuestCartToUser(int $userId): void
    {
        $guestCart = self::getGuestCart();
        if (empty($guestCart)) {
            return;
        }

        foreach ($guestCart as $productId => $qty) {
            self::addProduct((int)$productId, (int)$qty, $userId);
        }

        unset($_SESSION['guest_cart']);
    }
}

