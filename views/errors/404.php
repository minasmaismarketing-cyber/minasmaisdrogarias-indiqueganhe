<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#D71920">
    <title>Página não encontrada</title>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body class="page page--public">
    <main class="main">
        <div class="container error-page animate-fade">
            <p class="error-page__code">404</p>
            <h1>Página não encontrada</h1>
            <p class="error-page__text">O conteúdo que você procura não existe ou foi movido.</p>
            <a href="<?= url('/') ?>" class="btn btn--block">Voltar ao início</a>
        </div>
    </main>
</body>
</html>
