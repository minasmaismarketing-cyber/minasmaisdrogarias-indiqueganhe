<?php declare(strict_types=1); ?>
<?php
$subtitle = 'Painel de homologação AppsFlyer (somente leitura).';
$s = $snapshot;

$statusLines = [
    ['label' => 'AppsFlyer Enabled:', 'value' => ($s['enabled'] ?? false) ? 'Sim' : 'Não'],
    ['label' => 'Modo Homologação:', 'value' => ($s['homologation'] ?? false) ? 'Ativo' : 'Inativo'],
    ['label' => 'OneLink Ativo:', 'value' => ($s['oneLinkActive'] ?? false) ? 'Sim' : 'Não (template ou flag)'],
    ['label' => 'Template OneLink:', 'value' => (string) ($s['template'] ?? '') !== '' ? (string) $s['template'] : 'Não configurado'],
    ['label' => 'Media Source (pid):', 'value' => (string) ($s['default_media_source'] ?? '') ?: '—'],
    ['label' => 'Campanha (c):', 'value' => (string) ($s['default_campaign'] ?? '') ?: '—'],
];

$renderJsonBlock = static function (?array $data, string $emptyMessage): void {
    if ($data === null || $data === []) {
        echo '<p class="admin-card__text">' . e($emptyMessage) . '</p>';
        return;
    }

    echo '<pre class="code-block">' . e(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre>';
};
?>

<section class="admin-card">
    <header class="admin-card__header">
        <h2 class="admin-card__title">Status da Integração</h2>
    </header>
    <?php admin_info_lines($statusLines); ?>
    <p class="admin-card__text">
        Endpoint de teste (homologação): <code>POST <?= e(url('/api/appsflyer/test')) ?></code>
    </p>
</section>

<section class="admin-card">
    <header class="admin-card__header">
        <h2 class="admin-card__title">Último OneLink Gerado</h2>
    </header>
    <?php $renderJsonBlock($s['lastOneLinkLog'] ?? null, 'Nenhum OneLink registrado em homologação.'); ?>
</section>

<section class="admin-card">
    <header class="admin-card__header">
        <h2 class="admin-card__title">Último Webhook</h2>
    </header>
    <?php if (!empty($s['lastWebhook'])): ?>
        <p class="admin-card__text">
            <?= e((string) ($s['lastWebhook']['created_at'] ?? '')) ?>
            — HTTP <?= e((string) ($s['lastWebhook']['status_code'] ?? '')) ?>
            — IP <?= e((string) ($s['lastWebhook']['ip'] ?? '')) ?>
        </p>
    <?php endif; ?>
    <?php $renderJsonBlock($s['lastWebhookLog'] ?? null, 'Nenhum webhook registrado.'); ?>
</section>

<section class="admin-card">
    <header class="admin-card__header">
        <h2 class="admin-card__title">Último Cadastro via App (API KOBE)</h2>
    </header>
    <?php if (!empty($s['lastApiKobe'])): ?>
        <p class="admin-card__text">
            <?= e((string) ($s['lastApiKobe']['created_at'] ?? '')) ?>
            — HTTP <?= e((string) ($s['lastApiKobe']['status_code'] ?? '')) ?>
        </p>
    <?php endif; ?>
    <?php $renderJsonBlock($s['lastApiKobeLog'] ?? null, 'Nenhuma chamada API KOBE registrada em homologação.'); ?>
</section>

<section class="admin-card">
    <header class="admin-card__header">
        <h2 class="admin-card__title">Último Evento AppsFlyer</h2>
    </header>
    <?php if (!empty($s['lastEvent'])): ?>
        <?php
        admin_detail_card('Evento', [
            ['label' => 'ID:', 'value' => (string) $s['lastEvent']['id']],
            ['label' => 'AppsFlyer ID:', 'value' => (string) ($s['lastEvent']['appsflyer_id'] ?? 'N/A')],
            ['label' => 'Evento:', 'value' => (string) ($s['lastEvent']['event_name'] ?? 'N/A')],
            ['label' => 'Status:', 'value' => (string) ($s['lastEvent']['af_status'] ?? 'N/A')],
            ['label' => 'Criado em:', 'value' => date('d/m/Y H:i:s', strtotime((string) $s['lastEvent']['created_at']))],
        ]);
        ?>
    <?php else: ?>
        <p class="admin-card__text">Nenhum evento persistido em appsflyer_events.</p>
    <?php endif; ?>
</section>

<section class="admin-card">
    <header class="admin-card__header">
        <h2 class="admin-card__title">Último Erro</h2>
    </header>
    <?php $renderJsonBlock($s['lastError'] ?? null, 'Nenhum erro registrado.'); ?>
</section>

<section class="admin-card">
    <header class="admin-card__header">
        <h2 class="admin-card__title">Último Teste Manual</h2>
    </header>
    <?php if (!empty($s['lastTestWebhook'])): ?>
        <p class="admin-card__text">
            <?= e((string) ($s['lastTestWebhook']['created_at'] ?? '')) ?>
            — HTTP <?= e((string) ($s['lastTestWebhook']['status_code'] ?? '')) ?>
        </p>
        <?php $renderJsonBlock($s['lastTestWebhook'], 'Sem dados.'); ?>
    <?php else: ?>
        <p class="admin-card__text">Nenhum teste via POST /api/appsflyer/test.</p>
    <?php endif; ?>
</section>
