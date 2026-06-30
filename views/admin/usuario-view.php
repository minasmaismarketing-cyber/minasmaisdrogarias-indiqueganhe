<?php declare(strict_types=1); ?>
<?php $subtitle = 'Consulte informações e atividades do usuário.'; ?>

<section class="mm-card mm-card--usuario-info">
    <div class="mm-card__header">
        <h2 class="mm-card__title">Informações do Usuário</h2>
    </div>

    <ul class="usuario-info-lines">
        <li class="usuario-info-lines__item">
            <span class="usuario-info-lines__label">Nome:</span>
            <span class="usuario-info-lines__value"><?= e($usuario['nome']) ?></span>
        </li>
        <li class="usuario-info-lines__item">
            <span class="usuario-info-lines__label">CPF:</span>
            <span class="usuario-info-lines__value"><?= e(Usuario::formatCpfDisplay($usuario)) ?></span>
        </li>
        <li class="usuario-info-lines__item">
            <span class="usuario-info-lines__label">E-mail:</span>
            <span class="usuario-info-lines__value"><?= e($usuario['email']) ?></span>
        </li>
        <li class="usuario-info-lines__item">
            <span class="usuario-info-lines__label">WhatsApp:</span>
            <span class="usuario-info-lines__value"><?= e($usuario['whatsapp'] ? format_phone((string) $usuario['whatsapp']) : '—') ?></span>
        </li>
        <li class="usuario-info-lines__item">
            <span class="usuario-info-lines__label">Código de Indicação:</span>
            <span class="usuario-info-lines__value"><?= e($usuario['codigo_indicador'] ?? '—') ?></span>
        </li>
        <li class="usuario-info-lines__item">
            <span class="usuario-info-lines__label">Perfil:</span>
            <span class="usuario-info-lines__value"><?= e(Usuario::roleLabel((string) ($usuario['role'] ?? Usuario::ROLE_CLIENTE))) ?></span>
        </li>
        <li class="usuario-info-lines__item">
            <span class="usuario-info-lines__label">Status:</span>
            <span class="usuario-info-lines__value">
                <span class="badge <?= Usuario::accountStatusBadgeClass($usuario) ?>">
                    <?= Usuario::accountStatusIcon($usuario) ?>
                    <?= e(Usuario::accountStatusLabel($usuario)) ?>
                </span>
            </span>
        </li>
        <li class="usuario-info-lines__item">
            <span class="usuario-info-lines__label">Cadastro:</span>
            <span class="usuario-info-lines__value"><?= e(date('d/m/Y H:i', strtotime($usuario['created_at']))) ?></span>
        </li>
    </ul>
</section>

<section class="mm-card">
    <div class="mm-card__header">
        <h2 class="mm-card__title">Resumo</h2>
    </div>
    <section class="stats-grid stats-grid--admin-dashboard">
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
<section class="mm-card">
    <div class="mm-card__header">
        <h2 class="mm-card__title">Ações</h2>
    </div>
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
