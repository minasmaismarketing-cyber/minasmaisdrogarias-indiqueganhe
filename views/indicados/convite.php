<?php declare(strict_types=1); ?>
<?php require BASE_PATH . '/views/partials/alerts.php'; ?>

<?php 
$campanha = $campanha ?? null;
$titulo = $campanha['texto_landing'] ?? 'Indique amigos e ganhe benefícios';
$descricao = $campanha['descricao'] ?? 'Convide amigos para conhecer a Minas Mais e desbloqueie benefícios exclusivos durante a campanha.';
$botaoTexto = $campanha['texto_botao'] ?? 'Quero participar';
$corPrimaria = $campanha['cor_primaria'] ?? '#D71920';
$corSecundaria = $campanha['cor_secundaria'] ?? '#7A7A7A';
?>

<style>
<?php if ($campanha): ?>
:root {
    --brand-primary: <?= $corPrimaria ?>;
    --brand-secondary: <?= $corSecundaria ?>;
}
<?php endif; ?>
</style>

<section class="invite-section">
    <div class="invite-container">
        <?php if ($campanha && $campanha['banner']): ?>
            <div class="invite-banner">
                <img src="<?= e($campanha['banner']) ?>" alt="<?= e($campanha['nome']) ?>" class="invite-banner__image">
            </div>
        <?php endif; ?>
        
        <div class="mm-card mm-card--highlight">
            <div class="invite-content">
                <h1 class="invite-title"><?= e($titulo) ?></h1>
                
                <p class="invite-description">
                    <?= e($descricao) ?>
                </p>
                
                <form method="POST" action="<?= url('/participar') ?>" class="form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="codigo" value="<?= e($codigo) ?>">
                    
                    <button type="submit" class="btn btn--block btn--primary btn--tall">
                        <?= e($botaoTexto) ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>
