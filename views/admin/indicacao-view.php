<?php declare(strict_types=1); ?>
<?php
$subtitle = 'Consulte informações e histórico da indicação.';
$adminStatus = (string) ($indicacao['admin_status'] ?? Indicacao::resolveAdminStatus($indicacao));
$statusBadge = '<span class="badge badge--' . strtolower($adminStatus) . '">'
    . ValidacaoIndicacao::statusIcon($adminStatus) . ' '
    . e(Indicacao::adminStatusLabel($adminStatus)) . '</span>';
$codigoIndicacao = (string) ($indicacao['codigo_referencia'] ?? $indicacao['codigo_indicador'] ?? '—');
?>

<?php admin_detail_card('Dados da Indicação', [
    ['label' => 'Código:', 'value' => $codigoIndicacao],
    [
        'label' => 'Status:',
        'value' => $statusBadge,
        'html' => true,
    ],
    ['label' => 'Data da criação:', 'value' => date('d/m/Y H:i', strtotime($indicacao['created_at']))],
    ['label' => 'Data da validação:', 'value' => $validadoEm ? date('d/m/Y H:i', strtotime($validadoEm)) : '—'],
], $statusBadge); ?>

<?php admin_detail_card('Indicador', [
    ['label' => 'Nome:', 'value' => (string) ($indicacao['indicador_nome'] ?? '—')],
    ['label' => 'CPF:', 'value' => Usuario::formatCpfDisplay(['cpf' => $indicacao['indicador_cpf'] ?? ''])],
    [
        'label' => 'WhatsApp:',
        'value' => !empty($indicacao['indicador_whatsapp'])
            ? format_phone((string) $indicacao['indicador_whatsapp'])
            : '—',
    ],
]); ?>

<?php admin_detail_card('Indicado', [
    ['label' => 'Nome:', 'value' => (string) ($indicacao['indicado_nome'] ?? 'Aguardando cadastro')],
    ['label' => 'CPF:', 'value' => Usuario::formatCpfDisplay(['cpf' => $indicacao['indicado_cpf'] ?? ''])],
    [
        'label' => 'WhatsApp:',
        'value' => !empty($indicacao['indicado_whatsapp'])
            ? format_phone((string) $indicacao['indicado_whatsapp'])
            : '—',
    ],
]); ?>

<?php
$validacaoLines = [
    [
        'label' => 'Status:',
        'value' => $validacao !== null
            ? ValidacaoIndicacao::statusIcon((string) $validacao['status']) . ' ' . ValidacaoIndicacao::statusLabel((string) $validacao['status'])
            : Indicacao::adminStatusLabel($adminStatus),
    ],
    [
        'label' => 'Elegível:',
        'value' => $validacao !== null
            ? (((int) ($validacao['elegivel'] ?? 0) === 1) ? 'Sim' : 'Não')
            : '—',
    ],
    [
        'label' => 'Motivo da rejeição:',
        'value' => ($validacao !== null && !empty($validacao['motivo_bloqueio']))
            ? (string) $validacao['motivo_bloqueio']
            : '—',
    ],
];
admin_detail_card('Validação', $validacaoLines);
?>

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
