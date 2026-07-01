<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#D71920">
    <title><?= e($title ?? 'Indique e Ganhe') ?></title>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <?php if (!empty($valid) && !empty($smartScriptPayload['enabled']) && !empty($smartScriptPayload['scriptUrl'])): ?>
        <script src="<?= e((string) $smartScriptPayload['scriptUrl']) ?>" defer></script>
    <?php endif; ?>
</head>
<body class="page page--convite">
    <main class="convite-landing-main">
        <?= $content ?? '' ?>
    </main>
    <?php if (!empty($valid) && !empty($smartScriptPayload['enabled'])): ?>
        <script src="<?= asset('js/convite-landing.js') ?>" defer></script>
    <?php endif; ?>
</body>
</html>
