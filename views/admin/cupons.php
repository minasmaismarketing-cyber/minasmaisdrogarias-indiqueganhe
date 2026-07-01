<?php declare(strict_types=1); ?>
<?php
$subtitle = 'Gerencie os cupons do programa Indique e Ganhe.';
$buildPageUrl = static function (int $page) use ($filters): string {
    $params = array_filter($filters, static fn ($value) => $value !== '');
    if ($page > 1) {
        $params['page'] = $page;
    }
    $query = http_build_query($params);

    return url('/admin/cupons' . ($query !== '' ? '?' . $query : ''));
};
?>

<?php admin_filter_panel('admin-cupons-filters', $filters, static function () use ($filters, $campanhas): void { ?>
    <form method="GET" action="<?= url('/admin/cupons') ?>" class="form admin-filter-panel__form">
        <div class="form-row">
            <div class="form-group">
                <label for="codigo">Código</label>
                <input type="text" id="codigo" name="codigo" value="<?= e($filters['codigo']) ?>">
            </div>
            <div class="form-group">
                <label for="indicador">Indicador</label>
                <input type="text" id="indicador" name="indicador" value="<?= e($filters['indicador']) ?>">
            </div>
            <div class="form-group">
                <label for="campanha_id">Campanha</label>
                <select id="campanha_id" name="campanha_id">
                    <option value="">Todas</option>
                    <?php foreach ($campanhas as $campanha): ?>
                        <option
                            value="<?= (int) $campanha['id'] ?>"
                            <?= (string) $filters['campanha_id'] === (string) $campanha['id'] ? 'selected' : '' ?>
                        ><?= e($campanha['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
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
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="data_inicio">Período — início</label>
                <input type="date" id="data_inicio" name="data_inicio" value="<?= e($filters['data_inicio']) ?>">
            </div>
            <div class="form-group">
                <label for="data_fim">Período — fim</label>
                <input type="date" id="data_fim" name="data_fim" value="<?= e($filters['data_fim']) ?>">
            </div>
        </div>
        <div class="admin-filter-panel__actions">
            <a href="<?= url('/admin/cupons') ?>" class="btn btn--ghost">Limpar</a>
            <button type="submit" class="btn btn--primary">Filtrar</button>
        </div>
    </form>
<?php }); ?>

<section class="admin-stats stats-grid">
    <article class="stat-card">
        <span class="stat-card__value"><?= $stats['disponiveis'] ?? 0 ?></span>
        <span class="stat-card__label">Disponíveis</span>
    </article>
    <article class="stat-card">
        <span class="stat-card__value"><?= $stats['reservados'] ?? 0 ?></span>
        <span class="stat-card__label">Reservados</span>
    </article>
    <article class="stat-card">
        <span class="stat-card__value"><?= $stats['utilizados'] ?? 0 ?></span>
        <span class="stat-card__label">Utilizados</span>
    </article>
    <article class="stat-card">
        <span class="stat-card__value"><?= $stats['expirados'] ?? 0 ?></span>
        <span class="stat-card__label">Expirados</span>
    </article>
    <article class="stat-card">
        <span class="stat-card__value"><?= $stats['cancelados'] ?? 0 ?></span>
        <span class="stat-card__label">Cancelados</span>
    </article>
</section>

<?php admin_table([
    'title' => 'Lista de Cupons',
    'meta' => $total . ' registro(s)',
    'emptyMessage' => 'Nenhum cupom encontrado.',
    'columns' => [
        ['label' => 'Código'],
        ['label' => 'Indicador'],
        ['label' => 'Campanha'],
        ['label' => 'Status'],
        ['label' => 'Data de geração'],
        ['label' => 'Data de utilização'],
        ['label' => 'Origem'],
        ['label' => 'Ações', 'class' => 'admin-table__col--actions', 'align' => 'right'],
    ],
    'rows' => $cupons,
], static function (array $cupom): void {
    $status = (string) $cupom['status'];
    ?>
    <tr>
        <td class="admin-table__td admin-table__td--primary"><?= e((string) $cupom['codigo']) ?></td>
        <td class="admin-table__td admin-table__td--wrap"><?= e((string) ($cupom['usuario_nome'] ?? '—')) ?></td>
        <td class="admin-table__td admin-table__td--wrap"><?= e((string) ($cupom['campanha_nome'] ?? '—')) ?></td>
        <td class="admin-table__td">
            <span class="badge <?= e(Cupom::adminStatusBadgeClass($status)) ?>">
                <?= Cupom::statusIcon($status) ?>
                <?= e(Cupom::statusLabel($status)) ?>
            </span>
        </td>
        <td class="admin-table__td"><?= e(date('d/m/Y H:i', strtotime($cupom['created_at']))) ?></td>
        <td class="admin-table__td">
            <?= !empty($cupom['utilizado_em']) ? e(date('d/m/Y H:i', strtotime($cupom['utilizado_em']))) : '—' ?>
        </td>
        <td class="admin-table__td"><?= e(Cupom::origemLabel($cupom['origem'] ?? null)) ?></td>
        <td class="admin-table__td admin-table__col--actions">
            <div class="admin-table__actions">
                <a
                    href="<?= url('/admin/cupons/' . $cupom['id']) ?>"
                    class="btn btn--sm btn--ghost"
                    aria-label="Ver detalhes do cupom <?= e((string) $cupom['codigo']) ?>"
                    title="Ver detalhes"
                >👁</a>
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
        </td>
    </tr>
<?php }); ?>

<?php if ($totalPages > 1): ?>
    <nav class="admin-pagination pagination" aria-label="Paginação de cupons">
        <?php if ($hasPrev): ?>
            <a href="<?= e($buildPageUrl($currentPage - 1)) ?>" class="pagination__link pagination__link--prev">Anterior</a>
        <?php endif; ?>
        <span class="pagination__info">Página <?= $currentPage ?> de <?= $totalPages ?></span>
        <?php if ($hasNext): ?>
            <a href="<?= e($buildPageUrl($currentPage + 1)) ?>" class="pagination__link pagination__link--next">Próxima</a>
        <?php endif; ?>
    </nav>
<?php endif; ?>
