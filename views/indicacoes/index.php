<?php

declare(strict_types=1);
?>
<?php require BASE_PATH . '/views/partials/alerts.php'; ?>
<section class="page-hero animate-slide">
    <h1 class="page-hero__title">Minhas indicações</h1>
    <p class="page-hero__subtitle">Acompanhe o status de cada indicação vinculada ao seu código.</p>
</section>

<section class="mm-card">
    <div class="mm-card__header mm-card__header--stack">
        <h2 class="mm-card__title">Histórico de Participação</h2>
        <p class="mm-card__subtitle">Uma linha por indicação, da mais recente para a mais antiga.</p>
    </div>

    <?php if (($validacaoStats['total'] ?? 0) > 0): ?>
        <div class="validacao-stats-compact">
            <div class="validacao-stat-compact">
                <span class="validacao-stat-compact__value"><?= (int) $validacaoStats['total'] ?></span>
                <span class="validacao-stat-compact__label">Total</span>
            </div>
            <div class="validacao-stat-compact validacao-stat-compact--success">
                <span class="validacao-stat-compact__value"><?= (int) $validacaoStats['aprovados'] ?></span>
                <span class="validacao-stat-compact__label">Aprovados</span>
            </div>
            <div class="validacao-stat-compact validacao-stat-compact--error">
                <span class="validacao-stat-compact__value"><?= (int) $validacaoStats['reprovados'] ?></span>
                <span class="validacao-stat-compact__label">Reprovados</span>
            </div>
            <div class="validacao-stat-compact validacao-stat-compact--success">
                <span class="validacao-stat-compact__value"><?= (int) $validacaoStats['beneficios_liberados'] ?></span>
                <span class="validacao-stat-compact__label">Benefícios</span>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($historicoValidacoes)): ?>
        <div class="validacao-historico">
            <h3 class="validacao-historico__title">Suas indicações</h3>
            <ul class="validacao-list-compact">
                <?php foreach ($historicoValidacoes as $v): ?>
                    <?php
                    $vStatus = (string) ($v['status'] ?? '');
                    $iStatus = (string) ($v['indicacao_status'] ?? '');
                    $indicacaoId = (int) ($v['indicacao_id'] ?? 0);
                    $telefoneRaw = (string) ($v['telefone_indicado'] ?? '');
                    $hasIdentity = trim((string) ($v['telefone_indicado'] ?? '')) !== ''
                        || trim((string) ($v['email_indicado'] ?? '')) !== ''
                        || trim((string) ($v['cpf_indicado'] ?? '')) !== '';
                    $identifier = $hasIdentity
                        ? indicacao_display_identifier($v, (string) ($v['indicacao_created_at'] ?? $v['created_at'] ?? ''))
                        : 'Aguardando o cadastro do amigo';
                    $createdAt = (string) ($v['indicacao_created_at'] ?? $v['created_at'] ?? '');
                    $dateLabel = $createdAt !== '' && strtotime($createdAt) !== false
                        ? date('d/m/Y', strtotime($createdAt)) . ' às ' . date('H:i', strtotime($createdAt))
                        : '';
                    $motivoCode = (string) ($v['motivo_bloqueio'] ?? '');
                    $statusSummary = match (true) {
                        $vStatus === ValidacaoIndicacao::STATUS_BENEFICIO_LIBERADO,
                        $vStatus === ValidacaoIndicacao::STATUS_APROVADO => 'Indicação aprovada',
                        $vStatus === ValidacaoIndicacao::STATUS_REPROVADO => indicacao_friendly_reject_reason($motivoCode),
                        $vStatus === ValidacaoIndicacao::STATUS_EM_ANALISE,
                        $vStatus === ValidacaoIndicacao::STATUS_AGUARDANDO_VALIDACAO => 'Aguardando validação',
                        default => 'Aguardando o cadastro do amigo',
                    };
                    $badgeLabel = match (true) {
                        $vStatus === ValidacaoIndicacao::STATUS_BENEFICIO_LIBERADO,
                        $vStatus === ValidacaoIndicacao::STATUS_APROVADO => 'Aprovada',
                        $vStatus === ValidacaoIndicacao::STATUS_REPROVADO => 'Reprovada',
                        $vStatus === ValidacaoIndicacao::STATUS_EM_ANALISE,
                        $vStatus === ValidacaoIndicacao::STATUS_AGUARDANDO_VALIDACAO => 'Aguardando validação',
                        default => 'Aguardando',
                    };
                    $podeCompartilhar = $indicacaoId > 0
                        && is_indicacao_aprovada_para_beneficio($vStatus, $iStatus)
                        && normalize_brazilian_whatsapp_number($telefoneRaw) !== null;
                    $phoneTail = $podeCompartilhar ? mask_phone_for_display($telefoneRaw) : '';
                    ?>
                    <li class="validacao-list-compact__item">
                        <div class="validacao-list-compact__row">
                            <div class="validacao-list-compact__status">
                                <span class="validacao-status-badge validacao-status-badge--<?= e(strtolower($vStatus !== '' ? $vStatus : 'pendente')) ?>">
                                    <?= e($badgeLabel) ?>
                                </span>
                            </div>
                            <div class="validacao-list-compact__info">
                                <strong><?= e($identifier) ?></strong>
                                <?php if ($dateLabel !== ''): ?>
                                    <span class="validacao-list-compact__date"><?= e($dateLabel) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <p class="validacao-list-compact__motivo">
                            <?= e($statusSummary) ?>
                        </p>
                        <?php if ($podeCompartilhar): ?>
                            <div class="validacao-list-compact__share">
                                <form method="POST" action="<?= url('/indicacoes/' . $indicacaoId . '/compartilhar-beneficio') ?>">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn--block btn--outline btn--sm">
                                        Avisar meu amigo pelo WhatsApp
                                    </button>
                                </form>
                                <p class="validacao-list-compact__share-hint">WhatsApp final <?= e($phoneTail) ?></p>
                            </div>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php else: ?>
        <div class="mm-empty-state">
            <span class="mm-empty-state__icon" aria-hidden="true">+</span>
            <p class="mm-empty-state__title">Nenhuma indicação ainda</p>
            <p class="mm-empty-state__text">Compartilhe seu código no Dashboard e acompanhe aqui quando suas indicações forem validadas.</p>
            <a href="<?= url('/dashboard') ?>" class="btn btn--block btn--primary">Ir para Dashboard</a>
        </div>
    <?php endif; ?>
</section>

<?php if ($totalPages > 1): ?>
    <nav class="pagination" aria-label="Paginação">
        <?php if ($hasPrev): ?>
            <a href="<?= url('/indicacoes?page=' . ($currentPage - 1)) ?>" class="pagination__link pagination__link--prev">
                Anterior
            </a>
        <?php endif; ?>

        <span class="pagination__info">Página <?= $currentPage ?> de <?= $totalPages ?></span>

        <?php if ($hasNext): ?>
            <a href="<?= url('/indicacoes?page=' . ($currentPage + 1)) ?>" class="pagination__link pagination__link--next">
                Próxima
            </a>
        <?php endif; ?>
    </nav>
<?php endif; ?>
