<?php declare(strict_types=1); ?>
<?php $subtitle = 'Gerencie o fluxo de validações da campanha.'; ?>

<section class="mm-card">
    <div class="mm-card__header">
        <h2 class="mm-card__title">Validações de Indicações</h2>
        <form method="GET" class="admin-filter">
            <select name="filter" onchange="this.form.submit()">
                <option value="PENDENTE" <?= ($filter === 'PENDENTE') ? 'selected' : '' ?>>📋 Pendentes</option>
                <option value="AGUARDANDO_CADASTRO" <?= ($filter === 'AGUARDANDO_CADASTRO') ? 'selected' : '' ?>>📝 Aguardando Cadastro</option>
                <option value="AGUARDANDO_VALIDACAO" <?= ($filter === 'AGUARDANDO_VALIDACAO') ? 'selected' : '' ?>>🔍 Aguardando Validação</option>
                <option value="APROVADO" <?= ($filter === 'APROVADO') ? 'selected' : '' ?>>✅ Aprovados</option>
                <option value="REPROVADO" <?= ($filter === 'REPROVADO') ? 'selected' : '' ?>>❌ Reprovados</option>
                <option value="BENEFICIO_LIBERADO" <?= ($filter === 'BENEFICIO_LIBERADO') ? 'selected' : '' ?>>🎁 Benefícios Liberados</option>
            </select>
            <input type="text" name="q" placeholder="Buscar CPF, Código, Nome" value="<?= e($query) ?>">
            <button class="btn btn--small">Filtrar</button>
        </form>
    </div>

    <?php if (empty($items)): ?>
        <div class="admin-empty">
            <p class="admin-empty__icon">📭</p>
            <p class="admin-empty__text">Nenhuma validação encontrada neste filtro.</p>
        </div>
    <?php else: ?>
        <div class="validacao-admin-list">
            <?php foreach ($items as $it): ?>
                <div class="validacao-admin-card">
                    <div class="validacao-admin-card__header">
                        <div class="validacao-admin-card__status">
                            <span class="status-badge status-badge--<?= strtolower($it['status']) ?>">
                                <?= e(ValidacaoIndicacao::statusLabel($it['status'])) ?>
                            </span>
                        </div>
                        <div class="validacao-admin-card__id">
                            <small>ID: <?= (int) $it['id'] ?> | Indicação: <?= (int) $it['indicacao_id'] ?></small>
                        </div>
                    </div>

                    <div class="validacao-admin-card__body">
                        <div class="validacao-admin-card__info-row">
                            <span class="validacao-admin-card__label">Status:</span>
                            <span class="validacao-admin-card__value"><?= e(ValidacaoIndicacao::statusLabel($it['status'])) ?></span>
                        </div>
                        
                        <?php if (!empty($it['nome_indicado'])): ?>
                            <div class="validacao-admin-card__info-row">
                                <span class="validacao-admin-card__label">Nome indicado:</span>
                                <span class="validacao-admin-card__value"><?= e($it['nome_indicado']) ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($it['motivo_bloqueio'])): ?>
                            <div class="validacao-admin-card__info-row validacao-admin-card__info-row--warning">
                                <span class="validacao-admin-card__label">⚠️ Motivo:</span>
                                <span class="validacao-admin-card__value"><?= e(ValidacaoIndicacao::motivoLabel($it['motivo_bloqueio'])) ?></span>
                            </div>
                        <?php endif; ?>

                        <div class="validacao-admin-card__footer">
                            <small class="validacao-admin-card__date">
                                📅 Criado: <?= e(date('d/m/Y H:i', strtotime($it['created_at']))) ?>
                                <?php if ($it['updated_at'] !== $it['created_at']): ?>
                                    | Atualizado: <?= e(date('d/m/Y H:i', strtotime($it['updated_at']))) ?>
                                <?php endif; ?>
                            </small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<style>
.admin-filter {
    display: flex;
    gap: 0.5rem;
    align-items: center;
    flex-wrap: wrap;
}

.admin-filter select,
.admin-filter input {
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 0.875rem;
}

.admin-empty {
    text-align: center;
    padding: 2rem;
}

.admin-empty__icon {
    font-size: 2rem;
    margin-bottom: 1rem;
}

.admin-empty__text {
    color: #666;
}

.validacao-admin-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.validacao-admin-card {
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: 1rem;
    background-color: #fafafa;
}

.validacao-admin-card__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
    border-bottom: 1px solid #e0e0e0;
    padding-bottom: 0.75rem;
}

.validacao-admin-card__status {
    flex: 1;
}

.validacao-admin-card__id {
    text-align: right;
    color: #999;
    font-size: 0.8rem;
}

.validacao-admin-card__body {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.validacao-admin-card__info-row {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
}

.validacao-admin-card__info-row--warning {
    color: #f57c00;
}

.validacao-admin-card__label {
    font-weight: 600;
    min-width: 120px;
    color: #666;
    font-size: 0.875rem;
}

.validacao-admin-card__value {
    flex: 1;
    color: #333;
}

.validacao-admin-card__footer {
    margin-top: 0.75rem;
    padding-top: 0.75rem;
    border-top: 1px solid #e0e0e0;
    color: #999;
    font-size: 0.8rem;
}

.status-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.875rem;
    font-weight: 600;
}

.status-badge--pendente {
    background-color: #fff3cd;
    color: #856404;
}

.status-badge--aguardando_cadastro,
.status-badge--aguardando_validacao {
    background-color: #cfe2ff;
    color: #084298;
}

.status-badge--aprovado,
.status-badge--beneficio_liberado {
    background-color: #d1e7dd;
    color: #0a3622;
}

.status-badge--reprovado {
    background-color: #f8d7da;
    color: #842029;
}
</style>
