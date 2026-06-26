<section class="auth-page animate-slide">
    <div class="mm-card auth-card">
        <h1 class="auth-card__title">Entrar</h1>
        <p class="auth-card__subtitle">Acesse com CPF ou e-mail.</p>

        <?php $errors = $errors ?? []; require BASE_PATH . '/views/partials/alerts.php'; ?>

        <form method="POST" action="<?= url('/login') ?>" class="form" novalidate>
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="login">CPF ou E-mail</label>
                <input type="text" id="login" name="login" value="<?= e(old('login')) ?>" required autocomplete="username">
                <?php if (!empty($errors['login'])): ?><span class="form-error"><?= e($errors['login']) ?></span><?php endif; ?>
            </div>

            <div class="form-group">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" required autocomplete="current-password">
                <?php if (!empty($errors['senha'])): ?><span class="form-error"><?= e($errors['senha']) ?></span><?php endif; ?>
            </div>

            <div class="form-group form-check">
                <label class="checkbox">
                    <input type="checkbox" name="lembrar" value="1">
                    <span>Lembrar login</span>
                </label>
            </div>

            <button type="submit" class="btn btn--block">Entrar</button>
        </form>

        <p class="auth-card__footer">
            <a href="<?= url('/esqueci-senha') ?>">Esqueci minha senha</a><br>
            <a href="<?= url('/cadastro') ?>">Criar conta</a>
        </p>
    </div>
</section>

<footer class="page-footer">
    <p>© 2026 - Criado por NEXDEN Digital</p>
</footer>
