<?php

declare(strict_types=1);
?>
<section class="page-hero animate-slide">
    <h1 class="page-hero__title">Minhas indicações</h1>
    <p class="page-hero__subtitle">Acompanhe o progresso das suas indicações.</p>
</section>

<section class="mm-card">
    <div class="mm-card__header">
        <h2 class="mm-card__title">Filtros</h2>
    </div>
    
    <div class="filters-bar">
        <form method="GET" action="<?= url('/indicacoes') ?>" class="filters-form">
            <div class="filters-form__group">
                <select name="filtro" class="filters-form__select">
                    <option value="todas" <?= ($filtro ?? 'todas') === 'todas' ? 'selected' : '' ?>>Todas</option>
                    <option value="pendentes" <?= ($filtro ?? '') === 'pendentes' ? 'selected' : '' ?>>Pendentes</option>
                    <option value="validadas" <?= ($filtro ?? '') === 'validadas' ? 'selected' : '' ?>>Validadas</option>
                    <option value="premiadas" <?= ($filtro ?? '') === 'premiadas' ? 'selected' : '' ?>>Premiadas</option>
                </select>
            </div>
            
            <div class="filters-form__group">
                <input type="text" name="busca" placeholder="Buscar por nome ou telefone" value="<?= e($busca ?? '') ?>" class="filters-form__input">
            </div>
            
            <button type="submit" class="btn btn--primary">Filtrar</button>
            <a href="<?= url('/indicacoes') ?>" class="btn btn--ghost">Limpar</a>
        </form>
    </div>
</section>


<section class="indicacoes-list">
    <?php if ($indicacoes === [] && $indicados === []): ?>
        <div class="mm-card">
            <p class="mm-card__placeholder">Nenhuma indicação registrada ainda.</p>
        </div>
    <?php else: ?>
        <?php 
        // Use indicados if available, otherwise fall back to indicacoes
        $items = $indicados !== [] ? $indicados : $indicacoes;
        foreach ($items as $item): 
            $isIndicado = isset($item['status']) && in_array($item['status'], [
                Indicado::STATUS_LINK_ACESSADO,
                Indicado::STATUS_CADASTRO_INICIADO,
                Indicado::STATUS_CADASTRO_CONCLUIDO,
                Indicado::STATUS_AGUARDANDO_VALIDACAO,
                Indicado::STATUS_VALIDADO,
                Indicado::STATUS_INVALIDADO,
            ]);
            $status = $isIndicado ? $item['status'] : ($item['status'] ?? 'AGUARDANDO');
            $nome = $isIndicado ? Indicado::maskName($item['nome']) : ($item['nome_indicado'] ?? 'Aguardando cadastro');
            $telefone = $isIndicado ? $item['telefone'] : ($item['telefone_indicado'] ?? '');
            $timeline = $item['timeline'] ?? [];
            $created = $item['created_at'] ?? '';
        ?>
            <article class="referral-card">
                <div class="referral-card__header">
                    <span class="referral-card__icon"><?= $isIndicado ? '👤' : Indicacao::statusIcon((string) $status) ?></span>
                    <div class="referral-card__info">
                        <h3 class="referral-card__name"><?= e($nome) ?></h3>
                        <?php if (!empty($telefone)): ?>
                            <p class="referral-card__phone"><?= e(format_phone((string) $telefone)) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if (!empty($timeline)): ?>
                    <div class="referral-card__timeline">
                        <h4 class="timeline-title">Progresso</h4>
                        <div class="timeline">
                            <?php foreach ($timeline as $step): ?>
                                <div class="timeline__item timeline__item--<?= $step['status'] ?>">
                                    <span class="timeline__icon"><?= $step['icon'] ?></span>
                                    <div class="timeline__content">
                                        <span class="timeline__label"><?= e($step['label']) ?></span>
                                        <span class="timeline__date"><?= e(date('d/m/Y H:i', strtotime($step['date']))) ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="referral-card__footer">
                    <span class="badge badge--status badge--<?= strtolower((string) $status) ?>">
                        <?= e($isIndicado ? Indicado::statusLabel((string) $status) : Indicacao::statusLabel((string) $status)) ?>
                    </span>
                    <?php if ($isIndicado && $status === Indicado::STATUS_INVALIDADO && !empty($item['motivo'])): ?>
                        <span class="referral-card__motivo" title="<?= e($item['motivo']) ?>">
                            <?= e(substr($item['motivo'], 0, 50)) ?><?= strlen($item['motivo']) > 50 ? '...' : '' ?>
                        </span>
                    <?php endif; ?>
                    <span class="referral-card__date"><?= e(date('d/m/Y', strtotime((string) $created))) ?></span>
                </div>
            </article>
        <?php endforeach; ?>
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
