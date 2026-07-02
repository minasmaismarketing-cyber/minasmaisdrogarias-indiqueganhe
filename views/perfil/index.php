<section class="page-hero animate-slide">
    <h1 class="page-hero__title">Meu perfil</h1>
    <p class="page-hero__subtitle">Gerencie seus dados de cadastro.</p>
</section>

<?php $errors = $errors ?? []; require BASE_PATH . '/views/partials/alerts.php'; ?>
<?php if (!empty($errors['senha'])): ?>
    <div class="alert alert--error"><?= e($errors['senha']) ?></div>
<?php endif; ?>

<section class="mm-card">
    <div class="profile-header">
        <img src="<?= e(brand_logo_url()) ?>" alt="" class="profile-logo">
        <div class="profile-info">
            <h2 class="profile-name"><?= e((string) $user['nome']) ?></h2>
            <p class="profile-since">Membro desde: <?= e(date('d/m/Y', strtotime((string) $user['created_at']))) ?></p>
        </div>
    </div>
    
    <div class="campaign-info">
        <div class="campaign-info__item">
            <span class="campaign-info__label">Aceite campanha:</span>
            <span class="campaign-info__value campaign-info__value--success">Sim</span>
        </div>
        <div class="campaign-info__item">
            <span class="campaign-info__label">Data cadastro:</span>
            <span class="campaign-info__value"><?= e(date('d/m/Y', strtotime((string) $user['created_at']))) ?></span>
        </div>
        <div class="campaign-info__item">
            <span class="campaign-info__label">Status participação:</span>
            <span class="campaign-info__value campaign-info__value--active">Ativo</span>
        </div>
    </div>
</section>

<section class="mm-card">
    <form method="POST" action="<?= url('/perfil') ?>" class="form">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="nome">Nome</label>
            <input type="text" id="nome" name="nome" value="<?= e((string) $user['nome']) ?>" required>
            <?php if (!empty($errors['nome'])): ?><span class="form-error"><?= e($errors['nome']) ?></span><?php endif; ?>
        </div>

        <div class="form-group">
            <label>CPF</label>
            <input type="text" value="<?= e(format_cpf((string) $user['cpf'])) ?>" disabled class="input-disabled">
        </div>

        <div class="form-group">
            <label>E-mail</label>
            <input type="email" value="<?= e((string) $user['email']) ?>" disabled class="input-disabled">
        </div>

        <div class="form-group">
            <label for="whatsapp">WhatsApp</label>
            <input type="text" id="whatsapp" name="whatsapp" value="<?= e((string) $user['whatsapp']) ?>" inputmode="tel" required>
            <?php if (!empty($errors['whatsapp'])): ?><span class="form-error"><?= e($errors['whatsapp']) ?></span><?php endif; ?>
        </div>

        <div class="form-group">
            <label>Código indicador</label>
            <input type="text" value="<?= e((string) $user['codigo_indicador']) ?>" disabled class="input-disabled">
        </div>

        <button type="submit" class="btn btn--block btn--primary">Salvar alterações</button>
    </form>
</section>

<section class="mm-card">
    <h3 class="mm-card__title">Trocar senha</h3>
    <form method="POST" action="<?= url('/perfil/senha') ?>" class="form">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="senha_atual">Senha atual</label>
            <input type="password" id="senha_atual" name="senha_atual" required>
            <?php if (!empty($errors['senha_atual'])): ?><span class="form-error"><?= e($errors['senha_atual']) ?></span><?php endif; ?>
        </div>

        <div class="form-group">
            <label for="nova_senha">Nova senha</label>
            <input type="password" id="nova_senha" name="nova_senha" required>
            <?php if (!empty($errors['nova_senha'])): ?><span class="form-error"><?= e($errors['nova_senha']) ?></span><?php endif; ?>
        </div>

        <div class="form-group">
            <label for="confirmar_senha">Confirmar nova senha</label>
            <input type="password" id="confirmar_senha" name="confirmar_senha" required>
            <?php if (!empty($errors['confirmar_senha'])): ?><span class="form-error"><?= e($errors['confirmar_senha']) ?></span><?php endif; ?>
        </div>

        <button type="submit" class="btn btn--block btn--outline">Trocar senha</button>
    </form>
</section>

<form method="POST" action="<?= url('/logout') ?>" class="logout-form">
    <?= csrf_field() ?>
    <button type="submit" class="btn btn--block btn--ghost">Sair da conta</button>
</form>

<section class="mm-card mm-card--danger">
    <h3 class="mm-card__title">Excluir minha conta</h3>
    <p class="mm-card__text">Esta ação não pode ser desfeita. Seus dados serão desativados mas mantidos para fins de registro.</p>
    <button type="button" class="btn btn--block btn--danger" id="btn-delete-account">Excluir minha conta</button>
</section>

<div id="delete-modal" class="modal" style="display: none;" role="dialog" aria-modal="true" aria-labelledby="delete-modal-title">
    <div class="modal__overlay"></div>
    <div class="modal__content">
        <h3 class="modal__title" id="delete-modal-title">Confirmar exclusão de conta</h3>
        <p class="modal__text">Tem certeza que deseja excluir sua conta? Esta ação não pode ser desfeita.</p>
        <form method="POST" action="<?= url('/perfil/excluir') ?>" class="form">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="senha">Digite sua senha para confirmar</label>
                <input type="password" id="senha" name="senha" required>
                <?php if (!empty($errors['senha'])): ?><span class="form-error"><?= e($errors['senha']) ?></span><?php endif; ?>
            </div>
            <div class="modal__actions">
                <button type="button" class="btn btn--ghost" id="btn-cancel-delete">Cancelar</button>
                <button type="submit" class="btn btn--danger">Confirmar exclusão</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('btn-delete-account').addEventListener('click', function() {
    document.getElementById('delete-modal').style.display = 'flex';
});

document.getElementById('btn-cancel-delete').addEventListener('click', function() {
    document.getElementById('delete-modal').style.display = 'none';
});

document.querySelector('.modal__overlay').addEventListener('click', function() {
    document.getElementById('delete-modal').style.display = 'none';
});

<?php if (!empty($errors['senha'])): ?>
document.getElementById('delete-modal').style.display = 'flex';
<?php endif; ?>
</script>

