<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth_guard.php';
require_once __DIR__ . '/../models/Order.php';

$pageTitle = 'My Orders';
$orders = Order::getByUser((int)currentUserId());
include __DIR__ . '/../includes/header.php';
?>
<section class="container section">
    <h1>My Orders</h1>
    <?php if (empty($orders)): ?>
        <div class="card">
            <p>No orders yet.</p>
            <a href="<?= e(siteUrl('index.php')) ?>" class="btn btn-primary">Start shopping</a>
        </div>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Delivery</th>
                    <th>Status</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($orders as $o): ?>
                <tr>
                    <td>#<?= (int)$o['id'] ?></td>
                    <td><?= e($o['created_at']) ?></td>
                    <td>KSH <?= number_format((float)$o['total_amount'], 0) ?></td>
                    <td><?= e($o['payment_method']) ?></td>
                    <td><?= e($o['delivery_option']) ?></td>
                    <td><span class="pill"><?= e($o['status']) ?></span></td>
                    <td><a href="<?= e(siteUrl('order_view.php?order_id=' . (int)$o['id'])) ?>">View</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
