<?php declare(strict_types=1); ?>
<?php $subtitle = 'Gerencie o sistema de indicações.'; ?>

<section class="stats-grid">
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
</section>

<section class="mm-card">
    <div class="mm-card__header">
        <h2 class="mm-card__title">Ações Rápidas</h2>
    </div>
    <div class="admin-actions">
        <a href="<?= url('/admin/campanhas') ?>" class="btn btn--block">Gerenciar Campanhas</a>
        <a href="<?= url('/admin/cupons') ?>" class="btn btn--block btn--outline">Gerenciar Cupons</a>
        <a href="<?= url('/admin/validacoes') ?>" class="btn btn--block btn--outline">Validar Indicações</a>
        <a href="<?= url('/admin/usuarios') ?>" class="btn btn--block btn--outline">Ver Usuários</a>
        <a href="<?= url('/admin/indicacoes') ?>" class="btn btn--block btn--outline">Ver Indicações</a>
        <a href="<?= url('/admin/configuracoes') ?>" class="btn btn--block btn--ghost">Configurações</a>
    </div>
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

<section class="stats-grid">
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

<section class="mm-card">
    <div class="mm-card__header">
        <h2 class="mm-card__title">Últimas Atividades</h2>
    </div>
    
    <?php if ($recentEvents === []): ?>
        <p class="mm-card__text">Nenhuma atividade registrada.</p>
    <?php else: ?>
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
    <?php endif; ?>
</section>
