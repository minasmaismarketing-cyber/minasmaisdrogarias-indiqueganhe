<?php declare(strict_types=1); ?>
<?php $subtitle = 'Gerencie a integração com AppsFlyer.'; ?>

<section class="mm-card">
    <div class="mm-card__header">
        <h2 class="mm-card__title">Status da Integração</h2>
    </div>
    
    <div class="integration-status">
        <span class="integration-status__indicator integration-status__indicator--<?= $integrationEnabled ? 'active' : 'inactive' ?>">
            <?= $integrationEnabled ? '🟢' : '🔴' ?>
        </span>
        <span class="integration-status__text">
            <?= $integrationEnabled ? 'Integração Ativa' : 'Integração Inativa' ?>
        </span>
    </div>
    
    <p class="mm-card__text">
        A integração com AppsFlyer está atualmente <?= $integrationEnabled ? 'ativa' : 'inativa' ?>.
        Configure as credenciais em config/appsflyer.php para ativar.
    </p>
</section>

<section class="stats-grid">
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

<section class="mm-card">
    <div class="mm-card__header">
        <h2 class="mm-card__title">Últimos Eventos</h2>
    </div>

    <?php if ($recentEvents === []): ?>
        <p class="mm-card__placeholder">Nenhum evento recebido ainda.</p>
    <?php else: ?>
        <ul class="admin-list">
            <?php foreach ($recentEvents as $event): ?>
                <li class="admin-list__item">
                    <div class="admin-list__info">
                        <span class="admin-list__icon">
                            <?= AppsFlyerStatus::from($event['af_status'])->icon() ?>
                        </span>
                        <div>
                            <strong><?= e($event['event_name'] ?? 'N/A') ?></strong>
                            <span class="admin-list__meta">
                                <?= e($event['appsflyer_id'] ?? 'N/A') ?>
                            </span>
                            <span class="admin-list__meta">
                                <?= e($event['platform'] ?? 'N/A') ?>
                            </span>
                            <span class="admin-list__meta">
                                <?= e(date('d/m/Y H:i', strtotime($event['created_at']))) ?>
                            </span>
                        </div>
                    </div>
                    <span class="badge badge--<?= strtolower($event['af_status']) ?>">
                        <?= e(AppsFlyerStatus::from($event['af_status'])->label()) ?>
                    </span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>

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

<section class="mm-card">
    <div class="mm-card__header">
        <h2 class="mm-card__title">Todos os Eventos</h2>
    </div>

    <?php if ($events === []): ?>
        <p class="mm-card__placeholder">Nenhum evento encontrado.</p>
    <?php else: ?>
        <ul class="admin-list">
            <?php foreach ($events as $event): ?>
                <li class="admin-list__item">
                    <div class="admin-list__info">
                        <span class="admin-list__icon">
                            <?= AppsFlyerStatus::from($event['af_status'])->icon() ?>
                        </span>
                        <div>
                            <strong><?= e($event['event_name'] ?? 'N/A') ?></strong>
                            <span class="admin-list__meta">
                                <?= e($event['appsflyer_id'] ?? 'N/A') ?>
                            </span>
                            <span class="admin-list__meta">
                                <?= e($event['platform'] ?? 'N/A') ?>
                            </span>
                            <?php if ($event['install_type']): ?>
                                <span class="admin-list__meta">
                                    <?= e(InstallType::from($event['install_type'])->label()) ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($event['media_source']): ?>
                                <span class="admin-list__meta">
                                    <?= e($event['media_source']) ?>
                                </span>
                            <?php endif; ?>
                            <span class="admin-list__meta">
                                <?= e(date('d/m/Y H:i', strtotime($event['created_at']))) ?>
                            </span>
                        </div>
                    </div>
                    <div class="admin-list__actions">
                        <span class="badge badge--<?= strtolower($event['af_status']) ?>">
                            <?= e(AppsFlyerStatus::from($event['af_status'])->label()) ?>
                        </span>
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
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
