<?php declare(strict_types=1); ?>
<aside class="admin-sidebar" aria-label="Menu administrativo">
    <div class="admin-sidebar__brand">
        <a href="<?= url('/admin') ?>" class="admin-sidebar__logo">
            <img src="<?= e(brand_logo_url()) ?>" alt="" width="120" height="36" loading="eager">
        </a>
        <span class="admin-sidebar__label">Painel Admin</span>
    </div>

    <nav class="admin-sidebar__nav" aria-label="Navegação principal">
        <ul class="admin-sidebar__list">
            <?php foreach (admin_nav_items() as $item): ?>
                <?php $active = admin_nav_is_active($item['path']); ?>
                <li class="admin-sidebar__item">
                    <a
                        href="<?= url($item['path']) ?>"
                        class="admin-sidebar__link<?= $active ? ' is-active' : '' ?>"
                        <?= $active ? 'aria-current="page"' : '' ?>
                    >
                        <?= e($item['label']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <div class="admin-sidebar__footer">
        <a href="<?= url('/dashboard') ?>" class="admin-sidebar__link admin-sidebar__link--muted">Voltar ao app</a>
    </div>
</aside>
