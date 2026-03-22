<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/csrf.php';
require_once __DIR__ . '/../includes/auth_guard.php';
require_once __DIR__ . '/../controllers/order_controller.php';
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../models/User.php';

handleCheckoutSubmission();

$pageTitle = 'Checkout';
$userId = (int)currentUserId();
$items = Cart::getItems($userId);

if (empty($items)) {
    flash('error', 'Your cart is empty.');
    redirect('cart.php');
}

$totals = Cart::totals($userId);
$user = User::findById($userId);

include __DIR__ . '/../includes/header.php';
?>
<section class="container section">
    <div class="card checkout-card">
        <h1>Checkout</h1>
        <p class="muted">Total payable: <strong>KSH <?= number_format((float)$totals['total'], 0) ?></strong></p>

        <form method="POST">
            <?= csrfField() ?>
            <label>Full Name</label>
            <input type="text" name="full_name" required value="<?= e($user['full_name'] ?? '') ?>">

            <label>Phone</label>
            <input type="text" name="phone" required placeholder="07XXXXXXXXXX">

            <label>Address</label>
            <textarea name="address" required rows="3"></textarea>

            <label>Delivery Option</label>
            <select name="delivery_option">
                <option value="delivery">Delivery</option>
                <option value="pickup">Pickup</option>
            </select>

            <label>Payment Method</label>
            <select name="payment_method">
                <option value="MPESA_SIM">MPESA_SIM</option>
                <option value="COD">COD</option>
            </select>

            <button type="submit" class="btn btn-accent">Place Order</button>
        </form>
    </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
