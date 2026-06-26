<?php

declare(strict_types=1);

/**
 * Entrada do painel admin — será implementado na Etapa 14.
 */

require dirname(__DIR__) . '/config/bootstrap.php';

http_response_code(503);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — Em construção</title>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body>
    <main class="main error-page">
        <div class="container">
            <h1>Painel Admin</h1>
            <p>Em construção — Etapa 14.</p>
            <a href="<?= url('/') ?>" class="btn">Voltar ao início</a>
        </div>
    </main>
</body>
</html>
