<?php declare(strict_types=1); ?>
<?php
$subtitle = 'Gerencie as indicações do sistema.';
$buildPageUrl = static function (int $page) use ($filters): string {
    $params = array_filter($filters, static fn ($value) => $value !== '' && $value !== Indicacao::FILTER_TODOS);
    if ($page > 1) {
        $params['page'] = $page;
    }
    $query = http_build_query($params);

    return url('/admin/indicacoes' . ($query !== '' ? '?' . $query : ''));
};
?>

<?php admin_filter_panel('admin-indicacoes-filters', $filters, static function () use ($filters): void { ?>
    <form method="GET" action="<?= url('/admin/indicacoes') ?>" class="form admin-filter-panel__form">
        <div class="form-row">
            <div class="form-group">
                <label for="indicador">Nome do indicador</label>
                <input type="text" id="indicador" name="indicador" value="<?= e($filters['indicador']) ?>">
            </div>
            <div class="form-group">
                <label for="indicado">Nome do indicado</label>
                <input type="text" id="indicado" name="indicado" value="<?= e($filters['indicado']) ?>">
            </div>
            <div class="form-group">
                <label for="cpf">CPF do indicado</label>
                <input type="text" id="cpf" name="cpf" value="<?= e($filters['cpf']) ?>">
            </div>
            <div class="form-group">
                <label for="whatsapp">WhatsApp do indicado</label>
                <input type="text" id="whatsapp" name="whatsapp" value="<?= e($filters['whatsapp']) ?>">
            </div>
            <div class="form-group">
                <label for="codigo">Código da indicação</label>
                <input type="text" id="codigo" name="codigo" value="<?= e($filters['codigo']) ?>">
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="<?= Indicacao::FILTER_TODOS ?>" <?= $filters['status'] === Indicacao::FILTER_TODOS ? 'selected' : '' ?>>Todos</option>
                    <option value="<?= ValidacaoIndicacao::STATUS_AGUARDANDO_CADASTRO ?>" <?= $filters['status'] === ValidacaoIndicacao::STATUS_AGUARDANDO_CADASTRO ? 'selected' : '' ?>>Aguardando Cadastro</option>
                    <option value="<?= ValidacaoIndicacao::STATUS_AGUARDANDO_VALIDACAO ?>" <?= $filters['status'] === ValidacaoIndicacao::STATUS_AGUARDANDO_VALIDACAO ? 'selected' : '' ?>>Aguardando Validação</option>
                    <option value="<?= ValidacaoIndicacao::STATUS_EM_ANALISE ?>" <?= $filters['status'] === ValidacaoIndicacao::STATUS_EM_ANALISE ? 'selected' : '' ?>>Em Análise</option>
                    <option value="<?= ValidacaoIndicacao::STATUS_APROVADO ?>" <?= $filters['status'] === ValidacaoIndicacao::STATUS_APROVADO ? 'selected' : '' ?>>Aprovada</option>
                    <option value="<?= ValidacaoIndicacao::STATUS_REPROVADO ?>" <?= $filters['status'] === ValidacaoIndicacao::STATUS_REPROVADO ? 'selected' : '' ?>>Reprovada</option>
                    <option value="<?= ValidacaoIndicacao::STATUS_CANCELADO ?>" <?= $filters['status'] === ValidacaoIndicacao::STATUS_CANCELADO ? 'selected' : '' ?>>Cancelada</option>
                </select>
            </div>
        </div>
        <div class="admin-filter-panel__actions">
            <a href="<?= url('/admin/indicacoes') ?>" class="btn btn--ghost">Limpar</a>
            <button type="submit" class="btn btn--primary">Buscar</button>
        </div>
    </form>
<?php }); ?>

<?php admin_table([
    'title' => 'Lista de Indicações',
    'meta' => $total . ' registro(s)',
    'emptyMessage' => 'Nenhuma indicação encontrada.',
    'class' => 'admin-table--expandable',
    'columns' => [
        ['label' => 'Indicador'],
        ['label' => 'Indicado'],
        ['label' => 'WhatsApp'],
        ['label' => 'Código'],
        ['label' => 'Status'],
        ['label' => 'Cupom'],
        ['label' => 'Data'],
        ['label' => 'Ações', 'class' => 'admin-table__col--actions', 'align' => 'right'],
    ],
    'rows' => $indicacoes,
], static function (array $indicacao): void {
    $adminStatus = (string) ($indicacao['admin_status'] ?? Indicacao::resolveAdminStatus($indicacao));
    $detailId = 'admin-acc-ind-' . (int) $indicacao['id'];
    $indicadorNome = (string) ($indicacao['indicador_nome'] ?? '—');
    $indicadoNome = (string) ($indicacao['indicado_nome'] ?? 'Aguardando cadastro');
    $whatsapp = !empty($indicacao['indicado_whatsapp']) ? format_phone((string) $indicacao['indicado_whatsapp']) : '—';
    $codigo = (string) ($indicacao['codigo_referencia'] ?? $indicacao['codigo_indicador'] ?? '—');
    $cupom = !empty($indicacao['cupom_codigo']) ? (string) $indicacao['cupom_codigo'] : '—';
    $data = date('d/m/Y H:i', strtotime($indicacao['created_at']));
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
                <span class="admin-table__summary-title"><?= e($indicadorNome) ?></span>
                <span class="admin-table__chevron" aria-hidden="true"></span>
            </div>
            <span class="admin-table__summary-sub admin-table__mobile-only"><?= e($indicadoNome) ?></span>
        </td>
        <td class="admin-table__td admin-table__td--wrap admin-table__td--mobile-hide">
            <?= e($indicadoNome) ?>
        </td>
        <td class="admin-table__td admin-table__td--mobile-hide"><?= e($whatsapp) ?></td>
        <td class="admin-table__td admin-table__td--mobile-hide"><?= e($codigo) ?></td>
        <td class="admin-table__td admin-table__td--mobile-show">
            <span class="badge badge--status badge--<?= strtolower($adminStatus) ?>">
                <?= ValidacaoIndicacao::statusIcon($adminStatus) ?>
                <?= e(Indicacao::adminStatusLabel($adminStatus)) ?>
            </span>
        </td>
        <td class="admin-table__td admin-table__td--mobile-hide"><?= e($cupom) ?></td>
        <td class="admin-table__td admin-table__td--mobile-show"><?= e($data) ?></td>
        <td class="admin-table__td admin-table__col--actions admin-table__td--mobile-hide">
            <div class="admin-table__actions">
                <a
                    href="<?= url('/admin/indicacoes/' . $indicacao['id']) ?>"
                    class="btn btn--sm btn--ghost"
                    aria-label="Ver detalhes da indicação #<?= (int) $indicacao['id'] ?>"
                    title="Ver detalhes"
                >👁</a>
            </div>
        </td>
    </tr>
    <tr class="admin-table__row--details" id="<?= e($detailId) ?>" hidden>
        <td colspan="8">
            <dl class="admin-table__details">
                <div>
                    <dt>Indicado</dt>
                    <dd><?= e($indicadoNome) ?></dd>
                </div>
                <div>
                    <dt>WhatsApp</dt>
                    <dd><?= e($whatsapp) ?></dd>
                </div>
                <div>
                    <dt>Código</dt>
                    <dd><?= e($codigo) ?></dd>
                </div>
                <div>
                    <dt>Cupom</dt>
                    <dd><?= e($cupom) ?></dd>
                </div>
                <div class="admin-table__details-actions">
                    <dt>Ações</dt>
                    <dd>
                        <div class="admin-table__actions">
                            <a
                                href="<?= url('/admin/indicacoes/' . $indicacao['id']) ?>"
                                class="btn btn--sm btn--ghost"
                                aria-label="Ver detalhes da indicação #<?= (int) $indicacao['id'] ?>"
                                title="Ver detalhes"
                            >👁</a>
                        </div>
                    </dd>
                </div>
            </dl>
        </td>
    </tr>
<?php }); ?>

<?php if ($totalPages > 1): ?>
    <nav class="admin-pagination pagination" aria-label="Paginação de indicações">
        <?php if ($hasPrev): ?>
            <a href="<?= e($buildPageUrl($currentPage - 1)) ?>" class="pagination__link pagination__link--prev">Anterior</a>
        <?php endif; ?>
        <span class="pagination__info">Página <?= $currentPage ?> de <?= $totalPages ?></span>
        <?php if ($hasNext): ?>
            <a href="<?= e($buildPageUrl($currentPage + 1)) ?>" class="pagination__link pagination__link--next">Próxima</a>
        <?php endif; ?>
    </nav>
<?php endif; ?>
