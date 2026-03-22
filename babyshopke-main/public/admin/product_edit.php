<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/csrf.php';
require_once __DIR__ . '/../../includes/auth_guard.php';
require_once __DIR__ . '/../../includes/admin_guard.php';
require_once __DIR__ . '/../../models/Product.php';
require_once __DIR__ . '/../../controllers/product_controller.php';

$id = (int)($_GET['id'] ?? 0);
$product = Product::getById($id);
if (!$product) {
    flash('error', 'Product not found.');
    redirect('admin/products.php');
}

handleProductUpdateSubmission($id);

$pageTitle = 'Edit: ' . $product['name'];
include __DIR__ . '/../../includes/header.php';
?>
<section class="container section">
    <h1>Edit Product</h1>
    <form method="POST" class="card form-grid">
        <?= csrfField() ?>

        <label>Name</label>
        <input type="text" name="name" value="<?= e($product['name']) ?>" required maxlength="200">

        <label>Description</label>
        <textarea name="description" rows="4"><?= e($product['description']) ?></textarea>

        <label>Price (KSH)</label>
        <input type="number" step="0.01" min="1" name="price" value="<?= e((string)$product['price']) ?>" required>

        <label>Stock</label>
        <input type="number" min="0" name="stock" value="<?= (int)$product['stock'] ?>" required>

        <label>Category</label>
        <select name="category" required>
            <?php foreach (['Diapers & Wipes', 'Feeding', 'Toys', 'Clothing'] as $c): ?>
                <option value="<?= e($c) ?>" <?= ($product['category'] === $c) ? 'selected' : '' ?>><?= e($c) ?></option>
            <?php endforeach; ?>
        </select>

        <label>Image URL</label>
        <input type="url" name="image_url" value="<?= e($product['image_url']) ?>">

        <label>Minimum Age (Months)</label>
        <input type="number" min="0" name="age_min_months" value="<?= (int)$product['age_min_months'] ?>" required>

        <label>Maximum Age (Months)</label>
        <input type="number" min="0" name="age_max_months" value="<?= (int)$product['age_max_months'] ?>" required>

        <div class="button-row">
            <button type="submit" class="btn btn-primary">Update Product</button>
            <a href="<?= e(siteUrl('admin/products.php')) ?>" class="btn btn-outline">Back</a>
        </div>
    </form>
</section>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
