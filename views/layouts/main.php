<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <?php require BASE_PATH . '/views/partials/head-pwa.php'; ?>
    <title><?= e($title ?? 'Indique e Ganhe') ?></title>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body class="page page--public">
    <?php Header::render(); ?>

    <main class="main">
        <div class="container page-content animate-fade">
            <?= $content ?? '' ?>
        </div>
    </main>

    <footer class="footer footer--public">
        <div class="container">
            <p class="footer__text">&copy; 2026 - Criado por NEXDEN Digital</p>
        </div>
    </footer>

    <?php require BASE_PATH . '/views/partials/ui-shell.php'; ?>
    <script src="<?= asset('js/components.js') ?>"></script>
    <script src="<?= asset('js/app.js') ?>"></script>
    <script src="<?= asset('js/auth.js') ?>"></script>
    <?php require BASE_PATH . '/views/partials/pwa-register.php'; ?>
</body>
</html>
