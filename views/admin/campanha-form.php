<?php declare(strict_types=1); ?>
<?php
$errors = $errors ?? [];
$subtitle = isset($campanha)
    ? 'Atualize os detalhes da campanha.'
    : 'Configure os detalhes da nova campanha.';
?>

<section class="mm-card">
    <form method="POST" action="<?= url('/admin/campanhas' . (isset($campanha) ? '/editar/' . $campanha['id'] : '/criar')) ?>" class="form">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="nome">Nome da Campanha</label>
            <input type="text" id="nome" name="nome" value="<?= e(old('nome') ?? $campanha['nome'] ?? '') ?>" required>
            <?php if (!empty($errors['nome'])): ?><span class="form-error"><?= e($errors['nome']) ?></span><?php endif; ?>
        </div>

        <div class="form-group">
            <label for="descricao">Descrição</label>
            <textarea id="descricao" name="descricao" rows="3"><?= e(old('descricao') ?? $campanha['descricao'] ?? '') ?></textarea>
            <?php if (!empty($errors['descricao'])): ?><span class="form-error"><?= e($errors['descricao']) ?></span><?php endif; ?>
        </div>

        <div class="form-group">
            <label for="texto_landing">Texto da Landing Page</label>
            <textarea id="texto_landing" name="texto_landing" rows="3"><?= e(old('texto_landing') ?? $campanha['texto_landing'] ?? '') ?></textarea>
            <?php if (!empty($errors['texto_landing'])): ?><span class="form-error"><?= e($errors['texto_landing']) ?></span><?php endif; ?>
        </div>

        <div class="form-group">
            <label for="texto_botao">Texto do Botão</label>
            <input type="text" id="texto_botao" name="texto_botao" value="<?= e(old('texto_botao') ?? $campanha['texto_botao'] ?? 'Quero participar') ?>">
            <?php if (!empty($errors['texto_botao'])): ?><span class="form-error"><?= e($errors['texto_botao']) ?></span><?php endif; ?>
        </div>

        <div class="form-group">
            <label for="desconto">Desconto</label>
            <input type="number" id="desconto" name="desconto" step="0.01" value="<?= e(old('desconto') ?? $campanha['desconto'] ?? 0) ?>" min="0">
            <?php if (!empty($errors['desconto'])): ?><span class="form-error"><?= e($errors['desconto']) ?></span><?php endif; ?>
        </div>

        <div class="form-group">
            <label for="tipo_desconto">Tipo de Desconto</label>
            <select id="tipo_desconto" name="tipo_desconto">
                <option value="<?= Campanha::TIPO_DESCONTO_PERCENTUAL ?>" <?= (old('tipo_desconto') ?? $campanha['tipo_desconto'] ?? '') === Campanha::TIPO_DESCONTO_PERCENTUAL ? 'selected' : '' ?>>Percentual (%)</option>
                <option value="<?= Campanha::TIPO_DESCONTO_VALOR_FIXO ?>" <?= (old('tipo_desconto') ?? $campanha['tipo_desconto'] ?? '') === Campanha::TIPO_DESCONTO_VALOR_FIXO ? 'selected' : '' ?>>Valor Fixo (R$)</option>
            </select>
            <?php if (!empty($errors['tipo_desconto'])): ?><span class="form-error"><?= e($errors['tipo_desconto']) ?></span><?php endif; ?>
        </div>

        <div class="form-group">
            <label for="valor_minimo_compra">Valor Mínimo de Compra</label>
            <input type="number" id="valor_minimo_compra" name="valor_minimo_compra" step="0.01" value="<?= e(old('valor_minimo_compra') ?? $campanha['valor_minimo_compra'] ?? 0) ?>" min="0">
            <?php if (!empty($errors['valor_minimo_compra'])): ?><span class="form-error"><?= e($errors['valor_minimo_compra']) ?></span><?php endif; ?>
        </div>

        <div class="form-group">
            <label for="limite_indicacoes_usuario">Limite de Indicações por Usuário (0 = ilimitado)</label>
            <input type="number" id="limite_indicacoes_usuario" name="limite_indicacoes_usuario" value="<?= e(old('limite_indicacoes_usuario') ?? $campanha['limite_indicacoes_usuario'] ?? 0) ?>" min="0">
            <?php if (!empty($errors['limite_indicacoes_usuario'])): ?><span class="form-error"><?= e($errors['limite_indicacoes_usuario']) ?></span><?php endif; ?>
        </div>

        <div class="form-group">
            <label for="inicio">Data de Início</label>
            <input type="date" id="inicio" name="inicio" value="<?= e(old('inicio') ?? $campanha['inicio'] ?? '') ?>" required>
            <?php if (!empty($errors['inicio'])): ?><span class="form-error"><?= e($errors['inicio']) ?></span><?php endif; ?>
        </div>

        <div class="form-group">
            <label for="fim">Data de Fim</label>
            <input type="date" id="fim" name="fim" value="<?= e(old('fim') ?? $campanha['fim'] ?? '') ?>" required>
            <?php if (!empty($errors['fim'])): ?><span class="form-error"><?= e($errors['fim']) ?></span><?php endif; ?>
        </div>

        <div class="form-group">
            <label for="cor_primaria">Cor Primária</label>
            <input type="color" id="cor_primaria" name="cor_primaria" value="<?= e(old('cor_primaria') ?? $campanha['cor_primaria'] ?? '#D71920') ?>">
            <?php if (!empty($errors['cor_primaria'])): ?><span class="form-error"><?= e($errors['cor_primaria']) ?></span><?php endif; ?>
        </div>

        <div class="form-group">
            <label for="cor_secundaria">Cor Secundária</label>
            <input type="color" id="cor_secundaria" name="cor_secundaria" value="<?= e(old('cor_secundaria') ?? $campanha['cor_secundaria'] ?? '#7A7A7A') ?>">
            <?php if (!empty($errors['cor_secundaria'])): ?><span class="form-error"><?= e($errors['cor_secundaria']) ?></span><?php endif; ?>
        </div>

        <div class="form-group">
            <label for="banner">URL do Banner (opcional)</label>
            <input type="url" id="banner" name="banner" value="<?= e(old('banner') ?? $campanha['banner'] ?? '') ?>" placeholder="https://...">
            <?php if (!empty($errors['banner'])): ?><span class="form-error"><?= e($errors['banner']) ?></span><?php endif; ?>
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="<?= Campanha::STATUS_INATIVA ?>" <?= (old('status') ?? $campanha['status'] ?? '') === Campanha::STATUS_INATIVA ? 'selected' : '' ?>>Inativa</option>
                <option value="<?= Campanha::STATUS_ATIVA ?>" <?= (old('status') ?? $campanha['status'] ?? '') === Campanha::STATUS_ATIVA ? 'selected' : '' ?>>Ativa</option>
                <option value="<?= Campanha::STATUS_AGENDADA ?>" <?= (old('status') ?? $campanha['status'] ?? '') === Campanha::STATUS_AGENDADA ? 'selected' : '' ?>>Agendada</option>
                <option value="<?= Campanha::STATUS_FINALIZADA ?>" <?= (old('status') ?? $campanha['status'] ?? '') === Campanha::STATUS_FINALIZADA ? 'selected' : '' ?>>Finalizada</option>
            </select>
            <?php if (!empty($errors['status'])): ?><span class="form-error"><?= e($errors['status']) ?></span><?php endif; ?>
        </div>

        <button type="submit" class="btn btn--block btn--primary">
            <?= isset($campanha) ? 'Atualizar Campanha' : 'Criar Campanha' ?>
        </button>
    </form>
</section>

<a href="<?= url('/admin/campanhas') ?>" class="btn btn--block btn--ghost">Voltar</a>
