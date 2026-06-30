<?php declare(strict_types=1); ?>
<?php $subtitle = 'Gerencie a validação das indicações.'; ?>

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

<section class="stats-grid">
    <div class="stat-card">
        <div class="stat-card__value"><?= $stats['total'] ?? 0 ?></div>
        <div class="stat-card__label">Total</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__value"><?= $stats['pendentes'] ?? 0 ?></div>
        <div class="stat-card__label">Pendentes</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__value"><?= $stats['em_analise'] ?? 0 ?></div>
        <div class="stat-card__label">Em Análise</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__value"><?= $stats['validadas'] ?? 0 ?></div>
        <div class="stat-card__label">Validadas</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__value"><?= $stats['invalidadas'] ?? 0 ?></div>
        <div class="stat-card__label">Inválidas</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__value"><?= $stats['canceladas'] ?? 0 ?></div>
        <div class="stat-card__label">Canceladas</div>
    </div>
</section>

<?php if ($stats['total'] > 0): ?>
    <?php 
    $taxaAprovacao = $stats['validadas'] > 0 
        ? round(($stats['validadas'] / $stats['total']) * 100, 2) 
        : 0;
    ?>
    <section class="mm-card">
        <div class="mm-card__header">
            <h2 class="mm-card__title">Taxa de Aprovação</h2>
        </div>
        <div class="progress-bar">
            <div class="progress-bar__fill" style="width: <?= $taxaAprovacao ?>%"></div>
        </div>
        <p class="progress-bar__label"><?= $taxaAprovacao ?>%</p>
    </section>
<?php endif; ?>

<?php admin_table([
    'title' => 'Lista de Validações',
    'emptyMessage' => 'Nenhuma validação encontrada.',
    'columns' => [
        ['label' => 'Usuário'],
        ['label' => 'Indicado'],
        ['label' => 'Data'],
        ['label' => 'Motivo'],
        ['label' => 'Status'],
        ['label' => 'Ações', 'class' => 'admin-table__col--actions', 'align' => 'right'],
    ],
    'rows' => $validacoes,
], static function (array $validacao): void { ?>
    <tr>
        <td class="admin-table__td admin-table__td--wrap admin-table__td--primary">
            <?= ValidacaoIndicacao::statusIcon($validacao['status']) ?>
            <?= e($validacao['usuario_nome'] ?? 'N/A') ?>
        </td>
        <td class="admin-table__td admin-table__td--wrap"><?= e($validacao['nome_indicado'] ?: '—') ?></td>
        <td class="admin-table__td"><?= e(date('d/m/Y H:i', strtotime($validacao['created_at']))) ?></td>
        <td class="admin-table__td admin-table__td--wrap">
            <?php if ($validacao['motivo']): ?>
                <span class="admin-table__meta admin-table__meta--error"><?= e($validacao['motivo']) ?></span>
            <?php else: ?>
                —
            <?php endif; ?>
        </td>
        <td class="admin-table__td">
            <span class="badge badge--<?= strtolower($validacao['status']) ?>">
                <?= e(ValidacaoIndicacao::statusLabel($validacao['status'])) ?>
            </span>
        </td>
        <td class="admin-table__td admin-table__col--actions">
            <div class="admin-table__actions">
                <a href="<?= url('/admin/validacoes/' . $validacao['id']) ?>" class="btn btn--sm btn--ghost">Detalhes</a>
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
