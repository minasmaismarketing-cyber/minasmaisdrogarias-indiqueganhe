<?php declare(strict_types=1); ?>
<?php require BASE_PATH . '/views/partials/alerts.php'; ?>

<section class="page-hero">
    <h1 class="page-hero__title">Detalhes do Cupom</h1>
    <a href="<?= url('/admin/cupons') ?>" class="btn btn--sm btn--ghost">Voltar</a>
</section>

<section class="mm-card">
    <div class="mm-card__header">
        <h2 class="mm-card__title">Informações do Cupom</h2>
        <span class="badge badge--<?= strtolower($cupom['status']) ?>">
            <?= Cupom::statusIcon($cupom['status']) ?>
            <?= e(Cupom::statusLabel($cupom['status'])) ?>
        </span>
    </div>

    <div class="info-grid">
        <div class="info-grid__item">
            <span class="info-grid__label">Código</span>
            <span class="info-grid__value info-grid__value--highlight"><?= e($cupom['codigo']) ?></span>
        </div>
        <div class="info-grid__item">
            <span class="info-grid__label">Usuário</span>
            <span class="info-grid__value"><?= e($cupom['usuario_nome'] ?? 'N/A') ?></span>
        </div>
        <div class="info-grid__item">
            <span class="info-grid__label">Campanha</span>
            <span class="info-grid__value"><?= e($cupom['campanha_nome'] ?? 'N/A') ?></span>
        </div>
        <div class="info-grid__item">
            <span class="info-grid__label">Valor</span>
            <span class="info-grid__value"><?= e($cupom['valor']) ?> <?= e(Cupom::tipoLabel($cupom['tipo'])) ?></span>
        </div>
        <div class="info-grid__item">
            <span class="info-grid__label">Validade</span>
            <span class="info-grid__value"><?= $cupom['validade'] ? e(date('d/m/Y', strtotime($cupom['validade']))) : 'Indefinida' ?></span>
        </div>
        <div class="info-grid__item">
            <span class="info-grid__label">Origem</span>
            <span class="info-grid__value"><?= e($cupom['origem'] ?? 'Manual') ?></span>
        </div>
        <div class="info-grid__item">
            <span class="info-grid__label">Criado em</span>
            <span class="info-grid__value"><?= e(date('d/m/Y H:i:s', strtotime($cupom['created_at']))) ?></span>
        </div>
        <?php if ($cupom['utilizado_em']): ?>
            <div class="info-grid__item">
                <span class="info-grid__label">Utilizado em</span>
                <span class="info-grid__value"><?= e(date('d/m/Y H:i:s', strtotime($cupom['utilizado_em']))) ?></span>
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
                        <?= Cupom::statusIcon($item['status_novo']) ?>
                    </span>
                    <div class="timeline__content">
                        <span class="timeline__label">
                            <?= e(Cupom::statusLabel($item['status_anterior'])) ?> → 
                            <?= e(Cupom::statusLabel($item['status_novo'])) ?>
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

<?php if ($cupom['status'] === Cupom::STATUS_DISPONIVEL || $cupom['status'] === Cupom::STATUS_RESERVADO): ?>
    <section class="mm-card">
        <div class="mm-card__header">
            <h2 class="mm-card__title">Ações</h2>
        </div>
        <form method="POST" action="<?= url('/admin/cupons/cancelar') ?>" class="form">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $cupom['id'] ?>">
            <div class="form-group">
                <label for="motivo">Motivo do Cancelamento (opcional)</label>
                <textarea id="motivo" name="motivo" rows="2"></textarea>
            </div>
            <button type="submit" class="btn btn--block btn--danger" onsubmit="return confirm('Tem certeza que deseja cancelar este cupom?');">Cancelar Cupom</button>
        </form>
        <?php if ($cupom['status'] === Cupom::STATUS_DISPONIVEL): ?>
            <form method="POST" action="<?= url('/admin/cupons/expirar') ?>" class="form">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= $cupom['id'] ?>">
                <button type="submit" class="btn btn--block btn--ghost" onsubmit="return confirm('Tem certeza que deseja expirar este cupom?');">Expirar Cupom</button>
            </form>
        <?php endif; ?>
    </section>
<?php elseif ($cupom['status'] === Cupom::STATUS_CANCELADO || $cupom['status'] === Cupom::STATUS_EXPIRADO): ?>
    <section class="mm-card">
        <div class="mm-card__header">
            <h2 class="mm-card__title">Ações</h2>
        </div>
        <form method="POST" action="<?= url('/admin/cupons/reativar') ?>" class="form">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $cupom['id'] ?>">
            <button type="submit" class="btn btn--block btn--success">Reativar Cupom</button>
        </form>
    </section>
<?php endif; ?>
