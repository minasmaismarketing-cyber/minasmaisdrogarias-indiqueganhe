<?php declare(strict_types=1); ?>
<?php $subtitle = 'Central operacional de validação das indicações.'; ?>

<?php admin_filter_panel('admin-validacoes-filters', $filters, static function () use ($filters): void { ?>
    <form method="GET" action="<?= url('/admin/validacoes') ?>" class="form admin-filter-panel__form">
        <div class="form-row">
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">Todos</option>
                    <option value="<?= ValidacaoIndicacao::STATUS_PENDENTE ?>" <?= $filters['status'] === ValidacaoIndicacao::STATUS_PENDENTE ? 'selected' : '' ?>>Pendente</option>
                    <option value="<?= ValidacaoIndicacao::STATUS_EM_ANALISE ?>" <?= $filters['status'] === ValidacaoIndicacao::STATUS_EM_ANALISE ? 'selected' : '' ?>>Em Análise</option>
                    <option value="<?= ValidacaoIndicacao::STATUS_APROVADO ?>" <?= $filters['status'] === ValidacaoIndicacao::STATUS_APROVADO ? 'selected' : '' ?>>Validado</option>
                    <option value="<?= ValidacaoIndicacao::STATUS_REPROVADO ?>" <?= $filters['status'] === ValidacaoIndicacao::STATUS_REPROVADO ? 'selected' : '' ?>>Invalidado</option>
                    <option value="<?= ValidacaoIndicacao::STATUS_CANCELADO ?>" <?= $filters['status'] === ValidacaoIndicacao::STATUS_CANCELADO ? 'selected' : '' ?>>Cancelado</option>
                </select>
            </div>
            <div class="form-group">
                <label for="cpf">CPF</label>
                <input type="text" id="cpf" name="cpf" value="<?= e($filters['cpf']) ?>">
            </div>
            <div class="form-group">
                <label for="nome">Nome</label>
                <input type="text" id="nome" name="nome" value="<?= e($filters['nome']) ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" value="<?= e($filters['email']) ?>">
            </div>
            <div class="form-group">
                <label for="whatsapp">WhatsApp</label>
                <input type="text" id="whatsapp" name="whatsapp" value="<?= e($filters['whatsapp']) ?>">
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
            <a href="<?= url('/admin/validacoes') ?>" class="btn btn--ghost">Limpar</a>
            <button type="submit" class="btn btn--primary">Filtrar</button>
        </div>
    </form>
<?php }); ?>

<section class="admin-stats stats-grid">
    <article class="stat-card">
        <span class="stat-card__value"><?= $stats['pendentes'] ?? 0 ?></span>
        <span class="stat-card__label">Pendentes</span>
    </article>
    <article class="stat-card">
        <span class="stat-card__value"><?= $stats['em_analise'] ?? 0 ?></span>
        <span class="stat-card__label">Em análise</span>
    </article>
    <article class="stat-card">
        <span class="stat-card__value"><?= $stats['validadas'] ?? 0 ?></span>
        <span class="stat-card__label">Aprovadas</span>
    </article>
    <article class="stat-card">
        <span class="stat-card__value"><?= $stats['invalidadas'] ?? 0 ?></span>
        <span class="stat-card__label">Reprovadas</span>
    </article>
    <article class="stat-card">
        <span class="stat-card__value"><?= $stats['canceladas'] ?? 0 ?></span>
        <span class="stat-card__label">Canceladas</span>
    </article>
</section>

<?php admin_table([
    'title' => 'Central de Validações',
    'meta' => count($validacoes) . ' registro(s)',
    'emptyMessage' => 'Nenhuma validação encontrada.',
    'columns' => [
        ['label' => 'Indicador'],
        ['label' => 'Indicado'],
        ['label' => 'WhatsApp'],
        ['label' => 'Campanha'],
        ['label' => 'Status'],
        ['label' => 'Data da indicação'],
        ['label' => 'Tempo aguardando'],
        ['label' => 'Ações', 'class' => 'admin-table__col--actions', 'align' => 'right'],
    ],
    'rows' => $validacoes,
], static function (array $validacao): void {
    $status = (string) $validacao['status'];
    $waitTime = ValidacaoIndicacao::waitTimeMeta($validacao);
    $indicacaoDate = (string) ($validacao['indicacao_created_at'] ?? $validacao['created_at']);
    ?>
    <tr>
        <td class="admin-table__td admin-table__td--wrap admin-table__td--primary">
            <?= e((string) ($validacao['usuario_nome'] ?? 'N/A')) ?>
        </td>
        <td class="admin-table__td admin-table__td--wrap">
            <?= e((string) ($validacao['nome_indicado'] ?: '—')) ?>
        </td>
        <td class="admin-table__td">
            <?= !empty($validacao['telefone_indicado']) ? e(format_phone((string) $validacao['telefone_indicado'])) : '—' ?>
        </td>
        <td class="admin-table__td admin-table__td--wrap">
            <?= e((string) ($validacao['campanha_nome'] ?? '—')) ?>
        </td>
        <td class="admin-table__td">
            <span class="badge <?= e(ValidacaoIndicacao::adminStatusBadgeClass($status)) ?>">
                <?= ValidacaoIndicacao::adminStatusIcon($status) ?>
                <?= e(ValidacaoIndicacao::adminStatusLabel($status)) ?>
            </span>
        </td>
        <td class="admin-table__td"><?= e(date('d/m/Y H:i', strtotime($indicacaoDate))) ?></td>
        <td class="admin-table__td">
            <span class="wait-time <?= e($waitTime['class']) ?>"><?= e($waitTime['label']) ?></span>
        </td>
        <td class="admin-table__td admin-table__col--actions">
            <div class="admin-table__actions">
                <a
                    href="<?= url('/admin/validacoes/' . $validacao['id']) ?>"
                    class="btn btn--sm btn--ghost"
                    aria-label="Ver detalhes da validação #<?= (int) $validacao['id'] ?>"
                    title="Ver detalhes"
                >👁</a>
                <?php if (ValidacaoIndicacao::canStartReview($validacao['status'])): ?>
                    <form method="POST" action="<?= url('/admin/validacoes/iniciar') ?>" class="inline-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= $validacao['id'] ?>">
                        <button type="submit" class="btn btn--sm btn--primary">Iniciar</button>
                    </form>
                <?php endif; ?>
                <?php if (ValidacaoIndicacao::canDecide($validacao['status'])): ?>
                    <form method="POST" action="<?= url('/admin/validacoes/aprovar') ?>" class="inline-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= $validacao['id'] ?>">
                        <button type="submit" class="btn btn--sm btn--success">Aprovar</button>
                    </form>
                    <form method="POST" action="<?= url('/admin/validacoes/rejeitar') ?>" class="inline-form" onsubmit="return confirm('Tem certeza que deseja rejeitar?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= $validacao['id'] ?>">
                        <button type="submit" class="btn btn--sm btn--danger">Rejeitar</button>
                    </form>
                <?php endif; ?>
                <?php if (ValidacaoIndicacao::canCancel($validacao['status'])): ?>
                    <form method="POST" action="<?= url('/admin/validacoes/cancelar') ?>" class="inline-form" onsubmit="return confirm('Tem certeza que deseja cancelar?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= $validacao['id'] ?>">
                        <button type="submit" class="btn btn--sm btn--ghost">Cancelar</button>
                    </form>
                <?php endif; ?>
            </div>
        </td>
    </tr>
<?php }); ?>
