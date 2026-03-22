<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/csrf.php';
require_once __DIR__ . '/../../includes/auth_guard.php';
require_once __DIR__ . '/../../includes/admin_guard.php';
require_once __DIR__ . '/../../models/Product.php';

$pageTitle = 'Manage Products';
$products = Product::getAll();
include __DIR__ . '/../../includes/header.php';
?>
<section class="container section">
    <div class="page-head">
        <h1>Products</h1>
        <a href="<?= e(siteUrl('admin/product_add.php')) ?>" class="btn btn-primary">Add Product</a>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Age Range</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($products as $p): ?>
            <tr>
                <td><?= (int)$p['id'] ?></td>
                <td><?= e($p['name']) ?></td>
                <td><?= e($p['category']) ?></td>
                <td>KSH <?= number_format((float)$p['price'], 0) ?></td>
                <td><?= (int)$p['stock'] ?></td>
                <td><?= (int)$p['age_min_months'] ?>-<?= (int)$p['age_max_months'] ?> months</td>
                <td>
                    <a href="<?= e(siteUrl('admin/product_edit.php?id=' . (int)$p['id'])) ?>" class="btn btn-outline btn-sm">Edit</a>
                    <form method="POST" action="<?= e(siteUrl('admin/product_delete.php')) ?>" class="inline-form" onsubmit="return confirm('Delete this product?');">
                        <?= csrfField() ?>
                        <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
