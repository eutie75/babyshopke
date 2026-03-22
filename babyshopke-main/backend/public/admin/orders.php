<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/csrf.php';
require_once __DIR__ . '/../../includes/auth_guard.php';
require_once __DIR__ . '/../../includes/admin_guard.php';
require_once __DIR__ . '/../../models/Order.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!verifyCsrfToken()) {
        redirect('admin/orders.php');
    }

    $orderId = (int)($_POST['order_id'] ?? 0);
    $status = (string)($_POST['status'] ?? '');
    if ($orderId > 0 && Order::updateStatus($orderId, $status)) {
        flash('success', 'Order status updated.');
    } else {
        flash('error', 'Unable to update order status.');
    }
    redirect('admin/orders.php');
}

$pageTitle = 'Manage Orders';
$orders = Order::getAll();
include __DIR__ . '/../../includes/header.php';
?>
<section class="container section">
    <h1>All Orders</h1>
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Customer</th>
                <th>Total</th>
                <th>Payment</th>
                <th>Delivery</th>
                <th>Status</th>
                <th>Created</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $o): ?>
            <tr>
                <td>#<?= (int)$o['id'] ?></td>
                <td>
                    <?= e($o['full_name']) ?><br>
                    <small><?= e($o['user_email']) ?></small>
                </td>
                <td>KSH <?= number_format((float)$o['total_amount'], 0) ?></td>
                <td><?= e($o['payment_method']) ?></td>
                <td><?= e($o['delivery_option']) ?></td>
                <td>
                    <form method="POST" class="inline-form">
                        <?= csrfField() ?>
                        <input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>">
                        <select name="status">
                            <?php foreach (['pending', 'paid', 'shipped', 'delivered'] as $status): ?>
                                <option value="<?= e($status) ?>" <?= ($o['status'] === $status) ? 'selected' : '' ?>>
                                    <?= e(ucfirst($status)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm">Save status</button>
                    </form>
                </td>
                <td><?= e($o['created_at']) ?></td>
                <td><a href="<?= e(siteUrl('order_view.php?order_id=' . (int)$o['id'])) ?>">View</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
