<?php

declare(strict_types=1);

/**
 * Card de status da última indicação definitiva (parcial da Home).
 *
 * @var array<string, mixed>|null $statusCard
 */
if (empty($statusCard) || !is_array($statusCard)) {
    return;
}

$type = (string) ($statusCard['type'] ?? '');
$validacaoId = (int) ($statusCard['validacao_id'] ?? 0);
$indicacaoId = (int) ($statusCard['indicacao_id'] ?? 0);
$identifier = trim((string) ($statusCard['identifier'] ?? ''));
$title = trim((string) ($statusCard['title'] ?? ''));
$body = trim((string) ($statusCard['body'] ?? ''));
$motivo = trim((string) ($statusCard['motivo'] ?? ''));
$ctaUrl = trim((string) ($statusCard['cta_url'] ?? ''));
$ctaLabel = trim((string) ($statusCard['cta_label'] ?? ''));

if ($motivo === '') {
    $motivo = 'Não foi possível validar esta indicação.';
}
if ($ctaUrl === '') {
    $ctaUrl = url('/meus-cupons' . ($indicacaoId > 0 ? '?destaque=' . $indicacaoId : ''));
}

$isCelebration = $type === 'aprovado' || $type === 'beneficio_pendente';
$isAmigo = $type === 'amigo_aprovado';
$isPendingBenefit = $type === 'beneficio_pendente';

$modifier = match ($type) {
    'aprovado', 'amigo_aprovado' => 'success',
    'beneficio_pendente' => 'warning',
    'reprovado' => 'danger',
    default => 'info',
};

$ariaLabel = match ($type) {
    'aprovado' => 'Indicação aprovada, benefício liberado',
    'amigo_aprovado' => 'Mais um amigo aprovado',
    'beneficio_pendente' => 'Indicação aprovada, benefício pendente',
    'reprovado' => 'Indicação não aprovada',
    default => 'Status da indicação',
};
?>
<?php if ($isCelebration): ?>
<section
    class="indicacao-status-card indicacao-status-card--premium indicacao-status-card--<?= e($modifier) ?> indicacao-status-card--standalone"
    id="indicacao-status-card"
    data-status-type="<?= e($type) ?>"
    data-validacao-id="<?= $validacaoId ?>"
    data-indicacao-id="<?= $indicacaoId ?>"
    role="status"
    aria-label="<?= e($ariaLabel) ?>"
>
    <div class="indicacao-status-card__glow" aria-hidden="true"></div>

    <div class="indicacao-status-card__inner indicacao-status-card__inner--premium">
        <div class="indicacao-status-card__seal-row">
            <?php if ($isPendingBenefit): ?>
                <span class="indicacao-status-card__seal indicacao-status-card__seal--pending">
                    <svg class="indicacao-status-card__seal-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/>
                        <path d="M12 7v6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <circle cx="12" cy="16.5" r="1" fill="currentColor"/>
                    </svg>
                    Benefício pendente
                </span>
            <?php else: ?>
                <span class="indicacao-status-card__seal indicacao-status-card__seal--ok">
                    <svg class="indicacao-status-card__seal-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/>
                        <path d="M8 12.5l2.5 2.5L16 9.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Indicação aprovada
                </span>
            <?php endif; ?>
        </div>

        <div class="indicacao-status-card__hero">
            <div class="indicacao-status-card__copy">
                <p class="indicacao-status-card__headline">Deu certo!</p>
                <?php if ($identifier !== ''): ?>
                    <p class="indicacao-status-card__subline"><?= e($identifier) ?></p>
                <?php endif; ?>
                <?php if ($isPendingBenefit): ?>
                    <p class="indicacao-status-card__highlight">Benefício em breve</p>
                <?php else: ?>
                    <p class="indicacao-status-card__highlight">Benefício liberado</p>
                <?php endif; ?>
            </div>

            <div class="indicacao-status-card__offer" aria-hidden="true">
                <span class="indicacao-status-card__offer-icon">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M20 12V8H6a2 2 0 0 1-2-2 2 2 0 0 1 2-2h12v4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M4 6v12a2 2 0 0 0 2 2h14v-8H6a2 2 0 0 1-2-2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span class="indicacao-status-card__offer-off">10% OFF</span>
                <span class="indicacao-status-card__offer-label">Cupom exclusivo</span>
            </div>
        </div>

        <div class="indicacao-status-card__info-box">
            <span class="indicacao-status-card__info-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M20 12V8H6a2 2 0 0 1-2-2 2 2 0 0 1 2-2h12v4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M4 6v12a2 2 0 0 0 2 2h14v-8H6a2 2 0 0 1-2-2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
            <?php if ($isPendingBenefit): ?>
                <p class="indicacao-status-card__info-text">
                    Seu benefício será liberado em breve.
                </p>
            <?php else: ?>
                <p class="indicacao-status-card__info-text">
                    Seu cupom exclusivo de <span class="indicacao-status-card__off">10% OFF</span> já está disponível.
                </p>
            <?php endif; ?>
        </div>

        <?php if (!$isPendingBenefit): ?>
            <a href="<?= e($ctaUrl) ?>" class="btn btn--block btn--primary indicacao-status-card__cta">
                <span><?= e($ctaLabel !== '' ? $ctaLabel : 'Ver meu cupom') ?></span>
                <svg class="indicacao-status-card__cta-arrow" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
        <?php endif; ?>
    </div>
</section>
<?php elseif ($isAmigo): ?>
<section
    class="indicacao-status-card indicacao-status-card--premium indicacao-status-card--success indicacao-status-card--amigo indicacao-status-card--standalone"
    id="indicacao-status-card"
    data-status-type="<?= e($type) ?>"
    data-validacao-id="<?= $validacaoId ?>"
    data-indicacao-id="<?= $indicacaoId ?>"
    role="status"
    aria-label="<?= e($ariaLabel) ?>"
>
    <div class="indicacao-status-card__glow" aria-hidden="true"></div>
    <div class="indicacao-status-card__inner indicacao-status-card__inner--premium">
        <div class="indicacao-status-card__seal-row">
            <span class="indicacao-status-card__seal indicacao-status-card__seal--ok">Novo amigo</span>
        </div>
        <div class="indicacao-status-card__heading" style="margin-bottom:0.75rem;">
            <h2 class="indicacao-status-card__title"><?= e($title !== '' ? $title : 'Mais um amigo foi aprovado! 🎉') ?></h2>
        </div>
        <?php if ($identifier !== ''): ?>
            <p class="indicacao-status-card__identifier"><?= e($identifier) ?></p>
        <?php endif; ?>
        <p class="indicacao-status-card__motivo"><?= e($body) ?></p>
        <a href="<?= e($ctaUrl) ?>" class="btn btn--block btn--primary indicacao-status-card__cta">
            <span><?= e($ctaLabel !== '' ? $ctaLabel : 'Ver benefício do amigo') ?></span>
        </a>
    </div>
</section>
<?php else: ?>
<section
    class="indicacao-status-card indicacao-status-card--<?= e($modifier) ?> indicacao-status-card--standalone"
    id="indicacao-status-card"
    data-status-type="<?= e($type) ?>"
    data-validacao-id="<?= $validacaoId ?>"
    data-indicacao-id="<?= $indicacaoId ?>"
    role="status"
    aria-label="<?= e($ariaLabel) ?>"
>
    <div class="indicacao-status-card__accent" aria-hidden="true"></div>

    <div class="indicacao-status-card__inner">
        <div class="indicacao-status-card__top">
            <span class="indicacao-status-card__badge" aria-hidden="true">!</span>
            <div class="indicacao-status-card__heading">
                <span class="indicacao-status-card__seal indicacao-status-card__seal--alert">Indicação não aprovada</span>
                <h2 class="indicacao-status-card__title"><?= e($title !== '' ? $title : 'Uma indicação não foi aprovada') ?></h2>
            </div>
        </div>

        <div class="indicacao-status-card__content">
            <?php if ($identifier !== ''): ?>
                <p class="indicacao-status-card__identifier"><?= e($identifier) ?></p>
            <?php endif; ?>
            <p class="indicacao-status-card__motivo"><?= e($body !== '' ? $body : 'Não foi possível validar esta indicação.') ?></p>
            <?php if ($motivo !== '' && $motivo !== $body): ?>
                <p class="indicacao-status-card__motivo indicacao-status-card__motivo--detail"><?= e($motivo) ?></p>
            <?php endif; ?>
            <form
                method="POST"
                action="<?= url('/dashboard/status-card/dispensar') ?>"
                class="indicacao-status-card__dismiss-form"
                id="form-dismiss-status-card"
            >
                <?= csrf_field() ?>
                <input type="hidden" name="indicacao_id" value="<?= $indicacaoId ?>">
                <input type="hidden" name="validacao_id" value="<?= $validacaoId ?>">
                <button type="submit" class="btn btn--block btn--outline" id="btn-dismiss-status-card">
                    Entendi
                </button>
            </form>
        </div>
    </div>
</section>
<?php endif; ?>
