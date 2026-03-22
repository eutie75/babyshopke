<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/csrf.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Wishlist.php';
require_once __DIR__ . '/../controllers/cart_controller.php';
require_once __DIR__ . '/../controllers/wishlist_controller.php';

handleCartAction();
handleWishlistAction();

$id = (int)($_GET['id'] ?? 0);
$product = Product::getById($id);
if (!$product) {
    flash('error', 'Product not found.');
    redirect('index.php');
}

$wishlisted = isLoggedIn() ? Wishlist::hasProduct((int)currentUserId(), (int)$product['id']) : false;

$pageTitle = $product['name'];
include __DIR__ . '/../includes/header.php';
?>
<section class="container section">
    <div class="product-detail card">
        <img src="<?= e($product['image_url']) ?>" alt="<?= e($product['name']) ?>" class="product-detail-image">
        <div class="product-detail-content">
            <h1><?= e($product['name']) ?></h1>
            <p class="price">KSH <?= number_format((float)$product['price'], 0) ?></p>
            <p class="muted"><?= e($product['description']) ?></p>
            <p><strong>Category:</strong> <?= e($product['category']) ?></p>
            <p><strong>Age Range:</strong> <?= (int)$product['age_min_months'] ?>-<?= (int)$product['age_max_months'] ?> months</p>
            <p><strong>Stock:</strong> <?= (int)$product['stock'] ?> available</p>

            <div class="button-row">
                <form method="POST">
                    <?= csrfField() ?>
                    <input type="hidden" name="cart_action" value="add">
                    <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                    <input type="hidden" name="redirect_to" value="product.php?id=<?= (int)$product['id'] ?>">
                    <button type="submit" class="btn btn-accent" <?= ((int)$product['stock'] <= 0) ? 'disabled' : '' ?>>
                        <?= ((int)$product['stock'] <= 0) ? 'Out of Stock' : 'Add to Cart' ?>
                    </button>
                </form>

                <form method="POST">
                    <?= csrfField() ?>
                    <input type="hidden" name="wishlist_action" value="toggle">
                    <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                    <input type="hidden" name="redirect_to" value="product.php?id=<?= (int)$product['id'] ?>">
                    <button type="submit" class="btn btn-outline">
                        <?= $wishlisted ? 'Wishlisted' : 'Add Wishlist' ?>
                    </button>
                </form>

                <a href="<?= e(siteUrl('index.php')) ?>" class="btn btn-primary">Back to Shop</a>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
