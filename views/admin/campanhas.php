<?php declare(strict_types=1); ?>
<?php $subtitle = 'Gerencie as campanhas de indicação.'; ?>

<?php admin_table([
    'title' => 'Lista de Campanhas',
    'headerActions' => '<a href="' . e(url('/admin/campanhas/criar')) . '" class="btn btn--sm btn--primary">Nova Campanha</a>',
    'emptyMessage' => 'Nenhuma campanha cadastrada.',
    'columns' => [
        ['label' => 'Nome'],
        ['label' => 'Período'],
        ['label' => 'Desconto'],
        ['label' => 'Status'],
        ['label' => 'Ações', 'class' => 'admin-table__col--actions', 'align' => 'right'],
    ],
    'rows' => $campanhas,
], static function (array $campanha): void { ?>
    <tr>
        <td class="admin-table__td admin-table__td--wrap admin-table__td--primary"><?= e($campanha['nome']) ?></td>
        <td class="admin-table__td">
            <?= e(date('d/m/Y', strtotime($campanha['inicio']))) ?> —
            <?= e(date('d/m/Y', strtotime($campanha['fim']))) ?>
        </td>
        <td class="admin-table__td">
            <?= e($campanha['desconto']) ?> <?= e(Campanha::tipoDescontoLabel($campanha['tipo_desconto'])) ?>
        </td>
        <td class="admin-table__td">
            <span class="badge badge--<?= strtolower($campanha['status']) ?>">
                <?= e(Campanha::statusLabel($campanha['status'])) ?>
            </span>
        </td>
        <td class="admin-table__td admin-table__col--actions">
            <div class="admin-table__actions">
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
        </td>
    </tr>
<?php }); ?>
