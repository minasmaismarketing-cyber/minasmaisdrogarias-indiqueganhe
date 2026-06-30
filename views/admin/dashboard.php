<?php declare(strict_types=1); ?>
<?php $subtitle = 'Gerencie o sistema de indicações.'; ?>

<section class="stats-grid stats-grid--admin-dashboard">
    <article class="stat-card">
        <span class="stat-card__value"><?= $totalIndicacoes ?></span>
        <span class="stat-card__label">Total de Indicações</span>
    </article>
    <article class="stat-card">
        <span class="stat-card__value"><?= $validacaoStats['pendentes'] ?? 0 ?></span>
        <span class="stat-card__label">Pendentes 🟡</span>
    </article>
    <article class="stat-card">
        <span class="stat-card__value"><?= $validacaoStats['em_analise'] ?? 0 ?></span>
        <span class="stat-card__label">Em Análise 🟠</span>
    </article>
    <article class="stat-card">
        <span class="stat-card__value"><?= $validacaoStats['validadas'] ?? 0 ?></span>
        <span class="stat-card__label">Aprovadas 🟢</span>
    </article>
    <article class="stat-card">
        <span class="stat-card__value"><?= $validacaoStats['invalidadas'] ?? 0 ?></span>
        <span class="stat-card__label">Reprovadas 🔴</span>
    </article>
    <article class="stat-card">
        <span class="stat-card__value"><?= $validacaoStats['canceladas'] ?? 0 ?></span>
        <span class="stat-card__label">Canceladas ⚫</span>
    </article>
    <article class="stat-card">
        <span class="stat-card__value"><?= $cuponsGerados ?></span>
        <span class="stat-card__label">Cupons Gerados</span>
    </article>
    <article class="stat-card">
        <span class="stat-card__value"><?= $taxaConversao ?>%</span>
        <span class="stat-card__label">Taxa de Conversão</span>
    </article>
</section>

<?php if ($campanhaAtiva): ?>
<section class="mm-card mm-card--highlight">
    <div class="mm-card__header">
        <h2 class="mm-card__title">Campanha Ativa</h2>
    </div>
    <div class="campaign-status">
        <p class="campaign-status__name"><?= e($campanhaAtiva['nome']) ?></p>
        <p class="campaign-status__period">
            <?= e(date('d/m/Y', strtotime($campanhaAtiva['inicio']))) ?> a 
            <?= e(date('d/m/Y', strtotime($campanhaAtiva['fim']))) ?>
        </p>
    </div>
</section>
<?php endif; ?>

<section class="mm-card admin-dashboard-activities">
    <div class="mm-card__header">
        <h2 class="mm-card__title">Últimas Atividades</h2>
    </div>
    
    <?php if ($recentEvents === []): ?>
        <p class="mm-card__text">Nenhuma atividade registrada.</p>
    <?php else: ?>
        <div class="admin-dashboard-activities__scroll">
            <div class="activity-timeline">
                <?php foreach ($recentEvents as $event): ?>
                    <div class="activity-timeline__item">
                        <span class="activity-timeline__icon"><?= Evento::eventoIcon($event['evento']) ?></span>
                        <div class="activity-timeline__content">
                            <span class="activity-timeline__label"><?= e(Evento::eventoLabel($event['evento'])) ?></span>
                            <span class="activity-timeline__user"><?= e($event['usuario_nome'] ?? 'Sistema') ?></span>
                            <span class="activity-timeline__date"><?= e(date('d/m/Y H:i', strtotime($event['created_at']))) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</section>
