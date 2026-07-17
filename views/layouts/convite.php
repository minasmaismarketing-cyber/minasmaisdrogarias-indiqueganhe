<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <?php require BASE_PATH . '/views/partials/head-pwa.php'; ?>
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
        <script>
            window.__CONVITE_AF__ = <?= json_encode($smartScriptPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        </script>
        <script src="<?= asset('js/convite-landing.js') ?>" defer></script>
    <?php endif; ?>
    <?php if (!empty($valid)): ?>
        <script src="<?= asset('js/convite-landing-ui.js') ?>" defer></script>
    <?php endif; ?>
    <?php require BASE_PATH . '/views/partials/pwa-register.php'; ?>
</body>
</html>
