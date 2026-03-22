<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/csrf.php';
require_once __DIR__ . '/../controllers/cart_controller.php';
require_once __DIR__ . '/../models/Cart.php';

handleCartAction();

$pageTitle = 'Cart';
$userId = currentUserId();
$cartItems = Cart::getItems($userId);
$totals = Cart::totals($userId);

include __DIR__ . '/../includes/header.php';
?>
<section class="container section">
    <div class="page-head">
        <h1>Your Cart</h1>
        <a class="btn btn-outline" href="<?= e(siteUrl('index.php')) ?>">Continue Shopping</a>
    </div>

    <?php if (empty($cartItems)): ?>
        <div class="card">
            <p>Your cart is empty.</p>
            <a href="<?= e(siteUrl('index.php')) ?>" class="btn btn-primary">Start shopping</a>
        </div>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th>Subtotal</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($cartItems as $item): ?>
                <tr>
                    <td>
                        <div class="table-product">
                            <img src="<?= e($item['image_url']) ?>" alt="<?= e($item['name']) ?>">
                            <div>
                                <strong><?= e($item['name']) ?></strong>
                                <div class="muted">Stock: <?= (int)$item['stock'] ?></div>
                            </div>
                        </div>
                    </td>
                    <td>KSH <?= number_format((float)$item['price'], 0) ?></td>
                    <td><?= (int)$item['qty'] ?></td>
                    <td>KSH <?= number_format((float)$item['subtotal'], 0) ?></td>
                    <td>
                        <form method="POST" class="inline-form">
                            <?= csrfField() ?>
                            <input type="hidden" name="cart_action" value="increase">
                            <input type="hidden" name="product_id" value="<?= (int)$item['product_id'] ?>">
                            <input type="hidden" name="redirect_to" value="cart.php">
                            <button type="submit" class="btn btn-outline btn-sm">+</button>
                        </form>
                        <form method="POST" class="inline-form">
                            <?= csrfField() ?>
                            <input type="hidden" name="cart_action" value="decrease">
                            <input type="hidden" name="product_id" value="<?= (int)$item['product_id'] ?>">
                            <input type="hidden" name="redirect_to" value="cart.php">
                            <button type="submit" class="btn btn-outline btn-sm">-</button>
                        </form>
                        <form method="POST" class="inline-form">
                            <?= csrfField() ?>
                            <input type="hidden" name="cart_action" value="remove">
                            <input type="hidden" name="product_id" value="<?= (int)$item['product_id'] ?>">
                            <input type="hidden" name="redirect_to" value="cart.php">
                            <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <div class="summary-card card">
            <p><strong>Subtotal:</strong> KSH <?= number_format((float)$totals['subtotal'], 0) ?></p>
            <p><strong>Total:</strong> KSH <?= number_format((float)$totals['total'], 0) ?></p>
            <form method="POST" class="inline-form">
                <?= csrfField() ?>
                <input type="hidden" name="cart_action" value="clear">
                <input type="hidden" name="redirect_to" value="cart.php">
                <button type="submit" class="btn btn-outline">Clear Cart</button>
            </form>
            <a href="<?= e(siteUrl('checkout.php')) ?>" class="btn btn-accent">Proceed to Checkout</a>
        </div>
    <?php endif; ?>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
