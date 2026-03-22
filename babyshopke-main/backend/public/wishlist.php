<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/csrf.php';
require_once __DIR__ . '/../includes/auth_guard.php';
require_once __DIR__ . '/../controllers/wishlist_controller.php';
require_once __DIR__ . '/../controllers/cart_controller.php';
require_once __DIR__ . '/../models/Wishlist.php';

handleWishlistAction();
handleCartAction();

$items = Wishlist::getItemsByUser((int)currentUserId());
$pageTitle = 'Wishlist';
include __DIR__ . '/../includes/header.php';
?>
<section class="container section">
    <div class="page-head">
        <h1>My Wishlist</h1>
        <a href="<?= e(siteUrl('index.php')) ?>" class="btn btn-outline">Continue Shopping</a>
    </div>

    <?php if (empty($items)): ?>
        <div class="card">
            <p>Your wishlist is empty.</p>
            <a href="<?= e(siteUrl('index.php')) ?>" class="btn btn-primary">Browse Products</a>
        </div>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach ($items as $item): ?>
                <article class="product-card">
                    <img src="<?= e($item['image_url']) ?>" alt="<?= e($item['name']) ?>">
                    <h3><?= e($item['name']) ?></h3>
                    <p class="muted"><?= e($item['category']) ?> | <?= (int)$item['age_min_months'] ?>-<?= (int)$item['age_max_months'] ?> months</p>
                    <p class="price">KSH <?= number_format((float)$item['price'], 0) ?></p>

                    <div class="button-row">
                        <form method="POST">
                            <?= csrfField() ?>
                            <input type="hidden" name="wishlist_action" value="remove">
                            <input type="hidden" name="product_id" value="<?= (int)$item['product_id'] ?>">
                            <input type="hidden" name="redirect_to" value="wishlist.php">
                            <button type="submit" class="btn btn-outline btn-sm">Remove</button>
                        </form>

                        <form method="POST">
                            <?= csrfField() ?>
                            <input type="hidden" name="wishlist_action" value="add_to_cart">
                            <input type="hidden" name="product_id" value="<?= (int)$item['product_id'] ?>">
                            <input type="hidden" name="redirect_to" value="wishlist.php">
                            <button type="submit" class="btn btn-accent btn-sm" <?= ((int)$item['stock'] <= 0) ? 'disabled' : '' ?>>
                                <?= ((int)$item['stock'] <= 0) ? 'Out of Stock' : 'Add to Cart' ?>
                            </button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>

