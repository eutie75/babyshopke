<?php
declare(strict_types=1);

if (!isLoggedIn()) {
    flash('error', 'Please login to continue.');
    redirect('login.php');
}
?>
