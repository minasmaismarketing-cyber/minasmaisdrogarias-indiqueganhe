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
$validacaoId = (int) ($statusCard['validacao_id'] ?? 0);
$motivo = trim((string) ($statusCard['motivo'] ?? ''));

$title = match ($type) {
    'aprovado', 'beneficio_pendente' => 'Sua indicação foi aprovada!',
    'reprovado' => 'Sua indicação não foi aprovada',
    default => (string) ($statusCard['title'] ?? 'Status da indicação'),
};

$modifier = match ($type) {
    'aprovado' => 'success',
    'beneficio_pendente' => 'warning',
    'reprovado' => 'danger',
    default => 'info',
};

$icon = match ($type) {
    'aprovado' => '✓',
    'beneficio_pendente' => '⏳',
    'reprovado' => '!',
    default => 'i',
};

$ariaLabel = match ($type) {
    'aprovado' => 'Indicação aprovada',
    'beneficio_pendente' => 'Indicação aprovada, benefício pendente',
    'reprovado' => 'Indicação não aprovada',
    default => 'Status da indicação',
};
?>
<section
    class="indicacao-status-card indicacao-status-card--<?= e($modifier) ?> indicacao-status-card--standalone"
    id="indicacao-status-card"
    data-status-type="<?= e($type) ?>"
    data-validacao-id="<?= $validacaoId ?>"
    role="status"
    aria-label="<?= e($ariaLabel) ?>"
>
    <div class="indicacao-status-card__top">
        <span class="indicacao-status-card__badge" aria-hidden="true"><?= e($icon) ?></span>
        <div class="indicacao-status-card__heading">
            <h2 class="indicacao-status-card__title"><?= e($title) ?></h2>
            <?php if ($type === 'beneficio_pendente'): ?>
                <span class="indicacao-status-card__seal">Benefício pendente</span>
            <?php elseif ($type === 'aprovado'): ?>
                <span class="indicacao-status-card__seal indicacao-status-card__seal--ok">Aprovado</span>
            <?php elseif ($type === 'reprovado'): ?>
                <span class="indicacao-status-card__seal indicacao-status-card__seal--alert">Não aprovada</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="indicacao-status-card__content">
        <?php if ($type === 'aprovado'): ?>
            <p class="indicacao-status-card__body">
                Seu benefício de <span class="indicacao-status-card__off">10% OFF</span> já está disponível.
            </p>
            <a href="<?= url('/meus-cupons') ?>" class="btn btn--block btn--primary indicacao-status-card__cta">
                Ver meu cupom
            </a>
        <?php elseif ($type === 'beneficio_pendente'): ?>
            <p class="indicacao-status-card__body">
                Seu benefício será liberado em breve.
            </p>
        <?php elseif ($type === 'reprovado'): ?>
            <?php if ($motivo !== ''): ?>
                <p class="indicacao-status-card__motivo">
                    <span class="indicacao-status-card__motivo-label">Motivo:</span>
                    <?= e($motivo) ?>
                </p>
            <?php endif; ?>
            <button type="button" class="btn btn--block btn--outline" id="btn-dismiss-status-card">
                Entendi
            </button>
        <?php endif; ?>
    </div>
</section>
