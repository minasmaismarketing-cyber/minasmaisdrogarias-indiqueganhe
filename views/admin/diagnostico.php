<?php declare(strict_types=1); ?>
<?php
$subtitle = 'Acompanhe o ciclo completo de uma indicação (somente leitura).';

$stepBadgeClass = static function (string $kind): string {
    return match ($kind) {
        'done' => 'badge--success',
        'pending' => 'badge--warning',
        default => 'badge--neutral',
    };
};
?>

<section class="admin-card">
    <header class="admin-card__header">
        <h2 class="admin-card__title">Pesquisar indicação</h2>
    </header>

    <form method="GET" action="<?= url('/admin/diagnostico') ?>" class="form admin-diagnostico-search">
        <div class="form-group">
            <label for="diagnostico-q">Buscar por código, indicador, CPF, e-mail, telefone ou ID</label>
            <input
                type="search"
                id="diagnostico-q"
                name="q"
                value="<?= e($search ?? '') ?>"
                placeholder="Ex.: MMN6GAXJ, CPF, e-mail ou ID da indicação"
                autocomplete="off"
            >
        </div>
        <button type="submit" class="btn btn--primary">Buscar</button>
    </form>

    <?php if (!empty($searchError)): ?>
        <div class="alert alert--warning" role="alert"><?= e($searchError) ?></div>
    <?php endif; ?>
</section>

<?php if (!empty($report)): ?>
    <?php
    $summary = $report['summary'] ?? [];
    $side = $report['side_panel'] ?? [];
    $steps = $report['steps'] ?? [];
    $logs = $report['technical_logs'] ?? [];
    $hasLogs = ($logs['api_logs'] ?? []) !== [] || ($logs['appsflyer_events'] ?? []) !== [];
    $statusKey = strtolower((string) ($summary['current_status_key'] ?? ''));
    ?>

    <section class="admin-stats admin-diagnostico-stats">
        <article class="stat-card">
            <span class="stat-card__value"><?= (int) ($summary['completed_steps'] ?? 0) ?>/<?= (int) ($summary['total_steps'] ?? 0) ?></span>
            <span class="stat-card__label">Etapas concluídas</span>
        </article>
        <article class="stat-card">
            <span class="stat-card__value stat-card__value--sm">
                <span class="badge badge--<?= e($statusKey) ?>"><?= e((string) ($summary['current_status'] ?? '—')) ?></span>
            </span>
            <span class="stat-card__label">Status atual</span>
        </article>
        <article class="stat-card">
            <span class="stat-card__value stat-card__value--sm"><?= e((string) ($summary['since_created'] ?? '—')) ?></span>
            <span class="stat-card__label">Tempo desde criação</span>
        </article>
        <article class="stat-card">
            <span class="stat-card__value stat-card__value--sm"><?= e((string) ($summary['last_update'] ?? '—')) ?></span>
            <span class="stat-card__label">Última atualização</span>
        </article>
    </section>

    <div class="admin-diagnostico-layout">
        <div class="admin-diagnostico-main">
            <section class="admin-card">
                <header class="admin-card__header">
                    <h2 class="admin-card__title">Timeline do ciclo</h2>
                </header>

                <ul class="timeline timeline--diagnostic">
                    <?php foreach ($steps as $index => $step): ?>
                        <?php $kind = (string) ($step['kind'] ?? 'missing'); ?>
                        <li class="timeline__item timeline__item--<?= e($kind) ?>">
                            <span class="timeline__icon timeline__icon--step"><?= $index + 1 ?></span>
                            <div class="timeline__content">
                                <div class="timeline__head">
                                    <span class="timeline__label"><?= e((string) ($step['title'] ?? '')) ?></span>
                                    <span class="badge <?= e($stepBadgeClass($kind)) ?>"><?= e((string) ($step['status'] ?? '')) ?></span>
                                </div>
                                <p class="timeline__meta">
                                    <span><?= e((string) ($step['datetime'] ?? '—')) ?></span>
                                    · Origem: <strong><?= e((string) ($step['origin'] ?? '—')) ?></strong>
                                </p>
                                <?php if (!empty($step['observations'])): ?>
                                    <p class="timeline__description"><?= e((string) $step['observations']) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($step['data'])): ?>
                                    <?php admin_info_lines($step['data']); ?>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>

            <?php if ($hasLogs): ?>
                <details class="admin-card admin-collapse">
                    <summary class="admin-collapse__summary">Logs técnicos</summary>
                    <div class="admin-collapse__body">
                        <?php if (!empty($logs['api_logs'])): ?>
                            <h3 class="admin-card__subtitle">api_logs</h3>
                            <?php foreach ($logs['api_logs'] as $log): ?>
                                <pre class="code-block"><?= e(json_encode($log, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <?php if (!empty($logs['appsflyer_events'])): ?>
                            <h3 class="admin-card__subtitle">appsflyer_events</h3>
                            <?php foreach ($logs['appsflyer_events'] as $event): ?>
                                <pre class="code-block"><?= e(json_encode($event, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </details>
            <?php endif; ?>
        </div>

        <aside class="admin-diagnostico-aside">
            <?php admin_detail_card('Painel da indicação', [
                ['label' => 'Indicador:', 'value' => (string) ($side['indicador'] ?? '—')],
                ['label' => 'Indicado:', 'value' => (string) ($side['indicado'] ?? '—')],
                ['label' => 'Campanha:', 'value' => (string) ($side['campanha'] ?? '—')],
                ['label' => 'Origem:', 'value' => (string) ($side['origem'] ?? '—')],
                ['label' => 'AppsFlyer ID:', 'value' => (string) ($side['appsflyer_id'] ?? '—'), 'highlight' => ($side['appsflyer_id'] ?? '—') !== '—'],
                ['label' => 'Código da indicação:', 'value' => (string) ($side['codigo_indicacao'] ?? '—'), 'highlight' => true],
                ['label' => 'Código do cupom:', 'value' => (string) ($side['codigo_cupom'] ?? '—')],
            ]); ?>
        </aside>
    </div>
<?php elseif (($search ?? '') === ''): ?>
    <?php admin_empty_state('Informe um termo de busca para visualizar o diagnóstico.', '🔍'); ?>
<?php endif; ?>
