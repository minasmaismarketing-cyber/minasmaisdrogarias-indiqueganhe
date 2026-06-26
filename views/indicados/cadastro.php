<?php declare(strict_types=1); ?>
<?php $errors = $errors ?? []; $old = $old ?? []; require BASE_PATH . '/views/partials/alerts.php'; ?>

<section class="page-hero">
    <h1 class="page-hero__title">Cadastre-se</h1>
    <p class="page-hero__subtitle">Complete seu cadastro para participar da campanha.</p>
</section>

<section class="mm-card">
    <div class="mm-card__header">
        <h2 class="mm-card__title">Formulário de Cadastro</h2>
    </div>
    
    <form method="POST" action="<?= url('/salvar-indicado') ?>" class="form">
        <?= csrf_field() ?>
        <input type="hidden" name="codigo" value="<?= e($codigo) ?>">
        
        <div class="form-group">
            <label for="nome">Nome Completo</label>
            <input type="text" id="nome" name="nome" value="<?= e($old['nome'] ?? '') ?>" required>
            <?php if (!empty($errors['nome'])): ?><span class="form-error"><?= e($errors['nome']) ?></span><?php endif; ?>
        </div>
        
        <div class="form-group">
            <label for="cpf">CPF</label>
            <input type="text" id="cpf" name="cpf" value="<?= e($old['cpf'] ?? '') ?>" placeholder="000.000.000-00" required>
            <?php if (!empty($errors['cpf'])): ?><span class="form-error"><?= e($errors['cpf']) ?></span><?php endif; ?>
        </div>
        
        <div class="form-group">
            <label for="telefone">Telefone</label>
            <input type="tel" id="telefone" name="telefone" value="<?= e($old['telefone'] ?? '') ?>" placeholder="(00) 00000-0000" required>
            <?php if (!empty($errors['telefone'])): ?><span class="form-error"><?= e($errors['telefone']) ?></span><?php endif; ?>
        </div>
        
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= e($old['email'] ?? '') ?>" required>
            <?php if (!empty($errors['email'])): ?><span class="form-error"><?= e($errors['email']) ?></span><?php endif; ?>
        </div>
        
        <div class="form-group">
            <label for="senha">Senha</label>
            <input type="password" id="senha" name="senha" placeholder="Mínimo 6 caracteres" required>
            <?php if (!empty($errors['senha'])): ?><span class="form-error"><?= e($errors['senha']) ?></span><?php endif; ?>
        </div>
        
        <div class="form-group form-group--checkbox">
            <label class="checkbox-label">
                <input type="checkbox" name="aceite_lgpd" value="1" <?= !empty($old['aceite_lgpd']) ? 'checked' : '' ?> required>
                <span>Li e aceito os termos de uso e política de privacidade.</span>
            </label>
            <?php if (!empty($errors['aceite_lgpd'])): ?><span class="form-error"><?= e($errors['aceite_lgpd']) ?></span><?php endif; ?>
        </div>
        
        <button type="submit" class="btn btn--block btn--primary btn--lg">
            Concluir Cadastro
        </button>
    </form>
</section>

<section class="mm-card">
    <div class="mm-card__header">
        <h2 class="mm-card__title">Informações</h2>
    </div>
    
    <p class="mm-card__text">
        Seus dados serão utilizados apenas para fins de validação e contato sobre a campanha.
    </p>
    
    <p class="mm-card__text">
        Ao se cadastrar, você concorda com os termos de uso e política de privacidade da Minas Mais.
    </p>
</section>

<script>
document.getElementById('cpf').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 11) value = value.slice(0, 11);
    
    if (value.length > 9) {
        value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
    } else if (value.length > 6) {
        value = value.replace(/(\d{3})(\d{3})(\d{3})/, '$1.$2.$3');
    } else if (value.length > 3) {
        value = value.replace(/(\d{3})(\d{3})/, '$1.$2');
    }
    
    e.target.value = value;
});

document.getElementById('telefone').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 11) value = value.slice(0, 11);
    
    if (value.length > 10) {
        value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
    } else if (value.length > 6) {
        value = value.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
    } else if (value.length > 2) {
        value = value.replace(/(\d{2})(\d{4})/, '($1) $2');
    }
    
    e.target.value = value;
});
</script>
