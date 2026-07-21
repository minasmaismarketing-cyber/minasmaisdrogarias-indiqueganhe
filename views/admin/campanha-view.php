<?php declare(strict_types=1); ?>
<?php
$subtitle = 'Consulte informações e desempenho da campanha.';
$status = (string) $campanha['status'];
$statusBadge = '<span class="badge ' . Campanha::adminStatusBadgeClass($status) . '">'
    . Campanha::adminStatusIcon($status) . ' '
    . e(Campanha::adminStatusLabel($status)) . '</span>';
$regulamento = trim((string) ($campanha['descricao'] ?? ''));
if ($regulamento === '') {
    $regulamento = trim((string) ($campanha['texto_landing'] ?? ''));
}
?>

<?php admin_detail_card('Dados da Campanha', [
    ['label' => 'Nome:', 'value' => (string) $campanha['nome']],
    [
        'label' => 'Status:',
        'value' => $statusBadge,
        'html' => true,
    ],
    ['label' => 'Vigência:', 'value' => Campanha::formatVigencia($campanha)],
    ['label' => 'Regulamento:', 'value' => $regulamento !== '' ? $regulamento : '—'],
    ['label' => 'Data de criação:', 'value' => date('d/m/Y H:i', strtotime($campanha['created_at']))],
], $statusBadge); ?>

<section class="admin-card">
    <header class="admin-card__header">
        <h2 class="admin-card__title">Estatísticas</h2>
    </header>
    <section class="admin-stats stats-grid stats-grid--admin-dashboard">
        <article class="stat-card">
            <span class="stat-card__value"><?= (int) ($stats['indicacoes'] ?? 0) ?></span>
            <span class="stat-card__label">Indicações</span>
        </article>
        <article class="stat-card">
            <span class="stat-card__value"><?= (int) ($stats['validacoes'] ?? 0) ?></span>
            <span class="stat-card__label">Validações</span>
        </article>
        <article class="stat-card">
            <span class="stat-card__value"><?= (int) ($stats['cupons_gerados'] ?? 0) ?></span>
            <span class="stat-card__label">Cupons Gerados</span>
        </article>
        <article class="stat-card">
            <span class="stat-card__value"><?= (int) ($stats['cupons_utilizados'] ?? 0) ?></span>
            <span class="stat-card__label">Cupons Utilizados</span>
        </article>
        <article class="stat-card">
            <span class="stat-card__value"><?= number_format((float) ($stats['taxa_conversao'] ?? 0), 2, ',', '.') ?>%</span>
            <span class="stat-card__label">Taxa de Conversão</span>
        </article>
    </section>
</section>

<section class="admin-card admin-card--table">
    <header class="admin-card__header">
        <h2 class="admin-card__title">Cupons</h2>
        <div class="admin-card__header-end">
            <span class="admin-card__meta">Últimos gerados</span>
        </div>
    </header>

    <?php if ($recentCupons === []): ?>
        <?php admin_empty_state('Nenhum cupom gerado para esta campanha.'); ?>
    <?php else: ?>
        <div class="admin-table__scroll">
            <table class="admin-table admin-table--zebra">
                <thead>
                    <tr>
                        <th class="admin-table__th">Código</th>
                        <th class="admin-table__th">Indicador</th>
                        <th class="admin-table__th">Status</th>
                        <th class="admin-table__th">Gerado em</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentCupons as $cupom): ?>
                        <tr>
                            <td class="admin-table__td admin-table__td--primary">
                                <a href="<?= url('/admin/cupons/' . $cupom['id']) ?>"><?= e(mask_cupom_codigo((string) ($cupom['codigo'] ?? ''))) ?></a>
                            </td>
                            <td class="admin-table__td"><?= e((string) ($cupom['usuario_nome'] ?? '—')) ?></td>
                            <td class="admin-table__td">
                                <span class="badge <?= e(Cupom::adminStatusBadgeClass((string) $cupom['status'])) ?>">
                                    <?= Cupom::statusIcon((string) $cupom['status']) ?>
                                    <?= e(Cupom::statusLabel((string) $cupom['status'])) ?>
                                </span>
                            </td>
                            <td class="admin-table__td"><?= e(date('d/m/Y H:i', strtotime($cupom['created_at']))) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<section class="admin-card">
    <header class="admin-card__header">
        <h2 class="admin-card__title">Timeline</h2>
    </header>

    <?php if ($timeline === []): ?>
        <?php admin_empty_state('Nenhum evento registrado.'); ?>
    <?php else: ?>
        <ul class="timeline">
            <?php foreach ($timeline as $event): ?>
                <li class="timeline__item">
                    <span class="timeline__icon"><?= e((string) $event['icon']) ?></span>
                    <div class="timeline__content">
                        <span class="timeline__label"><?= e((string) $event['label']) ?></span>
                        <span class="timeline__date"><?= e(date('d/m/Y H:i', strtotime($event['date']))) ?></span>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>

<section class="admin-card">
    <header class="admin-card__header">
        <h2 class="admin-card__title">Ações</h2>
    </header>
    <div class="admin-table__actions">
        <a href="<?= url('/admin/campanhas/editar/' . $campanha['id']) ?>" class="btn btn--ghost">Editar</a>
        <form method="POST" action="<?= url('/admin/campanhas/duplicar/' . $campanha['id']) ?>" class="inline-form">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn--ghost">Duplicar</button>
        </form>
        <?php if ($campanha['status'] === Campanha::STATUS_ATIVA): ?>
            <form method="POST" action="<?= url('/admin/campanhas/desativar/' . $campanha['id']) ?>" class="inline-form">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn--ghost">Desativar</button>
            </form>
        <?php else: ?>
            <form method="POST" action="<?= url('/admin/campanhas/ativar/' . $campanha['id']) ?>" class="inline-form">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn--primary">Ativar</button>
            </form>
        <?php endif; ?>
        <form method="POST" action="<?= url('/admin/campanhas/excluir/' . $campanha['id']) ?>" class="inline-form" onsubmit="return confirm('Tem certeza que deseja excluir esta campanha?');">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn--danger">Excluir</button>
        </form>
    </div>
</section>
