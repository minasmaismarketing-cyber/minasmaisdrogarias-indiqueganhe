<?php declare(strict_types=1); ?>
<?php
$subtitle = 'Consulte informações e histórico do cupom.';
$status = (string) $cupom['status'];
$statusBadge = '<span class="badge ' . Cupom::adminStatusBadgeClass($status) . '">'
    . Cupom::statusIcon($status) . ' '
    . e(Cupom::statusLabel($status)) . '</span>';
$codigoIndicacao = (string) ($cupom['codigo_referencia'] ?? $cupom['codigo_indicador'] ?? '—');
$indicacaoStatus = (string) ($cupom['indicacao_status'] ?? '');
?>

<?php admin_detail_card('Dados do Cupom', [
    ['label' => 'Código:', 'value' => (string) $cupom['codigo'], 'highlight' => true],
    [
        'label' => 'Status:',
        'value' => $statusBadge,
        'html' => true,
    ],
    ['label' => 'Origem:', 'value' => Cupom::origemLabel($cupom['origem'] ?? null)],
    ['label' => 'Campanha:', 'value' => (string) ($cupom['campanha_nome'] ?? '—')],
    ['label' => 'Data de geração:', 'value' => date('d/m/Y H:i', strtotime($cupom['created_at']))],
    [
        'label' => 'Data de utilização:',
        'value' => !empty($cupom['utilizado_em'])
            ? date('d/m/Y H:i', strtotime($cupom['utilizado_em']))
            : '—',
    ],
], $statusBadge); ?>

<?php admin_detail_card('Indicador', [
    ['label' => 'Nome:', 'value' => (string) ($cupom['usuario_nome'] ?? '—')],
    ['label' => 'CPF:', 'value' => Usuario::formatCpfDisplay(['cpf' => $cupom['usuario_cpf'] ?? ''])],
    [
        'label' => 'WhatsApp:',
        'value' => !empty($cupom['usuario_whatsapp'])
            ? format_phone((string) $cupom['usuario_whatsapp'])
            : '—',
    ],
]); ?>

<?php admin_detail_card('Indicação', [
    ['label' => 'Código:', 'value' => $codigoIndicacao],
    [
        'label' => 'Status:',
        'value' => $indicacaoStatus !== ''
            ? Indicacao::statusIcon($indicacaoStatus) . ' ' . Indicacao::statusLabel($indicacaoStatus)
            : '—',
    ],
    [
        'label' => 'Data:',
        'value' => !empty($cupom['indicacao_created_at'])
            ? date('d/m/Y H:i', strtotime($cupom['indicacao_created_at']))
            : '—',
    ],
]); ?>

<section class="admin-card">
    <header class="admin-card__header">
        <h2 class="admin-card__title">Histórico</h2>
    </header>

    <?php if ($history === []): ?>
        <?php admin_empty_state('Nenhuma alteração registrada.'); ?>
    <?php else: ?>
        <ul class="timeline">
            <?php foreach ($history as $item): ?>
                <li class="timeline__item">
                    <span class="timeline__icon"><?= Cupom::statusIcon((string) $item['status_novo']) ?></span>
                    <div class="timeline__content">
                        <span class="timeline__label">
                            <?= e(Cupom::statusLabel((string) $item['status_anterior'])) ?> →
                            <?= e(Cupom::statusLabel((string) $item['status_novo'])) ?>
                        </span>
                        <?php if ($item['descricao']): ?>
                            <span class="timeline__description"><?= e($item['descricao']) ?></span>
                        <?php endif; ?>
                        <?php if ($item['usuario_admin']): ?>
                            <span class="timeline__admin">Por: <?= e($item['usuario_admin']) ?></span>
                        <?php endif; ?>
                        <span class="timeline__date"><?= e(date('d/m/Y H:i', strtotime($item['created_at']))) ?></span>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>

<?php if ($cupom['status'] === Cupom::STATUS_DISPONIVEL || $cupom['status'] === Cupom::STATUS_RESERVADO): ?>
    <section class="admin-card">
        <header class="admin-card__header">
            <h2 class="admin-card__title">Ações</h2>
        </header>
        <form method="POST" action="<?= url('/admin/cupons/cancelar') ?>" class="form">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $cupom['id'] ?>">
            <div class="form-group">
                <label for="motivo">Motivo do Cancelamento (opcional)</label>
                <textarea id="motivo" name="motivo" rows="2"></textarea>
            </div>
            <button type="submit" class="btn btn--block btn--danger" onsubmit="return confirm('Tem certeza que deseja cancelar este cupom?');">Cancelar</button>
        </form>
        <?php if ($cupom['status'] === Cupom::STATUS_DISPONIVEL): ?>
            <form method="POST" action="<?= url('/admin/cupons/expirar') ?>" class="form">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= $cupom['id'] ?>">
                <button type="submit" class="btn btn--block btn--ghost" onsubmit="return confirm('Tem certeza que deseja expirar este cupom?');">Expirar</button>
            </form>
        <?php endif; ?>
    </section>
<?php elseif ($cupom['status'] === Cupom::STATUS_CANCELADO || $cupom['status'] === Cupom::STATUS_EXPIRADO): ?>
    <section class="admin-card">
        <header class="admin-card__header">
            <h2 class="admin-card__title">Ações</h2>
        </header>
        <form method="POST" action="<?= url('/admin/cupons/reativar') ?>" class="form">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $cupom['id'] ?>">
            <button type="submit" class="btn btn--block btn--success">Reativar</button>
        </form>
    </section>
<?php endif; ?>
