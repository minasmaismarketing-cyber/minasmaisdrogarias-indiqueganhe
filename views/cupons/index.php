<?php declare(strict_types=1); ?>
<?php require BASE_PATH . '/views/partials/alerts.php'; ?>

<div class="cupons-stack">
    <header class="cupons-page-header animate-slide">
        <a
            href="<?= url('/dashboard') ?>"
            class="cupons-page-header__back"
            aria-label="Voltar"
        >
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M15 6l-6 6 6 6" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </a>
        <div class="cupons-page-header__copy">
            <h1 class="cupons-page-header__title">Meus Cupons</h1>
            <p class="cupons-page-header__subtitle">Seus benefícios de indicação</p>
        </div>
        <span class="cupons-page-header__decor" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none">
                <path d="M12 7v14M7.5 11h9" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                <path d="M6 11h12v8.5a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 6 19.5V11z" stroke="currentColor" stroke-width="1.75"/>
                <path d="M8.5 11c0-2.2 1.3-4 3.5-4s3.5 1.8 3.5 4" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
            </svg>
        </span>
    </header>

    <?php if ($cupons === []): ?>
        <section class="coupons-list" aria-label="Seu benefício">
            <div class="mm-card">
                <div class="mm-empty-state">
                    <span class="mm-empty-state__icon" aria-hidden="true">%</span>
                    <p class="mm-empty-state__title">Você ainda não possui cupons</p>
                    <p class="mm-empty-state__text">Indique amigos e ganhe cupons quando suas indicações forem aprovadas.</p>
                    <a href="<?= url('/dashboard') ?>" class="btn btn--block btn--primary">Ir para o painel</a>
                </div>
            </div>
        </section>
    <?php else: ?>
        <section class="cupons-section" aria-labelledby="seu-beneficio-heading">
            <h2 id="seu-beneficio-heading" class="cupons-section__label cupons-section__label--green">
                <svg class="cupons-section__label-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/>
                    <path d="M8 12.5l2.5 2.5L16 9.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Seu benefício
            </h2>

            <div class="coupons-list">
                <?php foreach ($cupons as $cupom): ?>
                    <?php
                    $status = (string) ($cupom['status'] ?? '');
                    $isDisponivel = $status === Cupom::STATUS_DISPONIVEL;
                    $isUtilizado = $status === Cupom::STATUS_UTILIZADO;
                    $valor = (float) ($cupom['valor'] ?? 0);
                    $isPercent = ($cupom['tipo'] ?? '') === Cupom::TIPO_PERCENTUAL;
                    $offLabel = $isPercent
                        ? rtrim(rtrim(number_format($valor, 2, ',', ''), '0'), ',') . '% OFF'
                        : 'R$ ' . number_format($valor, 2, ',', '.');
                    $utilizadoEm = !empty($cupom['utilizado_em'])
                        ? date('d/m/Y H:i', strtotime((string) $cupom['utilizado_em']))
                        : null;
                    $codigo = (string) ($cupom['codigo'] ?? '');
                    ?>
                    <article
                        class="coupon-card coupon-card--premium coupon-card--<?= e(strtolower($status)) ?>"
                        aria-label="<?= $isUtilizado ? 'Cupom utilizado' : 'Cupom disponível' ?>"
                    >
                        <div class="coupon-card__accent" aria-hidden="true"></div>
                        <div class="coupon-card__inner">
                            <div class="coupon-card__body">
                                <div class="coupon-card__main">
                                    <?php if ($isUtilizado): ?>
                                        <span class="coupon-card__seal coupon-card__seal--used">
                                            <svg class="coupon-card__seal-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/>
                                                <path d="M8 12.5l2.5 2.5L16 9.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            Cupom utilizado
                                        </span>
                                    <?php else: ?>
                                        <span class="coupon-card__seal coupon-card__seal--ok">
                                            <svg class="coupon-card__seal-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/>
                                                <path d="M8 12.5l2.5 2.5L16 9.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            Benefício liberado
                                        </span>
                                    <?php endif; ?>

                                    <h3 class="coupon-card__title">Seu cupom exclusivo</h3>
                                    <p class="coupon-card__off" aria-label="<?= e($offLabel) ?>"><?= e($offLabel) ?></p>
                                    <p class="coupon-card__hint-text">Use em qualquer compra no App Minas Mais.</p>
                                </div>

                                <div class="coupon-card__art" aria-hidden="true">
                                    <svg viewBox="0 0 64 64" fill="none">
                                        <rect x="10" y="22" width="44" height="28" rx="6" stroke="currentColor" stroke-width="2.5"/>
                                        <path d="M10 34h44" stroke="currentColor" stroke-width="2.5"/>
                                        <circle cx="32" cy="34" r="5" fill="currentColor" opacity="0.15"/>
                                        <path d="M29 34h6M32 31v6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        <path d="M20 22c0-6 5-10 12-10s12 4 12 10" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                                    </svg>
                                </div>
                            </div>

                            <?php if ($isDisponivel && $codigo !== ''): ?>
                                <div class="coupon-card__actions">
                                    <button
                                        type="button"
                                        class="btn btn--block btn--primary coupon-card__copy"
                                        data-copy="<?= e($codigo) ?>"
                                        data-copy-success="Cupom copiado"
                                        data-copy-toast="Cupom copiado com sucesso!"
                                        aria-label="Copiar meu cupom"
                                    >
                                        Copiar meu cupom
                                    </button>
                                    <p class="coupon-card__warning" role="note">
                                        <svg class="coupon-card__warning-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M12 3l8 4v5c0 5-3.4 8.4-8 9.5C7.4 20.4 4 17 4 12V7l8-4z" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/>
                                            <path d="M9.5 12.2l1.8 1.8 3.4-3.6" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        <span>Uso único e intransferível. Não compartilhe este código.</span>
                                    </p>
                                </div>
                            <?php elseif ($isUtilizado): ?>
                                <div class="coupon-card__used" role="status">
                                    <?php if ($utilizadoEm !== null): ?>
                                        <p class="coupon-card__used-date">Utilizado em: <?= e($utilizadoEm) ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="coupon-card__status-line" role="status">
                                    <?= Cupom::statusIcon($status) ?>
                                    <?= e(Cupom::statusLabel($status)) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if (!empty($beneficioShares)): ?>
        <section class="cupons-section" aria-labelledby="beneficio-amigo-heading">
            <h2 id="beneficio-amigo-heading" class="cupons-section__label cupons-section__label--coral">
                <svg class="cupons-section__label-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M12 7v14M7.5 11h9" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                    <path d="M6 11h12v8.5a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 6 19.5V11z" stroke="currentColor" stroke-width="1.75"/>
                    <path d="M8.5 11c0-2.2 1.3-4 3.5-4s3.5 1.8 3.5 4" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                </svg>
                Benefícios dos amigos
            </h2>

            <div class="beneficio-amigo-list" aria-label="Benefícios de 5% dos amigos indicados">
                <?php foreach ($beneficioShares as $share): ?>
                    <?php
                    $shareId = (int) ($share['indicacao_id'] ?? 0);
                    $isNovo = !empty($share['is_novo']);
                    $isDestaque = !empty($share['is_destaque']) || $isNovo;
                    $phoneOk = !empty($share['phone_ok']);
                    $identifier = (string) ($share['identifier'] ?? 'Indicação aprovada');
                    $phoneTail = (string) ($share['phone_tail'] ?? '');
                    $sharedAt = $share['shared_at'] ?? null;
                    $sharedLabel = '';
                    if (!empty($sharedAt) && strtotime((string) $sharedAt) !== false) {
                        $sharedLabel = 'Compartilhamento iniciado em ' . date('d/m/Y', strtotime((string) $sharedAt));
                    }
                    $cardId = 'beneficio-amigo-' . $shareId;
                    ?>
                    <article
                        id="<?= e($cardId) ?>"
                        class="beneficio-amigo-card<?= $isDestaque ? ' beneficio-amigo-card--novo' : '' ?>"
                        <?= $isDestaque ? 'data-highlight="1"' : '' ?>
                    >
                        <div class="beneficio-amigo-card__accent" aria-hidden="true"></div>
                        <div class="beneficio-amigo-card__inner">
                            <div class="beneficio-amigo-card__body">
                                <div class="beneficio-amigo-card__main">
                                    <?php if ($isNovo): ?>
                                        <span class="beneficio-amigo-card__novo-seal">Novo</span>
                                    <?php endif; ?>
                                    <span class="beneficio-amigo-card__off-badge" aria-hidden="true">5% OFF</span>
                                    <h3 class="beneficio-amigo-card__title"><?= e($identifier) ?></h3>
                                    <p class="beneficio-amigo-card__text">
                                        Indicação aprovada. Seu amigo ganhou 5% OFF na primeira compra
                                        (válido por 30 dias após a liberação).
                                    </p>
                                </div>
                                <div class="beneficio-amigo-card__art" aria-hidden="true">
                                    <svg viewBox="0 0 64 64" fill="none">
                                        <path d="M32 14l3.2 8.4H44l-6.8 5 2.6 8.6L32 30.8 24.2 36l2.6-8.6L20 22.4h8.8L32 14z" fill="currentColor" opacity="0.18"/>
                                        <path d="M18 40h28v12a4 4 0 0 1-4 4H22a4 4 0 0 1-4-4V40z" stroke="currentColor" stroke-width="2.5"/>
                                        <path d="M18 40h28l-2.5-8H20.5L18 40z" stroke="currentColor" stroke-width="2.5" stroke-linejoin="round"/>
                                        <path d="M32 32v24" stroke="currentColor" stroke-width="2.5"/>
                                    </svg>
                                </div>
                            </div>

                            <div class="beneficio-amigo-card__actions">
                                <?php if ($phoneOk): ?>
                                    <form method="POST" action="<?= url('/indicacoes/' . $shareId . '/compartilhar-beneficio') ?>">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn--block beneficio-amigo-card__whatsapp">
                                            <svg class="beneficio-amigo-card__wa-icon" viewBox="0 0 24 24" aria-hidden="true">
                                                <path fill="currentColor" d="M19.05 4.91A9.82 9.82 0 0 0 12.04 2C6.58 2 2.14 6.44 2.14 11.9c0 1.75.46 3.45 1.32 4.95L2 22l5.3-1.38c1.45.79 3.08 1.21 4.74 1.21h.01c5.46 0 9.9-4.44 9.9-9.9 0-2.65-1.03-5.14-2.9-7.02zm-7.01 15.24h-.01a8.2 8.2 0 0 1-4.18-1.15l-.3-.18-3.14.82.84-3.06-.2-.31a8.2 8.2 0 0 1-1.26-4.37c0-4.54 3.7-8.24 8.25-8.24 2.2 0 4.27.86 5.82 2.42a8.18 8.18 0 0 1 2.41 5.83c.02 4.54-3.68 8.24-8.23 8.24zm4.52-6.16c-.25-.12-1.47-.72-1.7-.81-.23-.08-.4-.12-.56.12-.17.25-.64.8-.79.97-.14.17-.3.19-.55.06-.25-.12-1.05-.39-2-1.23-.74-.66-1.23-1.47-1.38-1.72-.14-.25-.02-.38.11-.51.11-.11.25-.3.37-.44.12-.15.17-.25.25-.41.08-.17.04-.31-.02-.43-.06-.12-.56-1.35-.77-1.84-.2-.49-.41-.42-.56-.43h-.48c-.17 0-.43.06-.66.31-.22.25-.86.85-.86 2.07s.88 2.4 1 2.56c.12.17 1.75 2.67 4.25 3.74.59.26 1.05.41 1.41.52.59.19 1.13.16 1.56.1.48-.07 1.47-.6 1.67-1.18.21-.58.21-1.07.14-1.18-.06-.11-.22-.17-.47-.29z"/>
                                            </svg>
                                            Avisar meu amigo pelo WhatsApp
                                        </button>
                                    </form>
                                    <?php if ($sharedLabel !== ''): ?>
                                        <p class="beneficio-amigo-card__shared"><?= e($sharedLabel) ?></p>
                                    <?php endif; ?>
                                    <?php if ($phoneTail !== ''): ?>
                                        <p class="beneficio-amigo-card__hint">
                                            WhatsApp final <?= e($phoneTail) ?>
                                        </p>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <button type="button" class="btn btn--block btn--outline" disabled>
                                        Avisar meu amigo pelo WhatsApp
                                    </button>
                                    <p class="beneficio-amigo-card__hint">Telefone indisponível para envio.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</div>

<?php if (!empty($destaqueId)): ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const el = document.getElementById('beneficio-amigo-<?= (int) $destaqueId ?>');
    if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
});
</script>
<?php endif; ?>
