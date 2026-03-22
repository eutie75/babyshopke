<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/csrf.php';
require_once __DIR__ . '/../../includes/auth_guard.php';
require_once __DIR__ . '/../../includes/admin_guard.php';
require_once __DIR__ . '/../../controllers/product_controller.php';

handleProductCreateSubmission();

$pageTitle = 'Add Product';
include __DIR__ . '/../../includes/header.php';
?>
<section class="container section">
    <h1>Add Product</h1>
    <form method="POST" class="card form-grid">
        <?= csrfField() ?>

        <label>Name</label>
        <input type="text" name="name" required maxlength="200">

        <label>Description</label>
        <textarea name="description" rows="4"></textarea>

        <label>Price (KSH)</label>
        <input type="number" step="0.01" min="1" name="price" required>

        <label>Stock</label>
        <input type="number" min="0" name="stock" required>

        <label>Category</label>
        <select name="category" required>
            <option value="Diapers & Wipes">Diapers & Wipes</option>
            <option value="Feeding">Feeding</option>
            <option value="Toys">Toys</option>
            <option value="Clothing">Clothing</option>
        </select>

        <label>Image URL</label>
        <input type="url" name="image_url" placeholder="https://...">

        <label>Minimum Age (Months)</label>
        <input type="number" min="0" name="age_min_months" value="0" required>

        <label>Maximum Age (Months)</label>
        <input type="number" min="0" name="age_max_months" value="48" required>

        <div class="button-row">
            <button type="submit" class="btn btn-primary">Save Product</button>
            <a class="btn btn-outline" href="<?= e(siteUrl('admin/products.php')) ?>">Cancel</a>
        </div>
    </form>
</section>
<?php include __DIR__ . '/../../includes/footer.php'; ?>

