<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth_guard.php';
require_once __DIR__ . '/../models/Order.php';

$orderId = (int)($_GET['order_id'] ?? 0);
$order = $orderId > 0 ? Order::getByIdForUser($orderId, (int)currentUserId()) : null;

if (!$order) {
    flash('error', 'Order not found.');
    redirect('orders.php');
}

$pageTitle = 'Order Confirmation';
include __DIR__ . '/../includes/header.php';
?>
<section class="container section">
    <div class="card">
        <h1>Order Confirmed</h1>
        <p>Your order <strong>#<?= (int)$order['id'] ?></strong> was placed successfully.</p>
        <p>Total: <strong>KSH <?= number_format((float)$order['total_amount'], 0) ?></strong></p>
        <p>Status: <span class="pill"><?= e($order['status']) ?></span></p>

        <div class="button-row">
            <a href="<?= e(siteUrl('order_view.php?order_id=' . (int)$order['id'])) ?>" class="btn btn-primary">View Order</a>
            <a href="<?= e(siteUrl('index.php')) ?>" class="btn btn-outline">Continue Shopping</a>
            <a href="<?= e(siteUrl('orders.php')) ?>" class="btn btn-accent">My Orders</a>
        </div>
    </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>

