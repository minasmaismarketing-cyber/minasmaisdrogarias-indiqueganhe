<section class="convite-landing animate-slide">
    <?php if (!$valid): ?>
        <div class="convite-landing__card convite-landing__card--error">
            <div class="convite-landing__logo">
                <img
                    src="<?= e(brand_logo_url()) ?>"
                    alt="Minas Mais Drogaria e Perfumaria"
                    class="convite-landing__logo-img"
                    width="220"
                    height="48"
                    loading="eager"
                >
            </div>
            <h1 class="convite-landing__title">Convite indisponível</h1>
            <p class="convite-landing__text"><?= e($error) ?></p>
            <a href="<?= url('/') ?>" class="btn btn--block">Voltar ao início</a>
        </div>
    <?php else: ?>
        <div class="convite-landing__card">
            <div class="convite-landing__logo">
                <img
                    src="<?= e(brand_logo_url()) ?>"
                    alt="Minas Mais Drogaria e Perfumaria"
                    class="convite-landing__logo-img"
                    width="220"
                    height="48"
                    loading="eager"
                >
            </div>

            <p class="convite-landing__badge">🎁 Você foi indicado!</p>

            <h1 class="convite-landing__title">
                <?php if ($indicadorPrimeiroNome !== ''): ?>
                    <span class="convite-landing__referrer"><?= e($indicadorPrimeiroNome) ?></span>
                    quer dividir um benefício com você!
                <?php else: ?>
                    Alguém especial quer dividir um benefício com você!
                <?php endif; ?>
            </h1>

            <section class="convite-landing__benefits" aria-labelledby="convite-benefits-title">
                <h2 id="convite-benefits-title" class="convite-landing__section-title">Você ganha:</h2>
                <ul class="convite-landing__list">
                    <li>✅ 5% OFF na primeira compra</li>
                    <li>✅ Entrega rápida em até 30 minutos</li>
                    <li>✅ Promoções exclusivas no aplicativo</li>
                </ul>
            </section>

            <section class="convite-landing__steps" aria-labelledby="convite-steps-title">
                <h2 id="convite-steps-title" class="convite-landing__section-title">Como funciona?</h2>
                <ol class="convite-landing__steps-list">
                    <li>1️⃣ Baixe o aplicativo</li>
                    <li>2️⃣ <strong>Faça seu cadastro</strong></li>
                    <li>3️⃣ Seu desconto será liberado automaticamente</li>
                </ol>
            </section>

            <a
                href="<?= e($appDownloadUrl) ?>"
                class="convite-landing__cta btn btn--block"
                <?= $appDownloadUrl === '#' ? 'role="button" aria-disabled="true"' : 'target="_blank" rel="noopener noreferrer"' ?>
            >
                BAIXAR O APP
            </a>

            <p class="convite-landing__footnote">
                Oferta válida para novos cadastros realizados através desta indicação.
            </p>
        </div>
    <?php endif; ?>
</section>
