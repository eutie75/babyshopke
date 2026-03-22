<header class="site-header">
    <nav class="navbar">
        <div class="container nav-container">
            <a href="<?= e(siteUrl('index.php')) ?>" class="logo">
                <img src="<?= e(siteUrl('logo.png')) ?>" alt="Baby Shop KE logo">
            </a>

            <form class="search-bar" action="<?= e(siteUrl('index.php')) ?>" method="GET">
                <input
                    type="text"
                    name="q"
                    value="<?= e((string)($_GET['q'] ?? '')) ?>"
                    placeholder="Search products..."
                >
                <button type="submit">Search</button>
            </form>

            <div class="nav-links">
                <a href="<?= e(siteUrl('getstarted.php')) ?>">Get Started</a>
                <a href="<?= e(siteUrl('wishlist.php')) ?>" class="badge-link">
                    Wishlist
                    <?php if (isLoggedIn()): ?>
                        <span class="count-badge"><?= wishlistCount() ?></span>
                    <?php endif; ?>
                </a>
                <a href="<?= e(siteUrl('cart.php')) ?>" class="badge-link">
                    Cart <span class="count-badge"><?= cartCount() ?></span>
                </a>

                <?php if (isLoggedIn()): ?>
                    <a href="<?= e(siteUrl('account.php')) ?>">Account</a>
                    <a href="<?= e(siteUrl('orders.php')) ?>">Orders</a>
                    <?php if (isAdmin()): ?>
                        <a href="<?= e(siteUrl('admin/dashboard.php')) ?>">Admin</a>
                    <?php endif; ?>
                    <a href="<?= e(siteUrl('logout.php')) ?>">Logout</a>
                <?php else: ?>
                    <a href="<?= e(siteUrl('login.php')) ?>">Login</a>
                    <a href="<?= e(siteUrl('register.php')) ?>">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <div class="announcement-bar">Free shipping in Kenya for orders over KSH 5,000.</div>
</header>
