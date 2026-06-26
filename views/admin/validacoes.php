<?php declare(strict_types=1); ?>
<?php require BASE_PATH . '/views/partials/alerts.php'; ?>

<section class="page-hero">
    <h1 class="page-hero__title">Validação de Indicações</h1>
    <p class="page-hero__subtitle">Gerencie a validação das indicações.</p>
</section>

<section class="mm-card">
    <div class="mm-card__header">
        <h2 class="mm-card__title">Filtros</h2>
    </div>
    <form method="GET" action="<?= url('/admin/validacoes') ?>" class="form">
        <div class="form-row">
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">Todos</option>
                    <option value="<?= Validacao::STATUS_PENDENTE ?>" <?= $filters['status'] === Validacao::STATUS_PENDENTE ? 'selected' : '' ?>>Pendente</option>
                    <option value="<?= Validacao::STATUS_EM_ANALISE ?>" <?= $filters['status'] === Validacao::STATUS_EM_ANALISE ? 'selected' : '' ?>>Em Análise</option>
                    <option value="<?= Validacao::STATUS_VALIDADO ?>" <?= $filters['status'] === Validacao::STATUS_VALIDADO ? 'selected' : '' ?>>Validado</option>
                    <option value="<?= Validacao::STATUS_INVALIDADO ?>" <?= $filters['status'] === Validacao::STATUS_INVALIDADO ? 'selected' : '' ?>>Invalidado</option>
                    <option value="<?= Validacao::STATUS_CANCELADO ?>" <?= $filters['status'] === Validacao::STATUS_CANCELADO ? 'selected' : '' ?>>Cancelado</option>
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
                <label for="telefone">Telefone</label>
                <input type="text" id="telefone" name="telefone" value="<?= e($filters['telefone']) ?>">
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
        <button type="submit" class="btn btn--primary">Filtrar</button>
        <a href="<?= url('/admin/validacoes') ?>" class="btn btn--ghost">Limpar</a>
    </form>
</section>

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

<section class="mm-card">
    <div class="mm-card__header">
        <h2 class="mm-card__title">Lista de Validações</h2>
    </div>

    <?php if ($validacoes === []): ?>
        <p class="mm-card__placeholder">Nenhuma validação encontrada.</p>
    <?php else: ?>
        <ul class="admin-list">
            <?php foreach ($validacoes as $validacao): ?>
                <li class="admin-list__item">
                    <div class="admin-list__info">
                        <span class="admin-list__icon"><?= Validacao::statusIcon($validacao['status']) ?></span>
                        <div>
                            <strong><?= e($validacao['usuario_nome'] ?? 'N/A') ?></strong>
                            <?php if ($validacao['nome_indicado']): ?>
                                <span class="admin-list__meta">Indicado: <?= e($validacao['nome_indicado']) ?></span>
                            <?php endif; ?>
                            <span class="admin-list__meta">
                                <?= e(date('d/m/Y H:i', strtotime($validacao['created_at']))) ?>
                            </span>
                            <?php if ($validacao['motivo']): ?>
                                <span class="admin-list__meta admin-list__meta--error">
                                    Motivo: <?= e($validacao['motivo']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="admin-list__actions">
                        <span class="badge badge--<?= strtolower($validacao['status']) ?>">
                            <?= e(Validacao::statusLabel($validacao['status'])) ?>
                        </span>
                        <a href="<?= url('/admin/validacoes/' . $validacao['id']) ?>" class="btn btn--sm btn--ghost">Detalhes</a>
                        <?php if ($validacao['status'] === Validacao::STATUS_PENDENTE): ?>
                            <form method="POST" action="<?= url('/admin/validacoes/iniciar') ?>" class="inline-form">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= $validacao['id'] ?>">
                                <button type="submit" class="btn btn--sm btn--primary">Iniciar</button>
                            </form>
                        <?php elseif ($validacao['status'] === Validacao::STATUS_EM_ANALISE): ?>
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
                        <?php elseif ($validacao['status'] === Validacao::STATUS_EM_ANALISE || $validacao['status'] === Validacao::STATUS_PENDENTE): ?>
                            <form method="POST" action="<?= url('/admin/validacoes/cancelar') ?>" class="inline-form" onsubmit="return confirm('Tem certeza que deseja cancelar?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= $validacao['id'] ?>">
                                <button type="submit" class="btn btn--sm btn--ghost">Cancelar</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
