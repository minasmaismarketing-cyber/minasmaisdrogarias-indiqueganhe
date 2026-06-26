<section class="page-hero animate-slide">
    <h1 class="page-hero__title">Indique e Ganhe</h1>
    <p class="page-hero__subtitle">Indique amigos e ganhe benefícios nas Drogarias Minas Mais.</p>
</section>

<?php if (!auth_check()): ?>
<section class="home-cta">
    <a href="<?= url('/cadastro') ?>" class="btn btn--block">Quero participar</a>
    <a href="<?= url('/login') ?>" class="btn btn--block btn--outline">Já tenho conta</a>
</section>
<?php else: ?>
<section class="home-cta">
    <a href="<?= url('/dashboard') ?>" class="btn btn--block">Ir para meu painel</a>
</section>
<?php endif; ?>

<section class="mm-card">
    <h2 class="mm-card__title">Status do sistema</h2>
    <ul class="status-list">
        <li>
            <span class="status-label">Ambiente</span>
            <span class="badge badge--info"><?= e((string) $appEnv) ?></span>
        </li>
        <li>
            <span class="status-label">Banco</span>
            <?php if ($dbConnected): ?>
                <span class="badge badge--success">Conectado</span>
            <?php else: ?>
                <span class="badge badge--error">Desconectado</span>
            <?php endif; ?>
        </li>
        <li>
            <span class="status-label">Sessão</span>
            <span class="badge badge--success">Ativa</span>
        </li>
        <li>
            <span class="status-label">CSRF</span>
            <?php if ($csrfReady): ?>
                <span class="badge badge--success">Protegido</span>
            <?php else: ?>
                <span class="badge badge--error">Indisponível</span>
            <?php endif; ?>
        </li>
    </ul>
</section>

<section class="mm-card mm-card--muted">
    <h2 class="mm-card__title mm-card__title--sm">Diagnóstico PDO</h2>
    <ul class="status-list">
        <li><span class="status-label">HOST</span><span class="status-value"><?= e($dbTest['host']) ?></span></li>
        <li><span class="status-label">DB</span><span class="status-value"><?= e($dbTest['db']) ?></span></li>
        <li><span class="status-label">USER</span><span class="status-value"><?= e($dbTest['user']) ?></span></li>
        <li>
            <span class="status-label">PDO</span>
            <?php if ($dbTest['connected']): ?>
                <span class="status-value status-value--success"><?= e($dbTest['status']) ?></span>
            <?php else: ?>
                <span class="status-value status-value--error"><?= e($dbTest['status']) ?></span>
            <?php endif; ?>
        </li>
    </ul>
</section>

<footer class="page-footer">
    <p>© 2026 - Criado por NEXDEN Digital</p>
</footer>
