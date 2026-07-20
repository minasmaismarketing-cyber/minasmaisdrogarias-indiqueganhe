<?php declare(strict_types=1); ?>
<?php
$subtitle = 'Gerencie as campanhas de indicação.';
$buildPageUrl = static function (int $page) use ($filters): string {
    $params = array_filter($filters, static fn ($value) => $value !== '' && $value !== Campanha::FILTER_TODOS);
    if ($page > 1) {
        $params['page'] = $page;
    }
    $query = http_build_query($params);

    return url('/admin/campanhas' . ($query !== '' ? '?' . $query : ''));
};
?>

<?php admin_filter_panel('admin-campanhas-filters', $filters, static function () use ($filters): void { ?>
    <form method="GET" action="<?= url('/admin/campanhas') ?>" class="form admin-filter-panel__form">
        <div class="form-row">
            <div class="form-group">
                <label for="nome">Nome</label>
                <input type="text" id="nome" name="nome" value="<?= e($filters['nome']) ?>">
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="<?= Campanha::FILTER_TODOS ?>" <?= $filters['status'] === Campanha::FILTER_TODOS ? 'selected' : '' ?>>Todos</option>
                    <option value="<?= Campanha::STATUS_ATIVA ?>" <?= $filters['status'] === Campanha::STATUS_ATIVA ? 'selected' : '' ?>>Ativa</option>
                    <option value="<?= Campanha::STATUS_AGENDADA ?>" <?= $filters['status'] === Campanha::STATUS_AGENDADA ? 'selected' : '' ?>>Agendada</option>
                    <option value="<?= Campanha::STATUS_FINALIZADA ?>" <?= $filters['status'] === Campanha::STATUS_FINALIZADA ? 'selected' : '' ?>>Encerrada</option>
                    <option value="<?= Campanha::STATUS_INATIVA ?>" <?= $filters['status'] === Campanha::STATUS_INATIVA ? 'selected' : '' ?>>Inativa</option>
                </select>
            </div>
            <div class="form-group">
                <label for="vigencia">Vigência</label>
                <select id="vigencia" name="vigencia">
                    <option value="<?= Campanha::FILTER_TODOS ?>" <?= $filters['vigencia'] === Campanha::FILTER_TODOS ? 'selected' : '' ?>>Todas</option>
                    <option value="<?= Campanha::FILTER_VIGENCIA_VIGENTE ?>" <?= $filters['vigencia'] === Campanha::FILTER_VIGENCIA_VIGENTE ? 'selected' : '' ?>>Em vigor</option>
                    <option value="<?= Campanha::FILTER_VIGENCIA_FUTURA ?>" <?= $filters['vigencia'] === Campanha::FILTER_VIGENCIA_FUTURA ? 'selected' : '' ?>>Futura</option>
                    <option value="<?= Campanha::FILTER_VIGENCIA_ENCERRADA ?>" <?= $filters['vigencia'] === Campanha::FILTER_VIGENCIA_ENCERRADA ? 'selected' : '' ?>>Encerrada</option>
                </select>
            </div>
        </div>
        <div class="admin-filter-panel__actions">
            <a href="<?= url('/admin/campanhas') ?>" class="btn btn--ghost">Limpar</a>
            <button type="submit" class="btn btn--primary">Filtrar</button>
        </div>
    </form>
<?php }); ?>

<?php admin_table([
    'title' => 'Lista de Campanhas',
    'meta' => $total . ' registro(s)',
    'headerActions' => '<a href="' . e(url('/admin/campanhas/criar')) . '" class="btn btn--sm btn--primary">Criar</a>',
    'emptyMessage' => 'Nenhuma campanha encontrada.',
    'class' => 'admin-table--expandable',
    'columns' => [
        ['label' => 'Nome'],
        ['label' => 'Status'],
        ['label' => 'Vigência'],
        ['label' => 'Indicados'],
        ['label' => 'Cupons Gerados'],
        ['label' => 'Cupons Utilizados'],
        ['label' => 'Data de criação'],
        ['label' => 'Ações', 'class' => 'admin-table__col--actions', 'align' => 'right'],
    ],
    'rows' => $campanhas,
], static function (array $campanha): void {
    $status = (string) $campanha['status'];
    $detailId = 'admin-acc-camp-' . (int) $campanha['id'];
    $nome = (string) $campanha['nome'];
    $vigencia = Campanha::formatVigencia($campanha);
    $criadoEm = date('d/m/Y H:i', strtotime((string) $campanha['created_at']));

    ob_start();
    ?>
            <div class="admin-table__actions">
                <a
                    href="<?= url('/admin/campanhas/' . $campanha['id']) ?>"
                    class="btn btn--sm btn--ghost"
                    aria-label="Ver detalhes de <?= e($nome) ?>"
                    title="Ver detalhes"
                >Visualizar</a>
                <a href="<?= url('/admin/campanhas/editar/' . $campanha['id']) ?>" class="btn btn--sm btn--ghost">Editar</a>
                <form method="POST" action="<?= url('/admin/campanhas/duplicar/' . $campanha['id']) ?>" class="inline-form">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn--sm btn--ghost">Duplicar</button>
                </form>
                <?php if ($campanha['status'] === Campanha::STATUS_ATIVA): ?>
                    <form method="POST" action="<?= url('/admin/campanhas/desativar/' . $campanha['id']) ?>" class="inline-form">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn--sm btn--ghost">Desativar</button>
                    </form>
                <?php else: ?>
                    <form method="POST" action="<?= url('/admin/campanhas/ativar/' . $campanha['id']) ?>" class="inline-form">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn--sm btn--primary">Ativar</button>
                    </form>
                <?php endif; ?>
                <form method="POST" action="<?= url('/admin/campanhas/excluir/' . $campanha['id']) ?>" class="inline-form" onsubmit="return confirm('Tem certeza que deseja excluir esta campanha?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn--sm btn--danger">Excluir</button>
                </form>
            </div>
    <?php
    $actionsHtml = ob_get_clean();
    ?>
    <tr
        class="admin-table__row--summary"
        data-admin-accordion-trigger
        tabindex="0"
        role="button"
        aria-expanded="false"
        aria-controls="<?= e($detailId) ?>"
    >
        <td class="admin-table__td admin-table__td--wrap admin-table__td--primary admin-table__td--mobile-show">
            <div class="admin-table__summary-main">
                <span class="admin-table__summary-title"><?= e($nome) ?></span>
                <span class="admin-table__chevron" aria-hidden="true"></span>
            </div>
            <span class="admin-table__summary-sub admin-table__mobile-only"><?= e($vigencia) ?></span>
        </td>
        <td class="admin-table__td admin-table__td--mobile-show">
            <span class="badge <?= e(Campanha::adminStatusBadgeClass($status)) ?>">
                <?= Campanha::adminStatusIcon($status) ?>
                <?= e(Campanha::adminStatusLabel($status)) ?>
            </span>
        </td>
        <td class="admin-table__td admin-table__td--mobile-hide"><?= e($vigencia) ?></td>
        <td class="admin-table__td admin-table__td--mobile-hide"><?= (int) ($campanha['total_indicados'] ?? 0) ?></td>
        <td class="admin-table__td admin-table__td--mobile-hide"><?= (int) ($campanha['cupons_gerados'] ?? 0) ?></td>
        <td class="admin-table__td admin-table__td--mobile-hide"><?= (int) ($campanha['cupons_utilizados'] ?? 0) ?></td>
        <td class="admin-table__td admin-table__td--mobile-hide"><?= e($criadoEm) ?></td>
        <td class="admin-table__td admin-table__col--actions admin-table__td--mobile-hide">
            <?= $actionsHtml ?>
        </td>
    </tr>
    <tr class="admin-table__row--details" id="<?= e($detailId) ?>" hidden>
        <td colspan="8">
            <dl class="admin-table__details">
                <div>
                    <dt>Vigência</dt>
                    <dd><?= e($vigencia) ?></dd>
                </div>
                <div>
                    <dt>Indicados</dt>
                    <dd><?= (int) ($campanha['total_indicados'] ?? 0) ?></dd>
                </div>
                <div>
                    <dt>Cupons gerados</dt>
                    <dd><?= (int) ($campanha['cupons_gerados'] ?? 0) ?></dd>
                </div>
                <div>
                    <dt>Cupons utilizados</dt>
                    <dd><?= (int) ($campanha['cupons_utilizados'] ?? 0) ?></dd>
                </div>
                <div>
                    <dt>Data de criação</dt>
                    <dd><?= e($criadoEm) ?></dd>
                </div>
                <div class="admin-table__details-actions">
                    <dt>Ações</dt>
                    <dd><?= $actionsHtml ?></dd>
                </div>
            </dl>
        </td>
    </tr>
<?php }); ?>

<?php if ($totalPages > 1): ?>
    <nav class="admin-pagination pagination" aria-label="Paginação de campanhas">
        <?php if ($hasPrev): ?>
            <a href="<?= e($buildPageUrl($currentPage - 1)) ?>" class="pagination__link pagination__link--prev">Anterior</a>
        <?php endif; ?>
        <span class="pagination__info">Página <?= $currentPage ?> de <?= $totalPages ?></span>
        <?php if ($hasNext): ?>
            <a href="<?= e($buildPageUrl($currentPage + 1)) ?>" class="pagination__link pagination__link--next">Próxima</a>
        <?php endif; ?>
    </nav>
<?php endif; ?>
