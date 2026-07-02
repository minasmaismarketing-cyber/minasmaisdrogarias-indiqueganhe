<?php declare(strict_types=1);

$loginUrl = url('/login');
$cadastroUrl = url('/cadastro');
$dashboardUrl = url('/dashboard');
$logoUrl = asset('images/Logo - Drogaria e Perfumaria.png');
$banner1Url = asset('images/banner1.webp');
$banner2Url = asset('images/banner2.webp');
$isGuest = !auth_check();
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('css/home-landing.css') ?>">

<main class="mm-home" id="topo">
    <section class="mm-home__hero" aria-labelledby="home-title">
        <div class="mm-home__hero-bg" aria-hidden="true">
            <img src="<?= e($banner1Url) ?>" alt="" loading="eager">
        </div>

        <div class="mm-home__container mm-home__hero-inner">
            <header class="mm-home__header" aria-label="Cabeçalho">
                <a class="mm-home__brand" href="<?= e(url('/')) ?>" aria-label="Drogarias Minas Mais">
                    <img src="<?= e($logoUrl) ?>" alt="Drogarias Minas Mais" width="240" height="80">
                </a>

                <nav class="mm-home__nav" aria-label="Acesso">
                    <?php if ($isGuest): ?>
                        <a class="mm-home__nav-link" href="<?= e($loginUrl) ?>">Entrar</a>
                        <a class="mm-home__nav-button" href="<?= e($cadastroUrl) ?>">Cadastrar</a>
                    <?php else: ?>
                        <a class="mm-home__nav-button" href="<?= e($dashboardUrl) ?>">Meu painel</a>
                    <?php endif; ?>
                </nav>
            </header>

            <div class="mm-home__hero-content">
                <div class="mm-home__eyebrow">
                    <span>Programa</span>
                    <strong>Indique e Ganhe</strong>
                </div>

                <h1 class="mm-home__title" id="home-title">
                    Indique amigos<br>
                    e ganhe <span>benefícios!</span>
                </h1>

                <p class="mm-home__lead">
                    Na <strong>Minas Mais</strong>, cuidar da sua saúde fica ainda melhor quando você indica
                    e seus amigos também ganham.
                </p>

                <div class="mm-home__actions" aria-label="Ações principais">
                    <?php if ($isGuest): ?>
                        <a class="mm-home__btn mm-home__btn--primary" href="<?= e($cadastroUrl) ?>" id="home-btn-participar">
                            <span>Quero participar</span>
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M16 11c1.66 0 3-1.57 3-3.5S17.66 4 16 4s-3 1.57-3 3.5S14.34 11 16 11Zm-8 0c1.66 0 3-1.57 3-3.5S9.66 4 8 4 5 5.57 5 7.5 6.34 11 8 11Zm0 2c-2.67 0-8 1.34-8 4v2h10v-2c0-1.04.4-2.02 1.1-2.86C9.94 13.43 8.74 13 8 13Zm8 0c-.74 0-1.94.43-3.1 1.14A3.73 3.73 0 0 0 11.8 17v2H24v-2c0-2.66-5.33-4-8-4Z"/>
                            </svg>
                        </a>

                        <a class="mm-home__btn mm-home__btn--outline" href="<?= e($loginUrl) ?>" id="home-btn-login">
                            <span>Já tenho conta</span>
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M10 17v-3H3v-4h7V7l5 5-5 5Zm-8 5h9v-2H2V4h9V2H2C.9 2 0 2.9 0 4v16c0 1.1.9 2 2 2Zm13.8-4.4 1.4 1.4L24 12l-6.8-7-1.4 1.4L20.1 11H8v2h12.1l-4.3 4.6Z"/>
                            </svg>
                        </a>
                    <?php else: ?>
                        <a class="mm-home__btn mm-home__btn--primary" href="<?= e($dashboardUrl) ?>" id="home-btn-participar">
                            <span>Ir para meu painel</span>
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M10 17v-3H3v-4h7V7l5 5-5 5Zm-8 5h9v-2H2V4h9V2H2C.9 2 0 2.9 0 4v16c0 1.1.9 2 2 2Zm13.8-4.4 1.4 1.4L24 12l-6.8-7-1.4 1.4L20.1 11H8v2h12.1l-4.3 4.6Z"/>
                            </svg>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <aside class="mm-home__summary" aria-label="Resumo do programa">
                <div class="mm-home__summary-card">
                    <div class="mm-home__summary-item">
                        <span class="mm-home__summary-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M20 7h-2.2A3 3 0 0 0 12 5.4 3 3 0 0 0 6.2 7H4a2 2 0 0 0-2 2v3h20V9a2 2 0 0 0-2-2Zm-10 0H8a1 1 0 1 1 1-1 1 1 0 0 0 1 1Zm6 0h-2a1 1 0 0 0 1-1 1 1 0 1 1 1 1ZM3 14v6a2 2 0 0 0 2 2h6v-8H3Zm10 8h6a2 2 0 0 0 2-2v-6h-8v8Z"/></svg>
                        </span>
                        <strong>Seus amigos ganham</strong>
                    </div>
                    <div class="mm-home__summary-item">
                        <span class="mm-home__summary-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.42 0-8 2.24-8 5v1h16v-1c0-2.76-3.58-5-8-5Z"/></svg>
                        </span>
                        <strong>Você também ganha</strong>
                    </div>
                    <div class="mm-home__summary-item">
                        <span class="mm-home__summary-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M21 10.5 13.5 3H4a2 2 0 0 0-2 2v9.5L9.5 22a2 2 0 0 0 2.83 0L21 13.33a2 2 0 0 0 0-2.83ZM7 8a2 2 0 1 1 2-2 2 2 0 0 1-2 2Zm3.2 9.2-1.4-1.4 7-7 1.4 1.4-7 7Zm-.7-6.2a1.5 1.5 0 1 1 1.5-1.5A1.5 1.5 0 0 1 9.5 11Zm7 6a1.5 1.5 0 1 1 1.5-1.5 1.5 1.5 0 0 1-1.5 1.5Z"/></svg>
                        </span>
                        <strong>Descontos exclusivos</strong>
                    </div>
                </div>

                <div class="mm-home__summary-ribbon">
                    <span aria-hidden="true">❤</span>
                    É rápido, fácil e <strong>vale muito a pena!</strong>
                </div>
            </aside>
        </div>
    </section>

    <section class="mm-home__steps" id="como-funciona" aria-labelledby="steps-title">
        <div class="mm-home__container">
            <div class="mm-home__section-head">
                <span>Como funciona</span>
                <h2 id="steps-title">É <strong>simples</strong> e rápido!</h2>
            </div>

            <div class="mm-home__steps-grid">
                <article class="mm-home__step">
                    <div class="mm-home__step-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11A2.99 2.99 0 1 0 15 5c0 .24.04.47.09.7L8.04 9.81A3 3 0 1 0 8.04 14l7.12 4.18c-.05.2-.08.41-.08.62a2.92 2.92 0 1 0 2.92-2.72Z"/></svg>
                    </div>
                    <div class="mm-home__step-number">1</div>
                    <h3>Compartilhe</h3>
                    <p>Compartilhe seu link com amigos e familiares pelo WhatsApp ou onde quiser.</p>
                </article>

                <article class="mm-home__step">
                    <div class="mm-home__step-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 20h14v-2H5v2ZM19 9h-4V3H9v6H5l7 7 7-7Z"/></svg>
                    </div>
                    <div class="mm-home__step-number">2</div>
                    <h3>Seu amigo baixa o app</h3>
                    <p>Ele acessa o link e faz o download do app Minas Mais.</p>
                </article>

                <article class="mm-home__step">
                    <div class="mm-home__step-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4ZM15 14c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4ZM6 10V7H3V5h3V2h2v3h3v2H8v3H6Z"/></svg>
                    </div>
                    <div class="mm-home__step-number">3</div>
                    <h3>Ele se cadastra</h3>
                    <p>Seu amigo realiza o cadastro e a primeira compra pelo aplicativo.</p>
                </article>

                <article class="mm-home__step">
                    <div class="mm-home__step-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 7h-2.2A3 3 0 0 0 12 5.4 3 3 0 0 0 6.2 7H4a2 2 0 0 0-2 2v3h20V9a2 2 0 0 0-2-2ZM3 14v6a2 2 0 0 0 2 2h6v-8H3Zm10 8h6a2 2 0 0 0 2-2v-6h-8v8Z"/></svg>
                    </div>
                    <div class="mm-home__step-number">4</div>
                    <h3>Todos ganham</h3>
                    <p>Seu amigo ganha benefícios e você também recebe recompensas exclusivas.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="mm-home__app-banner" aria-label="Ofertas exclusivas no app">
        <div class="mm-home__container">
            <div class="mm-home__app-card">
                <img src="<?= e($banner2Url) ?>" alt="" loading="lazy">
                <div class="mm-home__app-text">
                    <span>Saúde, bem-estar e economia</span>
                    <h2>Aqui tem saúde<br>e <strong>muito mais!</strong></h2>
                    <p>Ofertas exclusivas, preços especiais e tudo que você precisa em um só lugar.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="mm-home__trust" aria-label="Benefícios Minas Mais">
        <div class="mm-home__container">
            <div class="mm-home__trust-grid">
                <article>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2 4 5v6c0 5.55 3.84 10.74 8 12 4.16-1.26 8-6.45 8-12V5l-8-3Zm-1 14-4-4 1.41-1.41L11 13.17l5.59-5.59L18 9l-7 7Z"/></svg>
                    <div>
                        <strong>Compra segura</strong>
                        <span>Ambiente protegido</span>
                    </div>
                </article>
                <article>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 10.5 13.5 3H4a2 2 0 0 0-2 2v9.5L9.5 22a2 2 0 0 0 2.83 0L21 13.33a2 2 0 0 0 0-2.83ZM7 8a2 2 0 1 1 2-2 2 2 0 0 1-2 2Z"/></svg>
                    <div>
                        <strong>Ofertas exclusivas</strong>
                        <span>Só no app</span>
                    </div>
                </article>
                <article>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a8 8 0 0 0-8 8v3a4 4 0 0 0 4 4h1v-6H6v-1a6 6 0 0 1 12 0v1h-3v6h1a4 4 0 0 0 4-4v-3a8 8 0 0 0-8-8Zm-2 18h4v2h-4v-2Z"/></svg>
                    <div>
                        <strong>Atendimento rápido</strong>
                        <span>Sempre que precisar</span>
                    </div>
                </article>
                <article>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 11c1.66 0 3-1.57 3-3.5S17.66 4 16 4s-3 1.57-3 3.5S14.34 11 16 11Zm-8 0c1.66 0 3-1.57 3-3.5S9.66 4 8 4 5 5.57 5 7.5 6.34 11 8 11Zm0 2c-2.67 0-8 1.34-8 4v2h10v-2c0-1.04.4-2.02 1.1-2.86C9.94 13.43 8.74 13 8 13Zm8 0c-.74 0-1.94.43-3.1 1.14A3.73 3.73 0 0 0 11.8 17v2H24v-2c0-2.66-5.33-4-8-4Z"/></svg>
                    <div>
                        <strong>Confiança</strong>
                        <span>Drogaria Minas Mais</span>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <footer class="mm-home__footer">
        <div class="mm-home__container mm-home__footer-inner">
            <img src="<?= e($logoUrl) ?>" alt="Drogarias Minas Mais" width="180" height="60">
            <p>Cuidar de você. Esse é nosso compromisso.</p>
            <span>© <?= date('Y') ?> Drogarias Minas Mais</span>
        </div>
    </footer>
</main>

<script src="<?= asset('js/home-landing.js') ?>" defer></script>
