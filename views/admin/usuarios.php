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

<section class="mm-card">
    <div class="mm-card__header">
        <h2 class="mm-card__title">Filtros</h2>
    </div>
    <form method="GET" action="<?= url('/admin/usuarios') ?>" class="form">
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
        <button type="submit" class="btn btn--primary">Buscar</button>
        <a href="<?= url('/admin/usuarios') ?>" class="btn btn--ghost">Limpar</a>
    </form>
</section>

<section class="mm-card">
    <div class="mm-card__header">
        <h2 class="mm-card__title">Lista de Usuários</h2>
        <span class="mm-card__meta"><?= $total ?> registro(s)</span>
    </div>

    <?php if ($usuarios === []): ?>
        <p class="mm-card__placeholder">Nenhum usuário encontrado.</p>
    <?php else: ?>
        <ul class="admin-list">
            <?php foreach ($usuarios as $usuario): ?>
                <li class="admin-list__item">
                    <div class="admin-list__info">
                        <strong><?= e($usuario['nome']) ?></strong>
                        <span class="admin-list__meta">CPF: <?= e(Usuario::formatCpfDisplay($usuario)) ?></span>
                        <span class="admin-list__meta"><?= e($usuario['email']) ?></span>
                        <span class="admin-list__meta">
                            Role: <?= e(Usuario::roleLabel((string) ($usuario['role'] ?? Usuario::ROLE_CLIENTE))) ?>
                            · Status: <?= e(Usuario::accountStatusLabel($usuario)) ?>
                        </span>
                        <span class="admin-list__meta">
                            Indicações: <?= (int) ($usuario['total_indicacoes'] ?? 0) ?>
                            · Cupons: <?= (int) ($usuario['total_cupons'] ?? 0) ?>
                        </span>
                        <span class="admin-list__meta">
                            Cadastro: <?= e(date('d/m/Y H:i', strtotime($usuario['created_at']))) ?>
                        </span>
                    </div>
                    <div class="admin-list__actions">
                        <a
                            href="<?= url('/admin/usuarios/' . $usuario['id']) ?>"
                            class="btn btn--sm btn--ghost"
                            aria-label="Ver detalhes de <?= e($usuario['nome']) ?>"
                            title="Ver detalhes"
                        >👁</a>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>

<?php if ($totalPages > 1): ?>
    <nav class="pagination" aria-label="Paginação de usuários">
        <?php if ($hasPrev): ?>
            <a href="<?= e($buildPageUrl($currentPage - 1)) ?>" class="pagination__link pagination__link--prev">Anterior</a>
        <?php endif; ?>
        <span class="pagination__info">Página <?= $currentPage ?> de <?= $totalPages ?></span>
        <?php if ($hasNext): ?>
            <a href="<?= e($buildPageUrl($currentPage + 1)) ?>" class="pagination__link pagination__link--next">Próxima</a>
        <?php endif; ?>
    </nav>
<?php endif; ?>
