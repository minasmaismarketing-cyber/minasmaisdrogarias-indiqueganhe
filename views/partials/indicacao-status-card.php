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
$motivoRaw = trim((string) ($statusCard['motivo'] ?? ''));

$motivo = match ($motivoRaw) {
    'CPF_JA_PARTICIPOU', 'JA_PARTICIPOU', 'CPF já participou', 'CPF já utilizado anteriormente.' =>
        'Este CPF já participou da campanha.',
    'CPF_JA_CADASTRADO', 'CPF_EXISTENTE', 'USUARIO_JA_CADASTRADO', 'CPF já cadastrado', 'Usuário já participou da campanha.' =>
        'Este CPF já possui cadastro.',
    'EMAIL_JA_CADASTRADO', 'E-mail já cadastrado', 'E-mail já utilizado nesta campanha.' =>
        'Este e-mail já possui cadastro.',
    'TELEFONE_JA_CADASTRADO', 'Telefone já cadastrado', 'Telefone já utilizado nesta campanha.' =>
        'Este telefone já possui cadastro.',
    'AUTOINDICACAO', 'AUTO_INDICACAO', 'Não é permitido indicar a si mesmo.' =>
        'Não é permitido indicar a si mesmo.',
    'CAMPANHA_INATIVA', 'CAMPANHA_EXPIRADA', 'Campanha inativa no momento da indicação.', 'Campanha expirada no momento da indicação.' =>
        'A campanha está encerrada.',
    default => ($motivoRaw !== '' && !preg_match('/^[A-Z0-9_]+$/', $motivoRaw))
        ? $motivoRaw
        : ($motivoRaw !== '' ? ValidacaoIndicacao::motivoLabel($motivoRaw) : 'Não foi possível validar esta indicação.'),
};

$isCelebration = $type === 'aprovado' || $type === 'beneficio_pendente';
$isPendingBenefit = $type === 'beneficio_pendente';

$modifier = match ($type) {
    'aprovado' => 'success',
    'beneficio_pendente' => 'warning',
    'reprovado' => 'danger',
    default => 'info',
};

$ariaLabel = match ($type) {
    'aprovado' => 'Indicação aprovada, benefício liberado',
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
                <?php if ($isPendingBenefit): ?>
                    <p class="indicacao-status-card__subline">Seu benefício será</p>
                    <p class="indicacao-status-card__highlight">liberado em breve</p>
                <?php else: ?>
                    <p class="indicacao-status-card__subline">Seu benefício foi</p>
                    <p class="indicacao-status-card__highlight">liberado</p>
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
            <a href="<?= url('/meus-cupons') ?>" class="btn btn--block btn--primary indicacao-status-card__cta">
                <span>Ver meu cupom</span>
                <svg class="indicacao-status-card__cta-arrow" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
        <?php endif; ?>
    </div>
</section>
<?php else: ?>
<section
    class="indicacao-status-card indicacao-status-card--<?= e($modifier) ?> indicacao-status-card--standalone"
    id="indicacao-status-card"
    data-status-type="<?= e($type) ?>"
    data-validacao-id="<?= $validacaoId ?>"
    role="status"
    aria-label="<?= e($ariaLabel) ?>"
>
    <div class="indicacao-status-card__accent" aria-hidden="true"></div>

    <div class="indicacao-status-card__inner">
        <div class="indicacao-status-card__top">
            <span class="indicacao-status-card__badge" aria-hidden="true">!</span>
            <div class="indicacao-status-card__heading">
                <span class="indicacao-status-card__seal indicacao-status-card__seal--alert">Indicação não aprovada</span>
                <h2 class="indicacao-status-card__title">Não foi possível validar esta indicação</h2>
            </div>
        </div>

        <div class="indicacao-status-card__content">
            <p class="indicacao-status-card__motivo"><?= e($motivo) ?></p>
            <button type="button" class="btn btn--block btn--outline" id="btn-dismiss-status-card">
                Entendi
            </button>
        </div>
    </div>
</section>
<?php endif; ?>
