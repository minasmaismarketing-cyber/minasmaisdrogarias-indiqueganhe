<?php declare(strict_types=1); ?>
<?php $subtitle = 'Gerencie as indicações do sistema.'; ?>

<section class="mm-card">
    <div class="mm-card__header">
        <h2 class="mm-card__title">Lista de Indicações</h2>
    </div>

    <?php if ($indicacoes === []): ?>
        <p class="mm-card__placeholder">Nenhuma indicação registrada.</p>
    <?php else: ?>
        <ul class="admin-list">
            <?php foreach ($indicacoes as $indicacao): ?>
                <li class="admin-list__item">
                    <div class="admin-list__info">
                        <strong><?= e($indicacao['nome_indicado'] ?? 'Aguardando cadastro') ?></strong>
                        <?php if ($indicacao['telefone_indicado']): ?>
                            <span class="admin-list__meta"><?= e(format_phone((string) $indicacao['telefone_indicado'])) ?></span>
                        <?php endif; ?>
                        <span class="admin-list__meta">Código: <?= e($indicacao['codigo_indicador']) ?></span>
                    </div>
                    <div class="admin-list__actions">
                        <span class="badge badge--status badge--<?= strtolower((string) $indicacao['status']) ?>">
                            <?= e(Indicacao::statusLabel((string) $indicacao['status'])) ?>
                        </span>
                        <span class="admin-list__meta"><?= e(date('d/m/Y', strtotime($indicacao['created_at']))) ?></span>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
