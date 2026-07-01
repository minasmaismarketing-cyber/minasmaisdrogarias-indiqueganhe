<?php declare(strict_types=1);

/** @var list<array{label: string, value: string, html?: bool, highlight?: bool}> $adminInfoLines */
?>
<ul class="admin-info-lines">
    <?php foreach ($adminInfoLines as $line): ?>
        <li class="admin-info-lines__item">
            <span class="admin-info-lines__label"><?= e($line['label']) ?></span>
            <span class="admin-info-lines__value<?= !empty($line['highlight']) ? ' admin-info-lines__value--highlight' : '' ?>">
                <?php if (!empty($line['html'])): ?>
                    <?= $line['value'] ?>
                <?php else: ?>
                    <?= e($line['value']) ?>
                <?php endif; ?>
            </span>
        </li>
    <?php endforeach; ?>
</ul>
