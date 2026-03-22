<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/csrf.php';
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../models/Product.php';

function handleCartAction(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        return;
    }

    if (!isset($_POST['cart_action'])) {
        return;
    }

    if (!verifyCsrfToken()) {
        redirect('cart.php');
    }

    $action = (string)($_POST['cart_action'] ?? '');
    $productId = (int)($_POST['product_id'] ?? 0);
    $qty = (int)($_POST['qty'] ?? 1);
    $userId = currentUserId();

    $result = ['success' => false, 'message' => 'Invalid cart action.'];

    switch ($action) {
        case 'add':
            $result = Cart::addProduct($productId, max(1, $qty), $userId);
            break;
        case 'increase':
            $result = Cart::increase($productId, $userId);
            break;
        case 'decrease':
            $result = Cart::decrease($productId, $userId);
            break;
        case 'remove':
            Cart::remove($productId, $userId);
            $result = ['success' => true, 'message' => 'Item removed from cart.'];
            break;
        case 'clear':
            Cart::clear($userId);
            $result = ['success' => true, 'message' => 'Cart cleared.'];
            break;
    }

    flash($result['success'] ? 'success' : 'error', $result['message']);

    $redirectTo = trim((string)($_POST['redirect_to'] ?? 'cart.php'));
    redirect($redirectTo === '' ? 'cart.php' : $redirectTo);
}
