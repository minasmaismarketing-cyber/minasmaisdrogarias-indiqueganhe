<?php declare(strict_types=1);

/** @var string $adminDetailTitle */
/** @var string|null $adminDetailHeaderBadge */
/** @var list<array{label: string, value: string, html?: bool, highlight?: bool}> $adminInfoLines */
?>
<section class="admin-card admin-card--detail">
    <header class="admin-card__header">
        <h2 class="admin-card__title"><?= e($adminDetailTitle) ?></h2>
        <?php if ($adminDetailHeaderBadge !== null): ?>
            <?= $adminDetailHeaderBadge ?>
        <?php endif; ?>
    </header>
    <?php require BASE_PATH . '/views/partials/admin-info-lines.php'; ?>
</section>
