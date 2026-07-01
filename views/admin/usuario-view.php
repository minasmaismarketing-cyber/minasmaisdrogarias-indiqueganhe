<?php declare(strict_types=1); ?>
<?php $subtitle = 'Consulte informações e atividades do usuário.'; ?>

<?php admin_detail_card('Informações do Usuário', [
    ['label' => 'Nome:', 'value' => (string) $usuario['nome']],
    ['label' => 'CPF:', 'value' => Usuario::formatCpfDisplay($usuario)],
    ['label' => 'E-mail:', 'value' => (string) $usuario['email']],
    ['label' => 'WhatsApp:', 'value' => $usuario['whatsapp'] ? format_phone((string) $usuario['whatsapp']) : '—'],
    ['label' => 'Código de Indicação:', 'value' => (string) ($usuario['codigo_indicador'] ?? '—')],
    ['label' => 'Perfil:', 'value' => Usuario::roleLabel((string) ($usuario['role'] ?? Usuario::ROLE_CLIENTE))],
    [
        'label' => 'Status:',
        'value' => '<span class="badge ' . Usuario::accountStatusBadgeClass($usuario) . '">'
            . Usuario::accountStatusIcon($usuario) . ' '
            . e(Usuario::accountStatusLabel($usuario)) . '</span>',
        'html' => true,
    ],
    ['label' => 'Cadastro:', 'value' => date('d/m/Y H:i', strtotime($usuario['created_at']))],
]); ?>

<section class="admin-card">
    <header class="admin-card__header">
        <h2 class="admin-card__title">Resumo</h2>
    </header>
    <section class="admin-stats stats-grid stats-grid--admin-dashboard">
        <article class="stat-card">
            <span class="stat-card__value"><?= $resumo['total_indicacoes'] ?? 0 ?></span>
            <span class="stat-card__label">Total de Indicações</span>
        </article>
        <article class="stat-card">
            <span class="stat-card__value"><?= $resumo['pendentes'] ?? 0 ?></span>
            <span class="stat-card__label">Pendentes</span>
        </article>
        <article class="stat-card">
            <span class="stat-card__value"><?= $resumo['em_analise'] ?? 0 ?></span>
            <span class="stat-card__label">Em Análise</span>
        </article>
        <article class="stat-card">
            <span class="stat-card__value"><?= $resumo['aprovadas'] ?? 0 ?></span>
            <span class="stat-card__label">Aprovadas</span>
        </article>
        <article class="stat-card">
            <span class="stat-card__value"><?= $resumo['reprovadas'] ?? 0 ?></span>
            <span class="stat-card__label">Reprovadas</span>
        </article>
        <article class="stat-card">
            <span class="stat-card__value"><?= $resumo['cupons_gerados'] ?? 0 ?></span>
            <span class="stat-card__label">Cupons Gerados</span>
        </article>
    </section>
</section>

<?php if ($canBlock || $canUnblock): ?>
<section class="admin-card">
    <header class="admin-card__header">
        <h2 class="admin-card__title">Ações</h2>
    </header>
    <?php if ($canBlock): ?>
        <form method="POST" action="<?= url('/admin/usuarios/bloquear') ?>" class="form" onsubmit="return confirm('Tem certeza que deseja bloquear este usuário?');">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
            <button type="submit" class="btn btn--block btn--danger">Bloquear Usuário</button>
        </form>
    <?php endif; ?>
    <?php if ($canUnblock): ?>
        <form method="POST" action="<?= url('/admin/usuarios/desbloquear') ?>" class="form">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
            <button type="submit" class="btn btn--block btn--success">Desbloquear Usuário</button>
        </form>
    <?php endif; ?>
</section>
<?php endif; ?>

<section class="admin-card admin-dashboard-activities">
    <header class="admin-card__header">
        <h2 class="admin-card__title">Últimas Atividades</h2>
    </header>

    <?php if ($recentEvents === []): ?>
        <?php admin_empty_state('Nenhuma atividade registrada.'); ?>
    <?php else: ?>
        <div class="admin-dashboard-activities__scroll">
            <div class="activity-timeline">
                <?php foreach ($recentEvents as $event): ?>
                    <div class="activity-timeline__item">
                        <span class="activity-timeline__icon"><?= Evento::eventoIcon($event['evento']) ?></span>
                        <div class="activity-timeline__content">
                            <span class="activity-timeline__label"><?= e(Evento::eventoLabel($event['evento'])) ?></span>
                            <?php if (!empty($event['referencia'])): ?>
                                <span class="activity-timeline__user">Ref: <?= e($event['referencia']) ?></span>
                            <?php endif; ?>
                            <span class="activity-timeline__date"><?= e(date('d/m/Y H:i', strtotime($event['created_at']))) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</section>
