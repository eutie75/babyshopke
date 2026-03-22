<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Get Started';
include __DIR__ . '/../includes/header.php';
?>
<section class="container section">
    <div class="card get-started-card">
        <h1>Welcome to Baby Shop KE</h1>
        <p>Start your family shopping journey in one click.</p>
        <div class="button-row">
            <a class="btn btn-primary" href="<?= e(siteUrl('register.php')) ?>">Create Account</a>
            <a class="btn btn-outline" href="<?= e(siteUrl('login.php')) ?>">Login</a>
            <a class="btn btn-accent" href="<?= e(siteUrl('index.php')) ?>">Continue Shopping</a>
        </div>
    </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>

