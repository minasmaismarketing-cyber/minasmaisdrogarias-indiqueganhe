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

$title = match ($type) {
    'aprovado' => 'Deu certo! Seu benefício foi liberado',
    'beneficio_pendente' => 'Sua indicação foi aprovada',
    'reprovado' => 'Não foi possível validar esta indicação',
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
    'aprovado' => 'Indicação aprovada, benefício liberado',
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
    <div class="indicacao-status-card__accent" aria-hidden="true"></div>

    <div class="indicacao-status-card__inner">
        <div class="indicacao-status-card__top">
            <span class="indicacao-status-card__badge" aria-hidden="true"><?= e($icon) ?></span>
            <div class="indicacao-status-card__heading">
                <?php if ($type === 'aprovado'): ?>
                    <span class="indicacao-status-card__seal indicacao-status-card__seal--ok">Indicação aprovada</span>
                <?php elseif ($type === 'beneficio_pendente'): ?>
                    <span class="indicacao-status-card__seal">Benefício pendente</span>
                <?php elseif ($type === 'reprovado'): ?>
                    <span class="indicacao-status-card__seal indicacao-status-card__seal--alert">Indicação não aprovada</span>
                <?php endif; ?>
                <h2 class="indicacao-status-card__title"><?= e($title) ?></h2>
            </div>
        </div>

        <div class="indicacao-status-card__content">
            <?php if ($type === 'aprovado'): ?>
                <p class="indicacao-status-card__body">
                    Seu cupom exclusivo de <span class="indicacao-status-card__off">10% OFF</span> já está disponível.
                </p>
                <a href="<?= url('/meus-cupons') ?>" class="btn btn--block btn--primary indicacao-status-card__cta">
                    Ver meu cupom
                </a>
            <?php elseif ($type === 'beneficio_pendente'): ?>
                <p class="indicacao-status-card__body">
                    Estamos preparando seu benefício. Ele será liberado em breve.
                </p>
            <?php elseif ($type === 'reprovado'): ?>
                <p class="indicacao-status-card__motivo"><?= e($motivo) ?></p>
                <button type="button" class="btn btn--block btn--outline" id="btn-dismiss-status-card">
                    Entendi
                </button>
            <?php endif; ?>
        </div>
    </div>
</section>
