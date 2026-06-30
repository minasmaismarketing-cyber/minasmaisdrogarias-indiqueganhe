<?php

declare(strict_types=1);
?>
<section class="page-hero animate-slide">
    <h1 class="page-hero__title">Configurações</h1>
    <p class="page-hero__subtitle">Atualize seus dados de contato.</p>
</section>

<?php $errors = $errors ?? []; require BASE_PATH . '/views/partials/alerts.php'; ?>

<section class="mm-card">
    <form method="POST" action="<?= url('/configuracoes') ?>" class="form">
        <?= csrf_field() ?>

        <div class="form-group">
            <label>CPF</label>
            <input type="text" value="<?= e(format_cpf((string) $user['cpf'])) ?>" disabled class="input-disabled">
            <small class="form-help">CPF não pode ser alterado</small>
        </div>

        <div class="form-group">
            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" value="<?= e((string) $user['email']) ?>" required>
            <?php if (!empty($errors['email'])): ?><span class="form-error"><?= e($errors['email']) ?></span><?php endif; ?>
        </div>

        <div class="form-group">
            <label for="whatsapp">WhatsApp</label>
            <input type="text" id="whatsapp" name="whatsapp" value="<?= e((string) $user['whatsapp']) ?>" inputmode="tel" required>
            <?php if (!empty($errors['whatsapp'])): ?><span class="form-error"><?= e($errors['whatsapp']) ?></span><?php endif; ?>
        </div>

        <div class="form-group">
            <label>Código indicador</label>
            <input type="text" value="<?= e((string) $user['codigo_indicador']) ?>" disabled class="input-disabled">
            <small class="form-help">Código de indicação não pode ser alterado</small>
        </div>

        <button type="submit" class="btn btn--block">Salvar alterações</button>
    </form>
</section>

<section class="mm-card">
    <h3 class="mm-card__title">Links rápidos</h3>
    <nav class="quick-links">
        <a href="<?= url('/perfil') ?>" class="quick-links__item">
            <span>👤</span>
            <span>Meu perfil</span>
        </a>
        <a href="<?= url('/minhas-indicacoes') ?>" class="quick-links__item">
            <span>📋</span>
            <span>Minhas indicações</span>
        </a>
        <a href="<?= url('/meus-premios') ?>" class="quick-links__item">
            <span>🎁</span>
            <span>Meus prêmios</span>
        </a>
    </nav>
</section>
