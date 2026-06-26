<?php

declare(strict_types=1);
?>
<section class="page-hero animate-slide">
    <h1 class="page-hero__title">Minhas indicações</h1>
</section>

<section class="mm-card">
    <div class="mm-card__header">
        <h2 class="mm-card__title">Histórico de Participação</h2>
        <p class="mm-card__subtitle">Acompanhe o status das suas indicações validadas.</p>
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
                    <li class="validacao-list-compact__item">
                        <div class="validacao-list-compact__row">
                            <div class="validacao-list-compact__status">
                                <span class="validacao-status-badge validacao-status-badge--<?= strtolower($v['status']) ?>">
                                    <?= e(ValidacaoIndicacao::statusLabel($v['status'])) ?>
                                </span>
                            </div>
                            <div class="validacao-list-compact__info">
                                <?php if (!empty($v['nome_indicado'])): ?>
                                    <strong><?= e($v['nome_indicado']) ?></strong>
                                <?php else: ?>
                                    <strong>Aguardando cadastro</strong>
                                <?php endif; ?>
                                <span class="validacao-list-compact__date">
                                    <?= e(date('d/m/Y', strtotime($v['created_at']))) ?>
                                </span>
                            </div>
                        </div>
                        <?php if (!empty($v['motivo_bloqueio'])): ?>
                            <p class="validacao-list-compact__motivo">
                                ⚠️ <?= e(ValidacaoIndicacao::motivoLabel($v['motivo_bloqueio'])) ?>
                            </p>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php else: ?>
        <p class="mm-card__placeholder">Nenhuma indicação registrada ainda.</p>
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
