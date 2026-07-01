<?php declare(strict_types=1); ?>
<?php $subtitle = 'Gerencie a integração com AppsFlyer.'; ?>

<section class="admin-card">
    <header class="admin-card__header">
        <h2 class="admin-card__title">Status da Integração</h2>
    </header>
    
    <div class="integration-status">
        <span class="integration-status__indicator integration-status__indicator--<?= $integrationEnabled ? 'active' : 'inactive' ?>">
            <?= $integrationEnabled ? '🟢' : '🔴' ?>
        </span>
        <span class="integration-status__text">
            <?= $integrationEnabled ? 'Integração Ativa' : 'Integração Inativa' ?>
        </span>
    </div>
    
    <p class="admin-card__text">
        A integração com AppsFlyer está atualmente <?= $integrationEnabled ? 'ativa' : 'inativa' ?>.
        Configure as credenciais em config/appsflyer.php para ativar.
    </p>
</section>

<section class="admin-stats stats-grid">
    <div class="stat-card">
        <div class="stat-card__value"><?= $stats['total'] ?? 0 ?></div>
        <div class="stat-card__label">Total</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__value"><?= $stats['pending'] ?? 0 ?></div>
        <div class="stat-card__label">Pendentes ⏳</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__value"><?= $stats['received'] ?? 0 ?></div>
        <div class="stat-card__label">Recebidos 📥</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__value"><?= $stats['validated'] ?? 0 ?></div>
        <div class="stat-card__label">Validados ✅</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__value"><?= $stats['invalid'] ?? 0 ?></div>
        <div class="stat-card__label">Inválidos ❌</div>
    </div>
</section>

<?php admin_table([
    'title' => 'Últimos Eventos',
    'emptyMessage' => 'Nenhum evento recebido ainda.',
    'columns' => [
        ['label' => 'Evento'],
        ['label' => 'AppsFlyer ID'],
        ['label' => 'Plataforma'],
        ['label' => 'Data'],
        ['label' => 'Status'],
    ],
    'rows' => $recentEvents,
], static function (array $event): void { ?>
    <tr>
        <td class="admin-table__td admin-table__td--wrap admin-table__td--primary">
            <?= AppsFlyerStatus::from($event['af_status'])->icon() ?>
            <?= e($event['event_name'] ?? 'N/A') ?>
        </td>
        <td class="admin-table__td admin-table__td--wrap"><?= e($event['appsflyer_id'] ?? 'N/A') ?></td>
        <td class="admin-table__td"><?= e($event['platform'] ?? 'N/A') ?></td>
        <td class="admin-table__td"><?= e(date('d/m/Y H:i', strtotime($event['created_at']))) ?></td>
        <td class="admin-table__td">
            <span class="badge badge--<?= strtolower($event['af_status']) ?>">
                <?= e(AppsFlyerStatus::from($event['af_status'])->label()) ?>
            </span>
        </td>
    </tr>
<?php }); ?>

<?php admin_filter_panel('admin-appsflyer-filters', $filters, static function () use ($filters): void { ?>
    <form method="GET" action="<?= url('/admin/appsflyer') ?>" class="form admin-filter-panel__form">
        <div class="form-row">
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">Todos</option>
                    <option value="<?= AppsFlyerStatus::PENDING->value ?>" <?= $filters['status'] === AppsFlyerStatus::PENDING->value ? 'selected' : '' ?>>Pendente</option>
                    <option value="<?= AppsFlyerStatus::RECEIVED->value ?>" <?= $filters['status'] === AppsFlyerStatus::RECEIVED->value ? 'selected' : '' ?>>Recebido</option>
                    <option value="<?= AppsFlyerStatus::VALIDATED->value ?>" <?= $filters['status'] === AppsFlyerStatus::VALIDATED->value ? 'selected' : '' ?>>Validado</option>
                    <option value="<?= AppsFlyerStatus::INVALID->value ?>" <?= $filters['status'] === AppsFlyerStatus::INVALID->value ? 'selected' : '' ?>>Inválido</option>
                </select>
            </div>
            <div class="form-group">
                <label for="platform">Plataforma</label>
                <select id="platform" name="platform">
                    <option value="">Todas</option>
                    <option value="android" <?= $filters['platform'] === 'android' ? 'selected' : '' ?>>Android</option>
                    <option value="ios" <?= $filters['platform'] === 'ios' ? 'selected' : '' ?>>iOS</option>
                    <option value="web" <?= $filters['platform'] === 'web' ? 'selected' : '' ?>>Web</option>
                </select>
            </div>
            <div class="form-group">
                <label for="install_type">Tipo de Instalação</label>
                <select id="install_type" name="install_type">
                    <option value="">Todos</option>
                    <option value="<?= InstallType::FIRST_INSTALL->value ?>" <?= $filters['install_type'] === InstallType::FIRST_INSTALL->value ? 'selected' : '' ?>>Primeira Instalação</option>
                    <option value="<?= InstallType::REINSTALL->value ?>" <?= $filters['install_type'] === InstallType::REINSTALL->value ? 'selected' : '' ?>>Reinstalação</option>
                    <option value="<?= InstallType::REENGAGEMENT->value ?>" <?= $filters['install_type'] === InstallType::REENGAGEMENT->value ? 'selected' : '' ?>>Reengajamento</option>
                    <option value="<?= InstallType::UNKNOWN->value ?>" <?= $filters['install_type'] === InstallType::UNKNOWN->value ? 'selected' : '' ?>>Desconhecido</option>
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
            <a href="<?= url('/admin/appsflyer') ?>" class="btn btn--ghost">Limpar</a>
            <button type="submit" class="btn btn--primary">Filtrar</button>
        </div>
    </form>
<?php }); ?>

<?php admin_table([
    'title' => 'Todos os Eventos',
    'emptyMessage' => 'Nenhum evento encontrado.',
    'columns' => [
        ['label' => 'Evento'],
        ['label' => 'AppsFlyer ID'],
        ['label' => 'Plataforma'],
        ['label' => 'Instalação'],
        ['label' => 'Origem'],
        ['label' => 'Data'],
        ['label' => 'Status'],
        ['label' => 'Ações', 'class' => 'admin-table__col--actions', 'align' => 'right'],
    ],
    'rows' => $events,
], static function (array $event): void { ?>
    <tr>
        <td class="admin-table__td admin-table__td--wrap admin-table__td--primary">
            <?= AppsFlyerStatus::from($event['af_status'])->icon() ?>
            <?= e($event['event_name'] ?? 'N/A') ?>
        </td>
        <td class="admin-table__td admin-table__td--wrap"><?= e($event['appsflyer_id'] ?? 'N/A') ?></td>
        <td class="admin-table__td"><?= e($event['platform'] ?? 'N/A') ?></td>
        <td class="admin-table__td admin-table__td--wrap">
            <?= $event['install_type'] ? e(InstallType::from($event['install_type'])->label()) : '—' ?>
        </td>
        <td class="admin-table__td admin-table__td--wrap"><?= e($event['media_source'] ?: '—') ?></td>
        <td class="admin-table__td"><?= e(date('d/m/Y H:i', strtotime($event['created_at']))) ?></td>
        <td class="admin-table__td">
            <span class="badge badge--<?= strtolower($event['af_status']) ?>">
                <?= e(AppsFlyerStatus::from($event['af_status'])->label()) ?>
            </span>
        </td>
        <td class="admin-table__td admin-table__col--actions">
            <div class="admin-table__actions">
                <a href="<?= url('/admin/appsflyer/' . $event['id']) ?>" class="btn btn--sm btn--ghost">Detalhes</a>
                <?php if ($event['af_status'] === AppsFlyerStatus::PENDING->value || $event['af_status'] === AppsFlyerStatus::RECEIVED->value): ?>
                    <form method="POST" action="<?= url('/admin/appsflyer/validar') ?>" class="inline-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= $event['id'] ?>">
                        <button type="submit" class="btn btn--sm btn--success">Validar</button>
                    </form>
                    <form method="POST" action="<?= url('/admin/appsflyer/rejeitar') ?>" class="inline-form" onsubmit="return confirm('Tem certeza que deseja rejeitar este evento?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= $event['id'] ?>">
                        <button type="submit" class="btn btn--sm btn--danger">Rejeitar</button>
                    </form>
                <?php endif; ?>
            </div>
        </td>
    </tr>
<?php }); ?>
