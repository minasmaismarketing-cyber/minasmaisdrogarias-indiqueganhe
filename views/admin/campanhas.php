<?php declare(strict_types=1); ?>
<?php $subtitle = 'Gerencie as campanhas de indicação.'; ?>

<section class="mm-card">
    <div class="mm-card__header">
        <h2 class="mm-card__title">Lista de Campanhas</h2>
        <a href="<?= url('/admin/campanhas/criar') ?>" class="btn btn--sm btn--primary">Nova Campanha</a>
    </div>

    <?php if ($campanhas === []): ?>
        <p class="mm-card__placeholder">Nenhuma campanha cadastrada.</p>
    <?php else: ?>
        <ul class="admin-list">
            <?php foreach ($campanhas as $campanha): ?>
                <li class="admin-list__item">
                    <div class="admin-list__info">
                        <strong><?= e($campanha['nome']) ?></strong>
                        <span class="admin-list__meta">
                            <?= e(date('d/m/Y', strtotime($campanha['inicio']))) ?> a 
                            <?= e(date('d/m/Y', strtotime($campanha['fim']))) ?>
                        </span>
                        <span class="admin-list__meta">
                            Desconto: <?= e($campanha['desconto']) ?> 
                            <?= e(Campanha::tipoDescontoLabel($campanha['tipo_desconto'])) ?>
                        </span>
                    </div>
                    <div class="admin-list__actions">
                        <span class="badge badge--<?= strtolower($campanha['status']) ?>">
                            <?= e(Campanha::statusLabel($campanha['status'])) ?>
                        </span>
                        <a href="<?= url('/admin/campanhas/editar/' . $campanha['id']) ?>" class="btn btn--sm btn--ghost">Editar</a>
                        <form method="POST" action="<?= url('/admin/campanhas/duplicar/' . $campanha['id']) ?>" class="inline-form">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn--sm btn--ghost">Duplicar</button>
                        </form>
                        <?php if ($campanha['status'] === Campanha::STATUS_ATIVA): ?>
                            <form method="POST" action="<?= url('/admin/campanhas/desativar/' . $campanha['id']) ?>" class="inline-form">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn--sm btn--ghost">Desativar</button>
                            </form>
                        <?php else: ?>
                            <form method="POST" action="<?= url('/admin/campanhas/ativar/' . $campanha['id']) ?>" class="inline-form">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn--sm btn--primary">Ativar</button>
                            </form>
                        <?php endif; ?>
                        <form method="POST" action="<?= url('/admin/campanhas/excluir/' . $campanha['id']) ?>" class="inline-form" onsubmit="return confirm('Tem certeza que deseja excluir esta campanha?');">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn--sm btn--danger">Excluir</button>
                        </form>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
