<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/csrf.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../models/Family.php';

function handleCheckoutSubmission(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        return;
    }

    if (!verifyCsrfToken()) {
        redirect('checkout.php');
    }

    if (!isLoggedIn()) {
        flash('error', 'Please log in to checkout.');
        redirect('login.php');
    }

    $userId = (int)currentUserId();
    $items = Cart::getItems($userId);
    if (empty($items)) {
        flash('error', 'Your cart is empty.');
        redirect('cart.php');
    }

    $fullName = trim((string)($_POST['full_name'] ?? ''));
    $phone = trim((string)($_POST['phone'] ?? ''));
    $address = trim((string)($_POST['address'] ?? ''));
    $deliveryOption = (string)($_POST['delivery_option'] ?? '');
    $paymentMethod = (string)($_POST['payment_method'] ?? '');

    $validDelivery = ['delivery', 'pickup'];
    $validPayment = ['MPESA', 'COD'];

    if ($fullName === '' || $phone === '' || $address === '') {
        flash('error', 'Full name, phone, and address are required.');
        redirect('checkout.php');
    }
    if (!in_array($deliveryOption, $validDelivery, true)) {
        flash('error', 'Invalid delivery option.');
        redirect('checkout.php');
    }
    if (!in_array($paymentMethod, $validPayment, true)) {
        flash('error', 'Invalid payment method.');
        redirect('checkout.php');
    }

    $family = Family::getUserFamily($userId);
    $familyId = $family ? (int)$family['id'] : null;

    $childId = null;
    if (!empty($_SESSION['active_child_id'])) {
        $child = Family::getChildForUser((int)$_SESSION['active_child_id'], $userId);
        if ($child) {
            $childId = (int)$child['id'];
        }
    }

    try {
        $orderId = Order::createFromCart(
            $userId,
            $fullName,
            $phone,
            $address,
            $deliveryOption,
            $paymentMethod,
            $familyId,
            $childId
        );
    } catch (Throwable $e) {
        flash('error', $e->getMessage());
        redirect('checkout.php');
    }

    flash('success', 'Order #' . $orderId . ' placed successfully.');
    redirect('order_confirmation.php?order_id=' . $orderId);
}
