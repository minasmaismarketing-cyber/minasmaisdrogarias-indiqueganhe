<section class="auth-page animate-slide">
    <div class="mm-card auth-card">
        <h1 class="auth-card__title">Redefinir senha</h1>
        <p class="auth-card__subtitle">Digite sua nova senha.</p>

        <?php $errors = $errors ?? []; require BASE_PATH . '/views/partials/alerts.php'; ?>

        <form method="POST" action="<?= url('/redefinir-senha') ?>" class="form" novalidate>
            <?= csrf_field() ?>
            <input type="hidden" name="token" value="<?= e($token) ?>">

            <div class="form-group">
                <label for="senha">Nova senha</label>
                <input type="password" id="senha" name="senha" required autocomplete="new-password">
                <small class="form-hint">Mín. 8 caracteres, 1 letra e 1 número</small>
                <?php if (!empty($errors['senha'])): ?><span class="form-error"><?= e($errors['senha']) ?></span><?php endif; ?>
            </div>

            <div class="form-group">
                <label for="confirmar_senha">Confirmar senha</label>
                <input type="password" id="confirmar_senha" name="confirmar_senha" required autocomplete="new-password">
                <?php if (!empty($errors['confirmar_senha'])): ?><span class="form-error"><?= e($errors['confirmar_senha']) ?></span><?php endif; ?>
            </div>

            <button type="submit" class="btn btn--block">Salvar nova senha</button>
        </form>
    </div>
</section>
