<?php declare(strict_types=1); ?>
<?php $subtitle = 'Consulte informações do evento recebido.'; ?>

<?php
$eventLines = [
    ['label' => 'ID:', 'value' => (string) $event['id']],
    ['label' => 'AppsFlyer ID:', 'value' => (string) ($event['appsflyer_id'] ?? 'N/A')],
    ['label' => 'Nome do Evento:', 'value' => (string) ($event['event_name'] ?? 'N/A')],
    ['label' => 'Valor do Evento:', 'value' => (string) ($event['event_value'] ?? 'N/A')],
    [
        'label' => 'Tipo de Instalação:',
        'value' => $event['install_type'] ? InstallType::from($event['install_type'])->label() : 'N/A',
    ],
    ['label' => 'Plataforma:', 'value' => (string) ($event['platform'] ?? 'N/A')],
    ['label' => 'Media Source:', 'value' => (string) ($event['media_source'] ?? 'N/A')],
    ['label' => 'Campanha:', 'value' => (string) ($event['campaign'] ?? 'N/A')],
    ['label' => 'Campaign ID:', 'value' => (string) ($event['campaign_id'] ?? 'N/A')],
    ['label' => 'Usuário ID:', 'value' => (string) ($event['usuario_id'] ?? 'N/A')],
    ['label' => 'Indicação ID:', 'value' => (string) ($event['indicacao_id'] ?? 'N/A')],
    ['label' => 'Criado em:', 'value' => date('d/m/Y H:i:s', strtotime($event['created_at']))],
];
$eventBadge = '<span class="badge badge--' . strtolower($event['af_status']) . '">'
    . AppsFlyerStatus::from($event['af_status'])->icon() . ' '
    . e(AppsFlyerStatus::from($event['af_status'])->label()) . '</span>';
admin_detail_card('Informações do Evento', $eventLines, $eventBadge);
?>

<?php if ($event['raw_payload']): ?>
<section class="admin-card">
    <header class="admin-card__header">
        <h2 class="admin-card__title">Payload Original</h2>
    </header>
    <pre class="code-block"><?= e(json_encode($event['raw_payload'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
</section>
<?php endif; ?>

<?php if ($event['af_status'] === AppsFlyerStatus::PENDING->value || $event['af_status'] === AppsFlyerStatus::RECEIVED->value): ?>
<section class="admin-card">
    <header class="admin-card__header">
        <h2 class="admin-card__title">Ações</h2>
    </header>
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
