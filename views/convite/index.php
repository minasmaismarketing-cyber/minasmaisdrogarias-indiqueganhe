<section class="convite-page animate-slide">
    <?php if (!$valid): ?>
        <div class="mm-card">
            <h1 class="convite-page__title">Convite indisponível</h1>
            <p class="convite-page__text"><?= e($error) ?></p>
            <a href="<?= url('/') ?>" class="btn btn--block">Voltar ao início</a>
        </div>
    <?php else: ?>
        <div class="convite-page__logo">
            <img src="<?= e(brand_logo_url()) ?>" alt="" class="convite-page__logo-img">
        </div>

        <div class="mm-card mm-card--highlight">
            <h1 class="convite-page__title">Indique amigos e ganhe benefícios</h1>
            <p class="convite-page__text">
                Participe do programa de indicação da Minas Mais e aproveite vantagens exclusivas.
            </p>

            <form method="POST" action="<?= url('/convite/participar') ?>" class="convite-page__form">
                <?= csrf_field() ?>
                <input type="hidden" name="ref" value="<?= e($ref) ?>">
                <button type="submit" class="btn btn--block btn--primary">Participar</button>
            </form>
        </div>
    <?php endif; ?>
</section>

<footer class="page-footer">
    <p>© 2026 - Criado por NEXDEN Digital</p>
</footer>
