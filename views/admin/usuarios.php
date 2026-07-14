<?php declare(strict_types=1); ?>
<?php
$subtitle = 'Gerencie os usuários do sistema.';
$buildPageUrl = static function (int $page) use ($filters): string {
    $params = array_filter($filters, static fn ($value) => $value !== '' && $value !== Usuario::FILTER_TODOS);
    if ($page > 1) {
        $params['page'] = $page;
    }
    $query = http_build_query($params);

    return url('/admin/usuarios' . ($query !== '' ? '?' . $query : ''));
};
?>

<?php admin_filter_panel('admin-usuarios-filters', $filters, static function () use ($filters): void { ?>
    <form method="GET" action="<?= url('/admin/usuarios') ?>" class="form admin-filter-panel__form">
        <div class="form-row">
            <div class="form-group">
                <label for="nome">Nome</label>
                <input type="text" id="nome" name="nome" value="<?= e($filters['nome']) ?>">
            </div>
            <div class="form-group">
                <label for="cpf">CPF</label>
                <input type="text" id="cpf" name="cpf" value="<?= e($filters['cpf']) ?>">
            </div>
            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" value="<?= e($filters['email']) ?>">
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="<?= Usuario::FILTER_TODOS ?>" <?= $filters['status'] === Usuario::FILTER_TODOS ? 'selected' : '' ?>>Todos</option>
                    <option value="<?= Usuario::FILTER_ATIVOS ?>" <?= $filters['status'] === Usuario::FILTER_ATIVOS ? 'selected' : '' ?>>Ativos</option>
                    <option value="<?= Usuario::FILTER_BLOQUEADOS ?>" <?= $filters['status'] === Usuario::FILTER_BLOQUEADOS ? 'selected' : '' ?>>Bloqueados</option>
                    <option value="<?= Usuario::FILTER_EXCLUIDOS ?>" <?= $filters['status'] === Usuario::FILTER_EXCLUIDOS ? 'selected' : '' ?>>Excluídos</option>
                </select>
            </div>
        </div>
        <div class="admin-filter-panel__actions">
            <a href="<?= url('/admin/usuarios') ?>" class="btn btn--ghost">Limpar</a>
            <button type="submit" class="btn btn--primary">Buscar</button>
        </div>
    </form>
<?php }); ?>

<?php admin_table([
    'title' => 'Lista de Usuários',
    'meta' => $total . ' registro(s)',
    'emptyMessage' => 'Nenhum usuário encontrado.',
    'class' => 'admin-table--expandable',
    'columns' => [
        ['label' => 'Nome'],
        ['label' => 'CPF'],
        ['label' => 'E-mail'],
        ['label' => 'Perfil'],
        ['label' => 'Indicações / Cupons'],
        ['label' => 'Cadastro'],
        ['label' => 'Ações', 'class' => 'admin-table__col--actions', 'align' => 'right'],
    ],
    'rows' => $usuarios,
], static function (array $usuario): void {
    $detailId = 'admin-acc-user-' . (int) $usuario['id'];
    $cpfDisplay = Usuario::formatCpfDisplay($usuario);
    $email = (string) $usuario['email'];
    $summarySecondary = $cpfDisplay !== '—' && $cpfDisplay !== '' ? $cpfDisplay : $email;
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
                <span class="admin-table__summary-title"><?= e($usuario['nome']) ?></span>
                <span class="admin-table__chevron" aria-hidden="true"></span>
            </div>
            <span class="badge <?= Usuario::accountStatusBadgeClass($usuario) ?>">
                <?= Usuario::accountStatusIcon($usuario) ?>
                <?= e(Usuario::accountStatusLabel($usuario)) ?>
            </span>
            <span class="admin-table__summary-sub admin-table__mobile-only"><?= e($summarySecondary) ?></span>
        </td>
        <td class="admin-table__td admin-table__td--mobile-hide"><?= e($cpfDisplay) ?></td>
        <td class="admin-table__td admin-table__td--wrap admin-table__td--mobile-hide"><?= e($email) ?></td>
        <td class="admin-table__td admin-table__td--mobile-hide"><?= e(Usuario::roleLabel((string) ($usuario['role'] ?? Usuario::ROLE_CLIENTE))) ?></td>
        <td class="admin-table__td admin-table__td--mobile-hide">
            <?= (int) ($usuario['total_indicacoes'] ?? 0) ?> / <?= (int) ($usuario['total_cupons'] ?? 0) ?>
        </td>
        <td class="admin-table__td admin-table__td--mobile-hide"><?= e(date('d/m/Y H:i', strtotime($usuario['created_at']))) ?></td>
        <td class="admin-table__td admin-table__col--actions admin-table__td--mobile-hide">
            <div class="admin-table__actions">
                <a
                    href="<?= url('/admin/usuarios/' . $usuario['id']) ?>"
                    class="btn btn--sm btn--ghost"
                    aria-label="Ver detalhes de <?= e($usuario['nome']) ?>"
                    title="Ver detalhes"
                >👁</a>
            </div>
        </td>
    </tr>
    <tr class="admin-table__row--details" id="<?= e($detailId) ?>" hidden>
        <td colspan="7">
            <dl class="admin-table__details">
                <div>
                    <dt>CPF</dt>
                    <dd><?= e($cpfDisplay) ?></dd>
                </div>
                <div>
                    <dt>E-mail</dt>
                    <dd><?= e($email) ?></dd>
                </div>
                <div>
                    <dt>Perfil</dt>
                    <dd><?= e(Usuario::roleLabel((string) ($usuario['role'] ?? Usuario::ROLE_CLIENTE))) ?></dd>
                </div>
                <div>
                    <dt>Indicações / Cupons</dt>
                    <dd><?= (int) ($usuario['total_indicacoes'] ?? 0) ?> / <?= (int) ($usuario['total_cupons'] ?? 0) ?></dd>
                </div>
                <div>
                    <dt>Cadastro</dt>
                    <dd><?= e(date('d/m/Y H:i', strtotime($usuario['created_at']))) ?></dd>
                </div>
                <div class="admin-table__details-actions">
                    <dt>Ações</dt>
                    <dd>
                        <div class="admin-table__actions">
                            <a
                                href="<?= url('/admin/usuarios/' . $usuario['id']) ?>"
                                class="btn btn--sm btn--ghost"
                                aria-label="Ver detalhes de <?= e($usuario['nome']) ?>"
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
    <nav class="admin-pagination pagination" aria-label="Paginação de usuários">
        <?php if ($hasPrev): ?>
            <a href="<?= e($buildPageUrl($currentPage - 1)) ?>" class="pagination__link pagination__link--prev">Anterior</a>
        <?php endif; ?>
        <span class="pagination__info">Página <?= $currentPage ?> de <?= $totalPages ?></span>
        <?php if ($hasNext): ?>
            <a href="<?= e($buildPageUrl($currentPage + 1)) ?>" class="pagination__link pagination__link--next">Próxima</a>
        <?php endif; ?>
    </nav>
<?php endif; ?>
