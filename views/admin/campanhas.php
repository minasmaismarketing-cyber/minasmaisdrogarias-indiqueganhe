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
    ?>
    <tr>
        <td class="admin-table__td admin-table__td--wrap admin-table__td--primary"><?= e((string) $campanha['nome']) ?></td>
        <td class="admin-table__td">
            <span class="badge <?= e(Campanha::adminStatusBadgeClass($status)) ?>">
                <?= Campanha::adminStatusIcon($status) ?>
                <?= e(Campanha::adminStatusLabel($status)) ?>
            </span>
        </td>
        <td class="admin-table__td"><?= e(Campanha::formatVigencia($campanha)) ?></td>
        <td class="admin-table__td"><?= (int) ($campanha['total_indicados'] ?? 0) ?></td>
        <td class="admin-table__td"><?= (int) ($campanha['cupons_gerados'] ?? 0) ?></td>
        <td class="admin-table__td"><?= (int) ($campanha['cupons_utilizados'] ?? 0) ?></td>
        <td class="admin-table__td"><?= e(date('d/m/Y H:i', strtotime($campanha['created_at']))) ?></td>
        <td class="admin-table__td admin-table__col--actions">
            <div class="admin-table__actions">
                <a
                    href="<?= url('/admin/campanhas/' . $campanha['id']) ?>"
                    class="btn btn--sm btn--ghost"
                    aria-label="Ver detalhes de <?= e((string) $campanha['nome']) ?>"
                    title="Ver detalhes"
                >👁</a>
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
