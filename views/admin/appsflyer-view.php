<?php declare(strict_types=1); ?>
<?php $subtitle = 'Consulte informações do evento recebido.'; ?>

<section class="mm-card">
    <div class="mm-card__header">
        <h2 class="mm-card__title">Informações do Evento</h2>
        <span class="badge badge--<?= strtolower($event['af_status']) ?>">
            <?= AppsFlyerStatus::from($event['af_status'])->icon() ?>
            <?= e(AppsFlyerStatus::from($event['af_status'])->label()) ?>
        </span>
    </div>

    <div class="info-grid">
        <div class="info-grid__item">
            <span class="info-grid__label">ID</span>
            <span class="info-grid__value"><?= e($event['id']) ?></span>
        </div>
        <div class="info-grid__item">
            <span class="info-grid__label">AppsFlyer ID</span>
            <span class="info-grid__value"><?= e($event['appsflyer_id'] ?? 'N/A') ?></span>
        </div>
        <div class="info-grid__item">
            <span class="info-grid__label">Nome do Evento</span>
            <span class="info-grid__value"><?= e($event['event_name'] ?? 'N/A') ?></span>
        </div>
        <div class="info-grid__item">
            <span class="info-grid__label">Valor do Evento</span>
            <span class="info-grid__value"><?= e($event['event_value'] ?? 'N/A') ?></span>
        </div>
        <div class="info-grid__item">
            <span class="info-grid__label">Tipo de Instalação</span>
            <span class="info-grid__value">
                <?php if ($event['install_type']): ?>
                    <?= e(InstallType::from($event['install_type'])->label()) ?>
                <?php else: ?>
                    N/A
                <?php endif; ?>
            </span>
        </div>
        <div class="info-grid__item">
            <span class="info-grid__label">Plataforma</span>
            <span class="info-grid__value"><?= e($event['platform'] ?? 'N/A') ?></span>
        </div>
        <div class="info-grid__item">
            <span class="info-grid__label">Media Source</span>
            <span class="info-grid__value"><?= e($event['media_source'] ?? 'N/A') ?></span>
        </div>
        <div class="info-grid__item">
            <span class="info-grid__label">Campanha</span>
            <span class="info-grid__value"><?= e($event['campaign'] ?? 'N/A') ?></span>
        </div>
        <div class="info-grid__item">
            <span class="info-grid__label">Campaign ID</span>
            <span class="info-grid__value"><?= e($event['campaign_id'] ?? 'N/A') ?></span>
        </div>
        <div class="info-grid__item">
            <span class="info-grid__label">Usuário ID</span>
            <span class="info-grid__value"><?= e($event['usuario_id'] ?? 'N/A') ?></span>
        </div>
        <div class="info-grid__item">
            <span class="info-grid__label">Indicação ID</span>
            <span class="info-grid__value"><?= e($event['indicacao_id'] ?? 'N/A') ?></span>
        </div>
        <div class="info-grid__item">
            <span class="info-grid__label">Criado em</span>
            <span class="info-grid__value"><?= e(date('d/m/Y H:i:s', strtotime($event['created_at']))) ?></span>
        </div>
    </div>
</section>

<?php if ($event['raw_payload']): ?>
<section class="mm-card">
    <div class="mm-card__header">
        <h2 class="mm-card__title">Payload Original</h2>
    </div>
    <pre class="code-block"><?= e(json_encode($event['raw_payload'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
</section>
<?php endif; ?>

<?php if ($event['af_status'] === AppsFlyerStatus::PENDING->value || $event['af_status'] === AppsFlyerStatus::RECEIVED->value): ?>
<section class="mm-card">
    <div class="mm-card__header">
        <h2 class="mm-card__title">Ações</h2>
    </div>
    <form method="POST" action="<?= url('/admin/appsflyer/validar') ?>" class="form">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $event['id'] ?>">
        <button type="submit" class="btn btn--block btn--success">Validar Evento</button>
    </form>
    <form method="POST" action="<?= url('/admin/appsflyer/rejeitar') ?>" class="form">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $event['id'] ?>">
        <div class="form-group">
            <label for="reason">Motivo da Rejeição</label>
            <textarea id="reason" name="reason" rows="2"></textarea>
        </div>
        <button type="submit" class="btn btn--block btn--danger" onsubmit="return confirm('Tem certeza que deseja rejeitar este evento?');">Rejeitar Evento</button>
    </form>
</section>
<?php endif; ?>
