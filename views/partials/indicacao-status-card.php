<?php

declare(strict_types=1);

/**
 * Card de status da última indicação definitiva (parcial da Home).
 *
 * @var array{type: string, title: string, body: string, motivo?: string, validacao_id: int}|null $statusCard
 */
if (empty($statusCard) || !is_array($statusCard)) {
    return;
}

$type = (string) ($statusCard['type'] ?? '');
$title = (string) ($statusCard['title'] ?? '');
$body = (string) ($statusCard['body'] ?? '');
$motivo = (string) ($statusCard['motivo'] ?? '');
$validacaoId = (int) ($statusCard['validacao_id'] ?? 0);

$modifier = match ($type) {
    'aprovado' => 'success',
    'beneficio_pendente' => 'warning',
    'reprovado' => 'danger',
    default => 'info',
};

$icon = match ($type) {
    'aprovado' => '🎉',
    'beneficio_pendente' => '⏳',
    'reprovado' => '✕',
    default => 'ℹ️',
};

$ariaLabel = match ($type) {
    'aprovado' => 'Indicação aprovada',
    'beneficio_pendente' => 'Benefício pendente',
    'reprovado' => 'Indicação não aprovada',
    default => 'Status da indicação',
};
?>
<section
    class="indicacao-status-card indicacao-status-card--<?= e($modifier) ?>"
    id="indicacao-status-card"
    data-status-type="<?= e($type) ?>"
    data-validacao-id="<?= $validacaoId ?>"
    role="status"
    aria-label="<?= e($ariaLabel) ?>"
>
    <div class="indicacao-status-card__icon" aria-hidden="true"><?= $icon ?></div>
    <div class="indicacao-status-card__content">
        <h2 class="indicacao-status-card__title"><?= e($title) ?></h2>
        <?php if ($body !== ''): ?>
            <p class="indicacao-status-card__body"><?= e($body) ?></p>
        <?php endif; ?>
        <?php if ($type === 'reprovado' && $motivo !== ''): ?>
            <p class="indicacao-status-card__motivo">
                <span class="indicacao-status-card__motivo-label">Motivo:</span>
                <?= e($motivo) ?>
            </p>
        <?php endif; ?>
        <?php if ($type === 'aprovado'): ?>
            <a href="<?= url('/meus-cupons') ?>" class="btn btn--block btn--primary indicacao-status-card__cta">
                Ver meu cupom
            </a>
        <?php elseif ($type === 'reprovado'): ?>
            <button type="button" class="btn btn--block btn--outline" id="btn-dismiss-status-card">
                Entendi
            </button>
        <?php endif; ?>
    </div>
</section>
