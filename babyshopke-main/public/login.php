<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/csrf.php';
require_once __DIR__ . '/../controllers/auth_controller.php';

if (isLoggedIn()) {
    redirect(isAdmin() ? 'admin/dashboard.php' : 'account.php');
}

handleLoginSubmission();

$pageTitle = 'Login';
include __DIR__ . '/../includes/header.php';
?>
<section class="container auth-section">
    <div class="auth-card card">
        <h1>Login to Baby Shop KE</h1>
        <form method="POST">
            <?= csrfField() ?>
            <label>Email</label>
            <input type="email" name="email" required>
            <label>Password</label>
            <input type="password" name="password" required>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
        <div class="inline-links">
            <a href="<?= e(siteUrl('register.php')) ?>">Create Account</a>
            <a href="<?= e(siteUrl('getstarted.php')) ?>">Get Started</a>
        </div>
    </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
