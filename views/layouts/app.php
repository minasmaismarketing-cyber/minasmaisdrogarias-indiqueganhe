<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#D71920">
    <title><?= e($title ?? 'Indique e Ganhe') ?></title>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body class="page page--app">
    <?php
    $headerUserName = is_array($user ?? null) ? (string) ($user['nome'] ?? '') : null;
    Header::render(['userName' => $headerUserName]);
    ?>

    <main class="main main--with-nav">
        <div class="container page-content animate-fade">
            <?= $content ?? '' ?>
        </div>
    </main>

    <?php require BASE_PATH . '/views/partials/bottom-nav.php'; ?>
    <?php require BASE_PATH . '/views/partials/ui-shell.php'; ?>

    <script src="<?= asset('js/components.js') ?>"></script>
    <script src="<?= asset('js/app.js') ?>"></script>
    <script src="<?= asset('js/auth.js') ?>"></script>
    <script src="<?= asset('js/dashboard.js') ?>"></script>
</body>
</html>
