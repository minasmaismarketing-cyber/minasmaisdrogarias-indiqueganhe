<?php declare(strict_types=1); ?>
<?php $subtitle = 'Gerencie as indicações do sistema.'; ?>

<?php admin_table([
    'title' => 'Lista de Indicações',
    'emptyMessage' => 'Nenhuma indicação registrada.',
    'columns' => [
        ['label' => 'Indicado'],
        ['label' => 'Telefone'],
        ['label' => 'Código'],
        ['label' => 'Status'],
        ['label' => 'Data'],
    ],
    'rows' => $indicacoes,
], static function (array $indicacao): void { ?>
    <tr>
        <td class="admin-table__td admin-table__td--wrap admin-table__td--primary">
            <?= e($indicacao['nome_indicado'] ?? 'Aguardando cadastro') ?>
        </td>
        <td class="admin-table__td">
            <?= $indicacao['telefone_indicado'] ? e(format_phone((string) $indicacao['telefone_indicado'])) : '—' ?>
        </td>
        <td class="admin-table__td"><?= e($indicacao['codigo_indicador']) ?></td>
        <td class="admin-table__td">
            <span class="badge badge--status badge--<?= strtolower((string) $indicacao['status']) ?>">
                <?= e(Indicacao::statusLabel((string) $indicacao['status'])) ?>
            </span>
        </td>
        <td class="admin-table__td"><?= e(date('d/m/Y', strtotime($indicacao['created_at']))) ?></td>
    </tr>
<?php }); ?>
