<section class="auth-page animate-slide">
    <div class="mm-card auth-card">
        <h1 class="auth-card__title">Recuperar senha</h1>
        <p class="auth-card__subtitle">Informe CPF ou e-mail cadastrado.</p>

        <?php $errors = $errors ?? []; require BASE_PATH . '/views/partials/alerts.php'; ?>

        <?php if (!empty($resetLink)): ?>
            <div class="alert alert--warning">
                Modo debug — link de redefinição:<br>
                <a href="<?= e($resetLink) ?>"><?= e($resetLink) ?></a>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= url('/esqueci-senha') ?>" class="form" novalidate>
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="login">CPF ou E-mail</label>
                <input type="text" id="login" name="login" value="<?= e(old('login')) ?>" required>
                <?php if (!empty($errors['login'])): ?><span class="form-error"><?= e($errors['login']) ?></span><?php endif; ?>
            </div>

            <button type="submit" class="btn btn--block">Enviar solicitação</button>
        </form>

        <p class="auth-card__footer"><a href="<?= url('/login') ?>">Voltar ao login</a></p>
    </div>
</section>
