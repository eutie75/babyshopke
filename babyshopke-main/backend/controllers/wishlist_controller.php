<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/csrf.php';
require_once __DIR__ . '/../models/Wishlist.php';
require_once __DIR__ . '/../models/Cart.php';

function handleWishlistAction(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        return;
    }

    if (!isset($_POST['wishlist_action'])) {
        return;
    }

    if (!verifyCsrfToken()) {
        redirect('index.php');
    }

    if (!isLoggedIn()) {
        flash('error', 'Please log in to use wishlist.');
        redirect('login.php');
    }

    $userId = (int)currentUserId();
    $action = (string)($_POST['wishlist_action'] ?? '');
    $productId = (int)($_POST['product_id'] ?? 0);

    if ($productId <= 0) {
        flash('error', 'Invalid product.');
        redirect('wishlist.php');
    }

    switch ($action) {
        case 'toggle':
            $added = Wishlist::toggle($userId, $productId);
            flash('success', $added ? 'Product added to wishlist.' : 'Product removed from wishlist.');
            break;

        case 'remove':
            Wishlist::remove($userId, $productId);
            flash('success', 'Product removed from wishlist.');
            break;

        case 'add_to_cart':
            $cartResult = Cart::addProduct($productId, 1, $userId);
            if ($cartResult['success']) {
                Wishlist::remove($userId, $productId);
                flash('success', 'Product moved to cart.');
            } else {
                flash('error', $cartResult['message']);
            }
            break;

        default:
            flash('error', 'Invalid wishlist action.');
            break;
    }

    $redirectTo = trim((string)($_POST['redirect_to'] ?? 'wishlist.php'));
    redirect($redirectTo === '' ? 'wishlist.php' : $redirectTo);
}

