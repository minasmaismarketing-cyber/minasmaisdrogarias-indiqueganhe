<?php declare(strict_types=1);

/** @var string $adminTableTitle */
/** @var string|null $adminTableMeta */
/** @var string|null $adminTableHeaderActions */
/** @var string $adminTableEmptyMessage */
/** @var list<array{label: string, class?: string, align?: string}> $adminTableColumns */
/** @var list<mixed> $adminTableRows */
/** @var bool $adminTableZebra */
/** @var string $adminTableClass */
/** @var callable(mixed, int): void $adminTableRenderRow */
?>
<section class="mm-card mm-card--admin-table">
    <div class="mm-card__header">
        <h2 class="mm-card__title"><?= e($adminTableTitle) ?></h2>
        <?php if ($adminTableMeta !== null || $adminTableHeaderActions !== null): ?>
            <div class="mm-card__header-end">
                <?php if ($adminTableMeta !== null): ?>
                    <span class="mm-card__meta"><?= e($adminTableMeta) ?></span>
                <?php endif; ?>
                <?php if ($adminTableHeaderActions !== null): ?>
                    <?= $adminTableHeaderActions ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($adminTableRows === []): ?>
        <p class="mm-card__placeholder"><?= e($adminTableEmptyMessage) ?></p>
    <?php else: ?>
        <div class="admin-table__scroll">
            <table class="<?= e($adminTableClass) ?><?= $adminTableZebra ? ' admin-table--zebra' : '' ?>">
                <thead>
                    <tr>
                        <?php foreach ($adminTableColumns as $column): ?>
                            <?php
                            $thClass = 'admin-table__th';
                            if (!empty($column['class'])) {
                                $thClass .= ' ' . $column['class'];
                            }
                            $align = $column['align'] ?? 'left';
                            ?>
                            <th class="<?= e($thClass) ?>" style="text-align: <?= e($align) ?>">
                                <?= e($column['label']) ?>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($adminTableRows as $index => $row): ?>
                        <?php $adminTableRenderRow($row, (int) $index); ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
