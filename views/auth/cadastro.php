<section class="auth-page animate-slide">
    <div class="mm-card auth-card">
        <h1 class="auth-card__title">Criar conta</h1>
        <p class="auth-card__subtitle">Cadastre-se e comece a indicar amigos.</p>

        <?php if (!empty($referralCode)): ?>
            <div class="alert alert--success">Você foi convidado! Código: <?= e($referralCode) ?></div>
        <?php endif; ?>

        <?php $errors = $errors ?? []; require BASE_PATH . '/views/partials/alerts.php'; ?>

        <form method="POST" action="<?= url('/cadastro') ?>" class="form" id="form-cadastro" novalidate>
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
                <label class="checkbox" for="legal_acceptance">
                    <input
                        type="checkbox"
                        id="legal_acceptance"
                        name="legal_acceptance"
                        value="1"
                        <?= old('legal_acceptance') === '1' ? 'checked' : '' ?>
                        required
                        aria-describedby="legal-acceptance-error"
                    >
                    <span>
                        Declaro que tenho 18 anos ou mais, li e aceito os
                        <a href="<?= e(url('/termos-de-uso')) ?>" target="_blank" rel="noopener noreferrer">Termos de Uso</a>
                        e o
                        <a href="<?= e(url('/regulamento')) ?>" target="_blank" rel="noopener noreferrer">Regulamento da Campanha</a>,
                        e declaro que li a
                        <a href="<?= e(url('/politica-de-privacidade')) ?>" target="_blank" rel="noopener noreferrer">Política de Privacidade</a>.
                    </span>
                </label>
                <?php if (!empty($errors['legal_acceptance'])): ?>
                    <span class="form-error" id="legal-acceptance-error"><?= e($errors['legal_acceptance']) ?></span>
                <?php else: ?>
                    <span class="form-error" id="legal-acceptance-error" hidden></span>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn--block" id="btn-cadastrar" disabled>Cadastrar</button>
        </form>

        <p class="auth-card__footer">Já tem conta? <a href="<?= url('/login') ?>">Entrar</a></p>
    </div>
</section>

<footer class="page-footer">
    <p>© 2026 - Criado por NEXDEN Digital</p>
</footer>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const checkbox = document.getElementById('legal_acceptance');
    const submitBtn = document.getElementById('btn-cadastrar');
    const form = document.getElementById('form-cadastro');
    const errorEl = document.getElementById('legal-acceptance-error');

    if (!checkbox || !submitBtn || !form) {
        return;
    }

    const syncSubmitState = () => {
        submitBtn.disabled = !checkbox.checked;
    };

    checkbox.addEventListener('change', () => {
        syncSubmitState();
        if (checkbox.checked && errorEl) {
            errorEl.hidden = true;
            errorEl.textContent = '';
        }
    });

    form.querySelectorAll('a[target="_blank"]').forEach((link) => {
        link.addEventListener('click', (event) => {
            event.stopPropagation();
        });
    });

    form.addEventListener('submit', (event) => {
        if (!checkbox.checked) {
            event.preventDefault();
            submitBtn.disabled = true;
            if (errorEl) {
                errorEl.hidden = false;
                errorEl.textContent = 'Para criar sua conta, confirme que leu e aceitou os documentos obrigatórios.';
            }
            checkbox.focus();
        }
    });

    syncSubmitState();
});
</script>
