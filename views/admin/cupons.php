<?php declare(strict_types=1); ?>
<?php $subtitle = 'Gerencie os cupons do programa Indique e Ganhe.'; ?>

<?php admin_filter_panel('admin-cupons-filters', $filters, static function () use ($filters): void { ?>
    <form method="GET" action="<?= url('/admin/cupons') ?>" class="form admin-filter-panel__form">
        <div class="form-row">
            <div class="form-group">
                <label for="codigo">Código</label>
                <input type="text" id="codigo" name="codigo" value="<?= e($filters['codigo']) ?>">
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">Todos</option>
                    <option value="<?= Cupom::STATUS_DISPONIVEL ?>" <?= $filters['status'] === Cupom::STATUS_DISPONIVEL ? 'selected' : '' ?>>Disponível</option>
                    <option value="<?= Cupom::STATUS_RESERVADO ?>" <?= $filters['status'] === Cupom::STATUS_RESERVADO ? 'selected' : '' ?>>Reservado</option>
                    <option value="<?= Cupom::STATUS_UTILIZADO ?>" <?= $filters['status'] === Cupom::STATUS_UTILIZADO ? 'selected' : '' ?>>Utilizado</option>
                    <option value="<?= Cupom::STATUS_EXPIRADO ?>" <?= $filters['status'] === Cupom::STATUS_EXPIRADO ? 'selected' : '' ?>>Expirado</option>
                    <option value="<?= Cupom::STATUS_CANCELADO ?>" <?= $filters['status'] === Cupom::STATUS_CANCELADO ? 'selected' : '' ?>>Cancelado</option>
                </select>
            </div>
            <div class="form-group">
                <label for="data_inicio">Data Início</label>
                <input type="date" id="data_inicio" name="data_inicio" value="<?= e($filters['data_inicio']) ?>">
            </div>
            <div class="form-group">
                <label for="data_fim">Data Fim</label>
                <input type="date" id="data_fim" name="data_fim" value="<?= e($filters['data_fim']) ?>">
            </div>
        </div>
        <div class="admin-filter-panel__actions">
            <a href="<?= url('/admin/cupons') ?>" class="btn btn--ghost">Limpar</a>
            <button type="submit" class="btn btn--primary">Filtrar</button>
        </div>
    </form>
<?php }); ?>

<section class="stats-grid">
    <div class="stat-card">
        <div class="stat-card__value"><?= $stats['total'] ?? 0 ?></div>
        <div class="stat-card__label">Total</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__value"><?= $stats['disponiveis'] ?? 0 ?></div>
        <div class="stat-card__label">Disponíveis 🟢</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__value"><?= $stats['reservados'] ?? 0 ?></div>
        <div class="stat-card__label">Reservados 🟡</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__value"><?= $stats['utilizados'] ?? 0 ?></div>
        <div class="stat-card__label">Utilizados ✅</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__value"><?= $stats['expirados'] ?? 0 ?></div>
        <div class="stat-card__label">Expirados ⚫</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__value"><?= $stats['cancelados'] ?? 0 ?></div>
        <div class="stat-card__label">Cancelados 🔴</div>
    </div>
</section>

<section class="mm-card">
    <div class="mm-card__header">
        <h2 class="mm-card__title">Lista de Cupons</h2>
    </div>

    <?php if ($cupons === []): ?>
        <p class="mm-card__placeholder">Nenhum cupom encontrado.</p>
    <?php else: ?>
        <ul class="admin-list">
            <?php foreach ($cupons as $cupom): ?>
                <li class="admin-list__item">
                    <div class="admin-list__info">
                        <span class="admin-list__icon"><?= Cupom::statusIcon($cupom['status']) ?></span>
                        <div>
                            <strong><?= e($cupom['codigo']) ?></strong>
                            <span class="admin-list__meta">
                                <?= e($cupom['usuario_nome'] ?? 'N/A') ?>
                            </span>
                            <span class="admin-list__meta">
                                <?= e($cupom['campanha_nome'] ?? 'N/A') ?>
                            </span>
                            <span class="admin-list__meta">
                                <?= e($cupom['valor']) ?> <?= e(Cupom::tipoLabel($cupom['tipo'])) ?>
                            </span>
                            <?php if ($cupom['validade']): ?>
                                <span class="admin-list__meta">
                                    Validade: <?= e(date('d/m/Y', strtotime($cupom['validade']))) ?>
                                </span>
                            <?php endif; ?>
                            <span class="admin-list__meta">
                                <?= e(date('d/m/Y H:i', strtotime($cupom['created_at']))) ?>
                            </span>
                        </div>
                    </div>
                    <div class="admin-list__actions">
                        <span class="badge badge--<?= strtolower($cupom['status']) ?>">
                            <?= e(Cupom::statusLabel($cupom['status'])) ?>
                        </span>
                        <a href="<?= url('/admin/cupons/' . $cupom['id']) ?>" class="btn btn--sm btn--ghost">Detalhes</a>
                        <?php if ($cupom['status'] === Cupom::STATUS_DISPONIVEL || $cupom['status'] === Cupom::STATUS_RESERVADO): ?>
                            <form method="POST" action="<?= url('/admin/cupons/cancelar') ?>" class="inline-form" onsubmit="return confirm('Tem certeza que deseja cancelar este cupom?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= $cupom['id'] ?>">
                                <button type="submit" class="btn btn--sm btn--danger">Cancelar</button>
                            </form>
                        <?php endif; ?>
                        <?php if ($cupom['status'] === Cupom::STATUS_DISPONIVEL): ?>
                            <form method="POST" action="<?= url('/admin/cupons/expirar') ?>" class="inline-form" onsubmit="return confirm('Tem certeza que deseja expirar este cupom?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= $cupom['id'] ?>">
                                <button type="submit" class="btn btn--sm btn--ghost">Expirar</button>
                            </form>
                        <?php endif; ?>
                        <?php if ($cupom['status'] === Cupom::STATUS_CANCELADO || $cupom['status'] === Cupom::STATUS_EXPIRADO): ?>
                            <form method="POST" action="<?= url('/admin/cupons/reativar') ?>" class="inline-form">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= $cupom['id'] ?>">
                                <button type="submit" class="btn btn--sm btn--success">Reativar</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
