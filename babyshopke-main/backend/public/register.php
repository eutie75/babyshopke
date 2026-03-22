<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/csrf.php';
require_once __DIR__ . '/../controllers/auth_controller.php';

if (isLoggedIn()) {
    redirect(isAdmin() ? 'admin/dashboard.php' : 'account.php');
}

handleRegisterSubmission();

$pageTitle = 'Register';
include __DIR__ . '/../includes/header.php';
?>
<section class="container auth-section">
    <div class="auth-card card">
        <h1>Create Account</h1>
        <form method="POST">
            <?= csrfField() ?>
            <label>Full Name</label>
            <input type="text" name="full_name" required maxlength="100">
            <label>Email</label>
            <input type="email" name="email" required maxlength="255">
            <label>Password (min 8 chars)</label>
            <input type="password" name="password" required minlength="8">
            <label>Confirm Password</label>
            <input type="password" name="confirm_password" required>
            <button type="submit" class="btn btn-primary">Register</button>
        </form>
        <div class="inline-links">
            <a href="<?= e(siteUrl('login.php')) ?>">Login</a>
            <a href="<?= e(siteUrl('getstarted.php')) ?>">Get Started</a>
        </div>
    </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
