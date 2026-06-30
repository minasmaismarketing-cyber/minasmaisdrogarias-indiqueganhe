<section class="auth-page animate-slide">
    <div class="mm-card auth-card">
        <h1 class="auth-card__title">Criar conta</h1>
        <p class="auth-card__subtitle">Cadastre-se e comece a indicar amigos.</p>

        <?php if (!empty($referralCode)): ?>
            <div class="alert alert--success">Você foi convidado! Código: <?= e($referralCode) ?></div>
        <?php endif; ?>

        <?php $errors = $errors ?? []; require BASE_PATH . '/views/partials/alerts.php'; ?>

        <form method="POST" action="<?= url('/cadastro') ?>" class="form" novalidate>
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="nome">Nome</label>
                <input type="text" id="nome" name="nome" value="<?= e(old('nome')) ?>" required autocomplete="name">
                <?php if (!empty($errors['nome'])): ?><span class="form-error"><?= e($errors['nome']) ?></span><?php endif; ?>
            </div>

            <div class="form-group">
                <label for="cpf">CPF</label>
                <input type="text" id="cpf" name="cpf" value="<?= e(old('cpf')) ?>" inputmode="numeric" placeholder="000.000.000-00" required>
                <?php if (!empty($errors['cpf'])): ?><span class="form-error"><?= e($errors['cpf']) ?></span><?php endif; ?>
            </div>

            <div class="form-group">
                <label for="whatsapp">WhatsApp</label>
                <input type="text" id="whatsapp" name="whatsapp" value="<?= e(old('whatsapp')) ?>" inputmode="tel" placeholder="(00) 00000-0000" required>
                <?php if (!empty($errors['whatsapp'])): ?><span class="form-error"><?= e($errors['whatsapp']) ?></span><?php endif; ?>
            </div>

            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" value="<?= e(old('email')) ?>" required autocomplete="email">
                <?php if (!empty($errors['email'])): ?><span class="form-error"><?= e($errors['email']) ?></span><?php endif; ?>
            </div>

            <div class="form-group">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" required autocomplete="new-password">
                <small class="form-hint">Mín. 8 caracteres, 1 letra e 1 número</small>
                <?php if (!empty($errors['senha'])): ?><span class="form-error"><?= e($errors['senha']) ?></span><?php endif; ?>
            </div>

            <div class="form-group">
                <label for="confirmar_senha">Confirmar senha</label>
                <input type="password" id="confirmar_senha" name="confirmar_senha" required autocomplete="new-password">
                <?php if (!empty($errors['confirmar_senha'])): ?><span class="form-error"><?= e($errors['confirmar_senha']) ?></span><?php endif; ?>
            </div>

            <div class="form-group form-check">
                <label class="checkbox">
                    <input type="checkbox" name="aceite_lgpd" value="1" <?= old('aceite_lgpd') === '1' ? 'checked' : '' ?> required>
                    <span>Li e aceito os termos da LGPD</span>
                </label>
                <?php if (!empty($errors['aceite_lgpd'])): ?><span class="form-error"><?= e($errors['aceite_lgpd']) ?></span><?php endif; ?>
            </div>

            <button type="submit" class="btn btn--block">Cadastrar</button>
        </form>

        <p class="auth-card__footer">Já tem conta? <a href="<?= url('/login') ?>">Entrar</a></p>
    </div>
</section>

<footer class="page-footer">
    <p>© 2026 - Criado por NEXDEN Digital</p>
</footer>
