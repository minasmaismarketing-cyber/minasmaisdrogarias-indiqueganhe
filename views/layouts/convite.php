<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#D71920">
    <title><?= e($title ?? 'Indique e Ganhe') ?></title>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body class="page page--convite">
    <main class="convite-landing-main">
        <?= $content ?? '' ?>
    </main>
</body>
</html>
