<?php declare(strict_types=1); ?>
<?php $subtitle = 'Configure o sistema.'; ?>

<section class="mm-card">
    <div class="mm-card__header">
        <h2 class="mm-card__title">Configurações Gerais</h2>
    </div>
    
    <div class="config-list">
        <div class="config-list__item">
            <span class="config-list__label">Email do Administrador</span>
            <span class="config-list__value"><?= e(AdminController::ADMIN_EMAIL) ?></span>
        </div>
        <div class="config-list__item">
            <span class="config-list__label">Versão do Sistema</span>
            <span class="config-list__value">ETAPA 7</span>
        </div>
        <div class="config-list__item">
            <span class="config-list__label">Ambiente</span>
            <span class="config-list__value">Simulação</span>
        </div>
    </div>
</section>

<section class="mm-card">
    <div class="mm-card__header">
        <h2 class="mm-card__title">Integrações</h2>
    </div>
    
    <div class="config-list">
        <div class="config-list__item">
            <span class="config-list__label">AppsFlyer</span>
            <span class="config-list__value config-list__value--inactive">Não integrado</span>
        </div>
        <div class="config-list__item">
            <span class="config-list__label">VTEX</span>
            <span class="config-list__value config-list__value--inactive">Não integrado</span>
        </div>
        <div class="config-list__item">
            <span class="config-list__label">WhatsApp</span>
            <span class="config-list__value config-list__value--inactive">Não integrado</span>
        </div>
        <div class="config-list__item">
            <span class="config-list__label">KOBE</span>
            <span class="config-list__value config-list__value--inactive">Não integrado</span>
        </div>
    </div>
</section>
