<?php declare(strict_types=1);

$logo = brand_logo_url();
$nome = (string) ($indicadorPrimeiroNome ?? '');
$link = (string) ($appDownloadUrl ?? '#');
$linkDisabled = $link === '#';

$indicadorUsuarioId = '';
$smartScriptPayload = ['enabled' => false, 'scriptUrl' => ''];

if (!empty($valid) && !empty($ref)) {
    $indicadorRow = (new Usuario())->findByCodigo((string) $ref);
    $indicadorUsuarioId = $indicadorRow ? (string) ($indicadorRow['id'] ?? '') : '';
    $smartScriptPayload = convite_smart_script_payload((string) $ref, $indicadorUsuarioId);
}

?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('css/landing-convite.css') ?>">

<div class="lc-page">
    <div class="lc-curve" aria-hidden="true"></div>

    <?php if (empty($valid)): ?>
        <section class="lc-shell">
            <article class="lc-card lc-card--error">
                <header class="lc-header">
                    <img class="lc-logo" src="<?= e($logo) ?>" alt="Minas Mais Drogaria e Perfumaria" width="220" height="56" loading="eager">
                </header>
                <h1 class="lc-hero-title lc-hero-title--error">Convite indisponível</h1>
                <p class="lc-error-text"><?= e((string) ($error ?? 'Link de convite inválido.')) ?></p>
                <a href="<?= url('/') ?>" class="lc-cta lc-cta--secondary">Voltar ao início</a>
            </article>
        </section>
    <?php else: ?>
        <section class="lc-shell">
            <article class="lc-card" data-lc-card>
                <header class="lc-header">
                    <img class="lc-logo" src="<?= e($logo) ?>" alt="Minas Mais Drogaria e Perfumaria" width="220" height="56" loading="eager">
                    <div class="lc-badge">
                        <svg class="lc-icon lc-icon--badge" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <rect x="3" y="8" width="18" height="4" rx="1" stroke="currentColor" stroke-width="1.8"/>
                            <path d="M12 8v13M7.5 8C7.5 5.5 9 4 12 4s4.5 1.5 4.5 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            <path d="M12 8V4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                        <span>Você foi indicado!</span>
                    </div>
                </header>

                <div class="lc-hero">
                    <?php if ($nome !== ''): ?>
                        <h1 class="lc-hero-title">
                            <span class="lc-hero-name"><?= e($nome) ?></span>
                            <span class="lc-hero-line">quer dividir um</span>
                            <span class="lc-hero-line">benefício com você!</span>
                        </h1>
                    <?php else: ?>
                        <h1 class="lc-hero-title">
                            <span class="lc-hero-line">Alguém especial quer dividir um</span>
                            <span class="lc-hero-line">benefício com você!</span>
                        </h1>
                    <?php endif; ?>
                </div>

                <section class="lc-section" aria-labelledby="lc-benefits-title">
                    <div class="lc-divider lc-divider--neutral">
                        <span class="lc-divider-line" aria-hidden="true"></span>
                        <h2 id="lc-benefits-title" class="lc-section-label">Você ganha</h2>
                        <span class="lc-divider-line" aria-hidden="true"></span>
                    </div>

                    <div class="lc-benefits-grid">
                        <article class="lc-benefit-card" data-lc-animate>
                            <div class="lc-benefit-icon-wrap">
                                <svg class="lc-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    <circle cx="7" cy="7" r="1.5" fill="currentColor"/>
                                </svg>
                            </div>
                            <h3 class="lc-benefit-title">5% OFF</h3>
                            <p class="lc-benefit-desc">na primeira compra</p>
                        </article>

                        <article class="lc-benefit-card" data-lc-animate>
                            <div class="lc-benefit-icon-wrap">
                                <svg class="lc-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M3 7h11v8H3zM14 10h4l3 3v2h-7V10z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    <circle cx="7.5" cy="17" r="1.5" stroke="currentColor" stroke-width="1.8"/>
                                    <circle cx="17.5" cy="17" r="1.5" stroke="currentColor" stroke-width="1.8"/>
                                </svg>
                            </div>
                            <h3 class="lc-benefit-title">Entrega rápida</h3>
                            <p class="lc-benefit-desc">em até 30 minutos</p>
                        </article>

                        <article class="lc-benefit-card" data-lc-animate>
                            <div class="lc-benefit-icon-wrap">
                                <svg class="lc-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M12 2l2.4 7.4H22l-6 4.6 2.3 7L12 16.8 5.7 21l2.3-7-6-4.6h7.6L12 2z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <h3 class="lc-benefit-title">Promoções exclusivas</h3>
                            <p class="lc-benefit-desc">no aplicativo</p>
                        </article>
                    </div>
                </section>

                <section class="lc-section" aria-labelledby="lc-steps-title">
                    <div class="lc-divider lc-divider--accent">
                        <span class="lc-divider-line" aria-hidden="true"></span>
                        <h2 id="lc-steps-title" class="lc-section-label lc-section-label--accent">Como funciona?</h2>
                        <span class="lc-divider-line" aria-hidden="true"></span>
                    </div>

                    <div class="lc-stepper">
                        <article class="lc-step" data-lc-animate>
                            <div class="lc-step-track">
                                <span class="lc-step-number">1</span>
                                <span class="lc-step-line" aria-hidden="true"></span>
                            </div>
                            <div class="lc-step-body">
                                <div class="lc-step-icon-box">
                                    <svg class="lc-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M12 3v10m0 0 4-4m-4 4-4-4M5 19h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <div class="lc-step-copy">
                                    <h3 class="lc-step-title">Baixe o aplicativo</h3>
                                    <p class="lc-step-desc">Clique no botão abaixo para baixar o app.</p>
                                </div>
                            </div>
                        </article>

                        <article class="lc-step" data-lc-animate>
                            <div class="lc-step-track">
                                <span class="lc-step-number">2</span>
                                <span class="lc-step-line" aria-hidden="true"></span>
                            </div>
                            <div class="lc-step-body">
                                <div class="lc-step-icon-box">
                                    <svg class="lc-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M20 21a8 8 0 10-16 0M12 11a4 4 0 100-8 4 4 0 000 8z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <div class="lc-step-copy">
                                    <h3 class="lc-step-title"><strong>Faça seu cadastro</strong></h3>
                                    <p class="lc-step-desc">Cadastre-se utilizando seus dados.</p>
                                </div>
                            </div>
                        </article>

                        <article class="lc-step" data-lc-animate>
                            <div class="lc-step-track">
                                <span class="lc-step-number">3</span>
                            </div>
                            <div class="lc-step-body">
                                <div class="lc-step-icon-box">
                                    <svg class="lc-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M20 12v8H4v-8M12 3v18M7.5 7.5 12 3l4.5 4.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        <rect x="3" y="8" width="18" height="4" rx="1" stroke="currentColor" stroke-width="1.8"/>
                                    </svg>
                                </div>
                                <div class="lc-step-copy">
                                    <h3 class="lc-step-title">Seu desconto será liberado automaticamente</h3>
                                    <p class="lc-step-desc">Finalize seu cadastro e aproveite seu benefício.</p>
                                </div>
                            </div>
                        </article>
                    </div>
                </section>

                <a
                    id="convite-download-btn"
                    href="<?= e($link) ?>"
                    class="lc-cta"
                    data-fallback-url="<?= e($link) ?>"
                    <?= $linkDisabled ? 'aria-disabled="true"' : 'target="_blank" rel="noopener noreferrer"' ?>
                >
                    <svg class="lc-icon lc-icon--cta" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M12 5v10m0 0 4-4m-4 4 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>BAIXAR O APP</span>
                </a>

                <footer class="lc-footer">
                    <svg class="lc-icon lc-icon--footer" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M12 3l7 4v5c0 4.5-3.1 8.7-7 9-3.9-.3-7-4.5-7-9V7l7-4z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                    </svg>
                    <span>Oferta válida para novos cadastros realizados através desta indicação.</span>
                </footer>
            </article>
        </section>

        <script>
            window.__CONVITE_AF__ = <?= json_encode($smartScriptPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        </script>
        <script src="<?= asset('js/landing-convite.js') ?>" defer></script>
    <?php endif; ?>
</div>
