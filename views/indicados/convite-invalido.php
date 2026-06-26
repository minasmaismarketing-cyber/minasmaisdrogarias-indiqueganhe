<?php
/** @var string $title */
?>

<div class="container container--centered">
    <div class="error-box">
        <div class="error-icon">🔗</div>
        <h1 class="error-title">Convite Inválido</h1>
        <p class="error-description">
            Desculpe, o link de convite que você acessou não é válido ou já expirou.
        </p>
        <p class="error-info">
            Verifique o link e tente novamente, ou solicite um novo convite ao seu indicador.
        </p>
        <div class="error-actions">
            <a href="<?= url('/') ?>" class="btn btn--primary">Voltar ao Início</a>
        </div>
    </div>
</div>

<style>
.error-box {
    text-align: center;
    padding: 3rem 1.5rem;
    max-width: 500px;
    margin: 4rem auto;
}

.error-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}

.error-title {
    font-size: 1.75rem;
    margin-bottom: 1rem;
    color: #d71920;
    font-weight: 700;
}

.error-description {
    font-size: 1rem;
    margin-bottom: 1rem;
    color: #333;
}

.error-info {
    font-size: 0.875rem;
    margin-bottom: 2rem;
    color: #666;
}

.error-actions {
    display: flex;
    justify-content: center;
    gap: 1rem;
}

.btn--primary {
    background-color: #d71920;
    color: white;
    padding: 0.75rem 2rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
    font-size: 1rem;
}

.btn--primary:hover {
    background-color: #b01419;
}
</style>
