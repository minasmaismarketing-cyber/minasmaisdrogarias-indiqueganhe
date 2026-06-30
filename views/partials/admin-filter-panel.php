<?php declare(strict_types=1);

/** @var string $filterPanelId */
/** @var bool $filterPanelOpen */
/** @var callable $renderForm */
?>
<section class="admin-filter-panel mm-card<?= $filterPanelOpen ? ' is-open' : '' ?>" data-admin-filter-panel>
    <button
        type="button"
        class="admin-filter-panel__toggle"
        aria-expanded="<?= $filterPanelOpen ? 'true' : 'false' ?>"
        aria-controls="<?= e($filterPanelId) ?>"
        data-admin-filter-toggle
    >
        <span class="admin-filter-panel__toggle-label">🔎 FILTRAR</span>
        <span class="admin-filter-panel__toggle-icon" aria-hidden="true"></span>
    </button>

    <div class="admin-filter-panel__body" id="<?= e($filterPanelId) ?>">
        <?php $renderForm(); ?>
    </div>
</section>
