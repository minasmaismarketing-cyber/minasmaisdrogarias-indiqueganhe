<?php declare(strict_types=1);

/** @var string $content */
/** @var string|null $title */
/** @var string|null $subtitle */

$adminUser = Auth::user();
$adminUserName = is_array($adminUser) ? (string) ($adminUser['nome'] ?? 'Administrador') : 'Administrador';
$pageTitle = $title ?? 'Admin';
$errors = $errors ?? [];
$errorMessage = $error ?? Session::flash('error');
$successMessage = $success ?? Session::flash('success');
$breadcrumbs = admin_breadcrumbs();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#D71920">
    <title><?= e($pageTitle) ?> — Admin · Indique e Ganhe</title>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/admin-design-system.css') ?>">
</head>
<body class="page page--admin">
    <div class="admin-shell">
        <?php require BASE_PATH . '/views/partials/admin-sidebar.php'; ?>

        <div class="admin-shell__main">
            <header class="admin-header">
                <button type="button" class="admin-header__toggle" aria-label="Abrir menu" data-admin-nav-toggle>
                    <span></span>
                    <span></span>
                    <span></span>
                </button>

                <div class="admin-header__info">
                    <span class="admin-header__eyebrow">Indique e Ganhe</span>
                    <strong class="admin-header__user"><?= e($adminUserName) ?></strong>
                </div>

                <div class="admin-header__actions">
                    <a href="<?= url('/dashboard') ?>" class="btn btn--sm btn--ghost">App</a>
                    <form method="POST" action="<?= url('/logout') ?>" class="inline-form">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn--sm btn--outline">Sair</button>
                    </form>
                </div>
            </header>

            <?php if ($breadcrumbs !== []): ?>
                <nav class="admin-breadcrumb" aria-label="Breadcrumb">
                    <ol class="admin-breadcrumb__list">
                        <?php foreach ($breadcrumbs as $index => $crumb): ?>
                            <li class="admin-breadcrumb__item">
                                <?php if ($crumb['url'] !== null && $index < count($breadcrumbs) - 1): ?>
                                    <a href="<?= e($crumb['url']) ?>"><?= e($crumb['label']) ?></a>
                                <?php else: ?>
                                    <span aria-current="page"><?= e($crumb['label']) ?></span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </nav>
            <?php endif; ?>

            <main class="admin-content">
                <header class="admin-page-head">
                    <h1 class="admin-page-head__title"><?= e($pageTitle) ?></h1>
                    <?php if (!empty($subtitle)): ?>
                        <p class="admin-page-head__subtitle"><?= e($subtitle) ?></p>
                    <?php endif; ?>
                </header>

                <?php require BASE_PATH . '/views/partials/alerts.php'; ?>
                <?php if (!empty($errorMessage)): ?>
                    <div class="alert alert--error"><?= e((string) $errorMessage) ?></div>
                <?php endif; ?>

                <div class="admin-page-body animate-fade">
                    <?= $content ?? '' ?>
                </div>
            </main>
        </div>
    </div>

    <div class="admin-sidebar-backdrop" data-admin-nav-backdrop hidden></div>

    <script src="<?= asset('js/components.js') ?>"></script>
    <script src="<?= asset('js/admin-filters.js') ?>"></script>
    <script src="<?= asset('js/app.js') ?>"></script>
    <script>
        (function () {
            var toggle = document.querySelector('[data-admin-nav-toggle]');
            var backdrop = document.querySelector('[data-admin-nav-backdrop]');
            var shell = document.querySelector('.admin-shell');
            if (!toggle || !shell) return;
            function setNavOpen(open) {
                shell.classList.toggle('is-nav-open', open);
                document.body.classList.toggle('is-nav-locked', open);
                if (backdrop) backdrop.hidden = !open;
            }
            function closeNav() {
                setNavOpen(false);
            }
            toggle.addEventListener('click', function () {
                setNavOpen(!shell.classList.contains('is-nav-open'));
            });
            if (backdrop) backdrop.addEventListener('click', closeNav);
            window.addEventListener('resize', function () {
                if (window.matchMedia('(min-width: 1025px)').matches) {
                    closeNav();
                }
            });
        })();
    </script>
</body>
</html>
