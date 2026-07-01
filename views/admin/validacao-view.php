<?php declare(strict_types=1); ?>
<?php
$subtitle = 'Consulte informações e histórico da validação.';
$status = (string) $validacao['status'];
$statusBadge = '<span class="badge ' . ValidacaoIndicacao::adminStatusBadgeClass($status) . '">'
    . ValidacaoIndicacao::adminStatusIcon($status) . ' '
    . e(ValidacaoIndicacao::adminStatusLabel($status)) . '</span>';
$codigoIndicacao = '—';
if ($indicacao !== null) {
    $codigoIndicacao = (string) ($indicacao['codigo_referencia'] ?? $indicacao['codigo_indicador'] ?? '—');
}
$indicacaoDate = $indicacao['created_at'] ?? $validacao['created_at'];
?>

<?php admin_detail_card('Dados da Indicação', [
    ['label' => 'Código:', 'value' => $codigoIndicacao],
    ['label' => 'Campanha:', 'value' => $campanhaNome],
    [
        'label' => 'Status:',
        'value' => $statusBadge,
        'html' => true,
    ],
    ['label' => 'Data da indicação:', 'value' => date('d/m/Y H:i', strtotime((string) $indicacaoDate))],
    ['label' => 'Validação ID:', 'value' => (string) $validacao['id']],
], $statusBadge); ?>

<?php admin_detail_card('Indicador', [
    ['label' => 'Nome:', 'value' => (string) ($indicador['nome'] ?? $validacao['usuario_nome'] ?? '—')],
    ['label' => 'CPF:', 'value' => $indicador !== null ? Usuario::formatCpfDisplay($indicador) : '—'],
    [
        'label' => 'WhatsApp:',
        'value' => !empty($indicador['whatsapp'])
            ? format_phone((string) $indicador['whatsapp'])
            : '—',
    ],
]); ?>

<?php
$indicadoNome = trim((string) ($validacao['nome_indicado'] ?? ''));
if ($indicadoNome === '' && $indicacao !== null) {
    $indicadoNome = trim((string) ($indicacao['nome_indicado'] ?? ''));
}
$indicadoWhatsapp = trim((string) ($validacao['telefone_indicado'] ?? ''));
if ($indicadoWhatsapp === '' && $indicacao !== null) {
    $indicadoWhatsapp = trim((string) ($indicacao['telefone_indicado'] ?? ''));
}
if ($indicadoWhatsapp === '' && $indicado !== null) {
    $indicadoWhatsapp = trim((string) ($indicado['whatsapp'] ?? ''));
}
?>
<?php admin_detail_card('Indicado', [
    ['label' => 'Nome:', 'value' => $indicadoNome !== '' ? $indicadoNome : '—'],
    ['label' => 'CPF:', 'value' => $indicado !== null ? Usuario::formatCpfDisplay($indicado) : '—'],
    [
        'label' => 'WhatsApp:',
        'value' => $indicadoWhatsapp !== '' ? format_phone($indicadoWhatsapp) : '—',
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
                    <span class="timeline__icon"><?= ValidacaoIndicacao::adminStatusIcon((string) $item['status_novo']) ?></span>
                    <div class="timeline__content">
                        <span class="timeline__label">
                            <?= e(ValidacaoIndicacao::adminStatusLabel((string) $item['status_anterior'])) ?> →
                            <?= e(ValidacaoIndicacao::adminStatusLabel((string) $item['status_novo'])) ?>
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

<?php admin_detail_card('Validação', [
    [
        'label' => 'Status:',
        'value' => $statusBadge,
        'html' => true,
    ],
    [
        'label' => 'Elegível:',
        'value' => ((int) ($validacao['elegivel'] ?? 0) === 1) ? 'Sim' : 'Não',
    ],
    [
        'label' => 'Motivo da rejeição:',
        'value' => !empty($validacao['motivo']) ? (string) $validacao['motivo'] : '—',
    ],
    ['label' => 'Criado em:', 'value' => date('d/m/Y H:i', strtotime($validacao['created_at']))],
    [
        'label' => 'Validado em:',
        'value' => !empty($validacao['validado_em'])
            ? date('d/m/Y H:i', strtotime($validacao['validado_em']))
            : '—',
    ],
]); ?>

<section class="admin-card">
    <header class="admin-card__header">
        <h2 class="admin-card__title">Cupom</h2>
    </header>

    <?php if ($cupom === null): ?>
        <?php admin_empty_state('Nenhum cupom gerado.'); ?>
    <?php else: ?>
        <?php admin_info_lines([
            ['label' => 'Código:', 'value' => (string) $cupom['codigo']],
            [
                'label' => 'Status:',
                'value' => Cupom::statusIcon((string) $cupom['status']) . ' ' . Cupom::statusLabel((string) $cupom['status']),
            ],
            ['label' => 'Data de geração:', 'value' => date('d/m/Y H:i', strtotime($cupom['created_at']))],
        ]); ?>
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
                        <?php if (!empty($event['description'])): ?>
                            <span class="timeline__description"><?= e((string) $event['description']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($event['admin'])): ?>
                            <span class="timeline__admin">Por: <?= e((string) $event['admin']) ?></span>
                        <?php endif; ?>
                        <span class="timeline__date"><?= e(date('d/m/Y H:i', strtotime($event['date']))) ?></span>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>

<?php if (ValidacaoIndicacao::canStartReview($validacao['status'])): ?>
    <section class="admin-card">
        <header class="admin-card__header">
            <h2 class="admin-card__title">Ações</h2>
        </header>
        <form method="POST" action="<?= url('/admin/validacoes/iniciar') ?>" class="form">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $validacao['id'] ?>">
            <button type="submit" class="btn btn--block btn--primary">Iniciar Análise</button>
        </form>
        <form method="POST" action="<?= url('/admin/validacoes/cancelar') ?>" class="form">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $validacao['id'] ?>">
            <div class="form-group">
                <label for="motivo_cancelar">Motivo do Cancelamento (opcional)</label>
                <textarea id="motivo_cancelar" name="motivo" rows="2"></textarea>
            </div>
            <button type="submit" class="btn btn--block btn--ghost" onsubmit="return confirm('Tem certeza que deseja cancelar esta validação?');">Cancelar Validação</button>
        </form>
    </section>
<?php elseif (ValidacaoIndicacao::canDecide($validacao['status'])): ?>
    <section class="admin-card">
        <header class="admin-card__header">
            <h2 class="admin-card__title">Ações</h2>
        </header>
        <form method="POST" action="<?= url('/admin/validacoes/aprovar') ?>" class="form">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $validacao['id'] ?>">
            <div class="form-group">
                <label for="observacao">Observação (opcional)</label>
                <textarea id="observacao" name="observacao" rows="2"></textarea>
            </div>
            <button type="submit" class="btn btn--block btn--success">Aprovar Validação</button>
        </form>
        <form method="POST" action="<?= url('/admin/validacoes/rejeitar') ?>" class="form">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $validacao['id'] ?>">
            <div class="form-group">
                <label for="motivo_rejeitar">Motivo da Rejeição *</label>
                <textarea id="motivo_rejeitar" name="motivo" rows="2" required></textarea>
            </div>
            <button type="submit" class="btn btn--block btn--danger" onsubmit="return confirm('Tem certeza que deseja rejeitar esta validação?');">Rejeitar Validação</button>
        </form>
        <form method="POST" action="<?= url('/admin/validacoes/cancelar') ?>" class="form">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $validacao['id'] ?>">
            <div class="form-group">
                <label for="motivo_cancelar">Motivo do Cancelamento (opcional)</label>
                <textarea id="motivo_cancelar" name="motivo" rows="2"></textarea>
            </div>
            <button type="submit" class="btn btn--block btn--ghost" onsubmit="return confirm('Tem certeza que deseja cancelar esta validação?');">Cancelar Validação</button>
        </form>
    </section>
<?php endif; ?>
