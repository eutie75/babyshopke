<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth_guard.php';
require_once __DIR__ . '/../../includes/admin_guard.php';
require_once __DIR__ . '/../../models/Product.php';
require_once __DIR__ . '/../../models/Order.php';

$pageTitle = 'Admin Dashboard';
$totalProducts = Product::countAll();
$totalOrders = Order::countAll();
$lowStockCount = Product::lowStockCount(5);
include __DIR__ . '/../../includes/header.php';
?>
<section class="container section">
    <h1>Admin Dashboard</h1>

    <div class="stats-grid">
        <div class="card stat-card">
            <h3>Total Orders</h3>
            <p><?= $totalOrders ?></p>
        </div>
        <div class="card stat-card">
            <h3>Total Products</h3>
            <p><?= $totalProducts ?></p>
        </div>
        <div class="card stat-card">
            <h3>Low Stock (<=5)</h3>
            <p><?= $lowStockCount ?></p>
        </div>
    </div>

    <div class="button-row">
        <a href="<?= e(siteUrl('admin/products.php')) ?>" class="btn btn-primary">Manage Products</a>
        <a href="<?= e(siteUrl('admin/orders.php')) ?>" class="btn btn-outline">Manage Orders</a>
    </div>
</section>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
