<?php declare(strict_types=1); ?>

<section class="page-hero">
    <h1 class="page-hero__title">Cadastro Realizado</h1>
    <p class="page-hero__subtitle">Seu cadastro foi concluído com sucesso!</p>
</section>

<section class="mm-card mm-card--success">
    <div class="mm-card__header">
        <h2 class="mm-card__title">Parabéns!</h2>
    </div>
    
    <div class="success-content">
        <div class="success-content__icon">✅</div>
        <p class="success-content__text">
            Cadastro realizado com sucesso.
        </p>
        <p class="success-content__text">
            Agora falta concluir as próximas etapas para validar sua participação.
        </p>
    </div>
    
    <div class="success-actions">
        <a href="<?= url('/login') ?>" class="btn btn--block btn--primary">
            Entrar
        </a>
        <a href="<?= url('/') ?>" class="btn btn--block btn--outline">
            Voltar ao Início
        </a>
    </div>
</section>

<section class="mm-card">
    <div class="mm-card__header">
        <h2 class="mm-card__title">Próximos Passos</h2>
    </div>
    
    <div class="next-steps">
        <div class="next-step">
            <span class="next-step__icon">1️⃣</span>
            <div class="next-step__content">
                <h3 class="next-step__title">Faça login</h3>
                <p class="next-step__text">Acesse sua conta com email e senha.</p>
            </div>
        </div>
        
        <div class="next-step">
            <span class="next-step__icon">2️⃣</span>
            <div class="next-step__content">
                <h3 class="next-step__title">Complete seu perfil</h3>
                <p class="next-step__text">Atualize suas informações pessoais.</p>
            </div>
        </div>
        
        <div class="next-step">
            <span class="next-step__icon">3️⃣</span>
            <div class="next-step__content">
                <h3 class="next-step__title">Aguarde validação</h3>
                <p class="next-step__text">Seus dados serão validados pela equipe.</p>
            </div>
        </div>
        
        <div class="next-step">
            <span class="next-step__icon">4️⃣</span>
            <div class="next-step__content">
                <h3 class="next-step__title">Receba benefícios</h3>
                <p class="next-step__text">Após validação, você receberá seus benefícios.</p>
            </div>
        </div>
    </div>
</section>
