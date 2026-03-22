<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth_guard.php';
require_once __DIR__ . '/../models/Order.php';

$orderId = (int)($_GET['order_id'] ?? 0);

if ($orderId <= 0) {
    flash('error', 'Order not found.');
    redirect('orders.php');
}

if (isAdmin()) {
    $order = Order::getById($orderId);
} else {
    $order = Order::getByIdForUser($orderId, (int)currentUserId());
}

if (!$order) {
    flash('error', 'Order not found.');
    redirect('orders.php');
}

$items = Order::getItems($orderId);
$pageTitle = 'Order #' . $orderId;
include __DIR__ . '/../includes/header.php';
?>
<section class="container section">
    <h1>Order #<?= $orderId ?></h1>
    <p><strong>Status:</strong> <span class="pill"><?= e($order['status']) ?></span></p>
    <p><strong>Name:</strong> <?= e($order['full_name']) ?></p>
    <p><strong>Phone:</strong> <?= e($order['phone']) ?></p>
    <p><strong>Address:</strong> <?= e($order['address']) ?></p>
    <p><strong>Delivery:</strong> <?= e($order['delivery_option']) ?></p>
    <p><strong>Payment:</strong> <?= e($order['payment_method']) ?></p>

    <table class="table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Qty</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $item): ?>
            <tr>
                <td><?= e($item['name']) ?></td>
                <td>KSH <?= number_format((float)$item['price'], 0) ?></td>
                <td><?= (int)$item['qty'] ?></td>
                <td>KSH <?= number_format((float)$item['price'] * (int)$item['qty'], 0) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <p><strong>Total: KSH <?= number_format((float)$order['total_amount'], 0) ?></strong></p>
    <a href="<?= e(siteUrl('orders.php')) ?>" class="btn btn-outline">Back to Orders</a>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
