<?php
declare(strict_types=1);

$messages = pullFlashes();
if (empty($messages)) {
    return;
}
?>
<div class="container flash-stack">
    <?php foreach ($messages as $msg): ?>
        <?php $type = $msg['type'] ?? 'info'; ?>
        <div class="flash flash-<?= e($type) ?>">
            <?= e((string)($msg['message'] ?? '')) ?>
        </div>
    <?php endforeach; ?>
</div>
