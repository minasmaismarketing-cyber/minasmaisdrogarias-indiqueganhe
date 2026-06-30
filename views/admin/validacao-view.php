<?php declare(strict_types=1); ?>
<?php $subtitle = 'Consulte informações e histórico da validação.'; ?>

<section class="mm-card">
    <div class="mm-card__header">
        <h2 class="mm-card__title">Informações da Validação</h2>
        <span class="badge badge--<?= strtolower($validacao['status']) ?>">
            <?= Validacao::statusIcon($validacao['status']) ?>
            <?= e(Validacao::statusLabel($validacao['status'])) ?>
        </span>
    </div>

    <div class="info-grid">
        <div class="info-grid__item">
            <span class="info-grid__label">ID</span>
            <span class="info-grid__value"><?= $validacao['id'] ?></span>
        </div>
        <div class="info-grid__item">
            <span class="info-grid__label">Usuário</span>
            <span class="info-grid__value"><?= e($validacao['usuario_nome'] ?? 'N/A') ?></span>
        </div>
        <div class="info-grid__item">
            <span class="info-grid__label">Indicação ID</span>
            <span class="info-grid__value"><?= $validacao['indicacao_id'] ?></span>
        </div>
        <div class="info-grid__item">
            <span class="info-grid__label">Criado em</span>
            <span class="info-grid__value"><?= e(date('d/m/Y H:i:s', strtotime($validacao['created_at']))) ?></span>
        </div>
        <?php if ($validacao['validado_em']): ?>
            <div class="info-grid__item">
                <span class="info-grid__label">Validado em</span>
                <span class="info-grid__value"><?= e(date('d/m/Y H:i:s', strtotime($validacao['validado_em']))) ?></span>
            </div>
        <?php endif; ?>
        <?php if ($validacao['motivo']): ?>
            <div class="info-grid__item info-grid__item--full">
                <span class="info-grid__label">Motivo</span>
                <span class="info-grid__value"><?= e($validacao['motivo']) ?></span>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="mm-card">
    <div class="mm-card__header">
        <h2 class="mm-card__title">Histórico de Alterações</h2>
    </div>

    <?php if ($history === []): ?>
        <p class="mm-card__placeholder">Nenhuma alteração registrada.</p>
    <?php else: ?>
        <ul class="timeline">
            <?php foreach ($history as $item): ?>
                <li class="timeline__item">
                    <span class="timeline__icon">
                        <?= Validacao::statusIcon($item['status_novo']) ?>
                    </span>
                    <div class="timeline__content">
                        <span class="timeline__label">
                            <?= e(Validacao::statusLabel($item['status_anterior'])) ?> → 
                            <?= e(Validacao::statusLabel($item['status_novo'])) ?>
                        </span>
                        <?php if ($item['descricao']): ?>
                            <span class="timeline__description"><?= e($item['descricao']) ?></span>
                        <?php endif; ?>
                        <?php if ($item['usuario_admin']): ?>
                            <span class="timeline__admin">Por: <?= e($item['usuario_admin']) ?></span>
                        <?php endif; ?>
                        <span class="timeline__date"><?= e(date('d/m/Y H:i', strtotime($item['created_at']))) ?></span>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>

<?php if ($validacao['status'] === Validacao::STATUS_PENDENTE): ?>
    <section class="mm-card">
        <div class="mm-card__header">
            <h2 class="mm-card__title">Ações</h2>
        </div>
        <form method="POST" action="<?= url('/admin/validacoes/iniciar') ?>" class="form">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $validacao['id'] ?>">
            <button type="submit" class="btn btn--block btn--primary">Iniciar Análise</button>
        </form>
    </section>
<?php elseif ($validacao['status'] === Validacao::STATUS_EM_ANALISE): ?>
    <section class="mm-card">
        <div class="mm-card__header">
            <h2 class="mm-card__title">Ações</h2>
        </div>
        <form method="POST" action="<?= url('/admin/validacoes/aprovar') ?>" class="form">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $validacao['id'] ?>">
            <div class="form-group">
                <label for="observacao">Observação (opcional)</label>
                <textarea id="observacao" name="observacao" rows="2"></textarea>
            </div>
            <button type="submit" class="btn btn--block btn--success">Aprovar Validação</button>
        </form>
        <form method="POST" action="<?= url('/admin/validacoes/rejeitar') ?>" class="form">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $validacao['id'] ?>">
            <div class="form-group">
                <label for="motivo_rejeitar">Motivo da Rejeição *</label>
                <textarea id="motivo_rejeitar" name="motivo" rows="2" required></textarea>
            </div>
            <button type="submit" class="btn btn--block btn--danger" onsubmit="return confirm('Tem certeza que deseja rejeitar esta validação?');">Rejeitar Validação</button>
        </form>
        <form method="POST" action="<?= url('/admin/validacoes/cancelar') ?>" class="form">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $validacao['id'] ?>">
            <div class="form-group">
                <label for="motivo_cancelar">Motivo do Cancelamento (opcional)</label>
                <textarea id="motivo_cancelar" name="motivo" rows="2"></textarea>
            </div>
            <button type="submit" class="btn btn--block btn--ghost" onsubmit="return confirm('Tem certeza que deseja cancelar esta validação?');">Cancelar Validação</button>
        </form>
    </section>
<?php elseif ($validacao['status'] === Validacao::STATUS_PENDENTE || $validacao['status'] === Validacao::STATUS_EM_ANALISE): ?>
    <section class="mm-card">
        <div class="mm-card__header">
            <h2 class="mm-card__title">Ações</h2>
        </div>
        <form method="POST" action="<?= url('/admin/validacoes/cancelar') ?>" class="form">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $validacao['id'] ?>">
            <div class="form-group">
                <label for="motivo_cancelar">Motivo do Cancelamento (opcional)</label>
                <textarea id="motivo_cancelar" name="motivo" rows="2"></textarea>
            </div>
            <button type="submit" class="btn btn--block btn--ghost" onsubmit="return confirm('Tem certeza que deseja cancelar esta validação?');">Cancelar Validação</button>
        </form>
    </section>
<?php endif; ?>
