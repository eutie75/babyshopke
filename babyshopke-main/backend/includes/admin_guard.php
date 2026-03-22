<?php
declare(strict_types=1);

if (!isAdmin()) {
    flash('error', 'Admin access required.');
    redirect('index.php');
}
?>
