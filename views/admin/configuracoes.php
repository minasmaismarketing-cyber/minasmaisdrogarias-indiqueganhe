<?php declare(strict_types=1); ?>
<?php $subtitle = 'Configure o sistema.'; ?>

<section class="admin-card">
    <header class="admin-card__header">
        <h2 class="admin-card__title">Configurações Gerais</h2>
    </header>

    <div class="config-list">
        <div class="config-list__item">
            <span class="config-list__label">Papel do usuário logado</span>
            <span class="config-list__value"><?= e(Usuario::roleLabel((string) (Auth::user()['role'] ?? Usuario::ROLE_CLIENTE))) ?></span>
        </div>
        <div class="config-list__item">
            <span class="config-list__label">Versão do Sistema</span>
            <span class="config-list__value">ETAPA 7</span>
        </div>
        <div class="config-list__item">
            <span class="config-list__label">Ambiente</span>
            <span class="config-list__value"><?= AppsFlyerConfig::isHomologation() ? 'Homologação AppsFlyer' : 'Produção' ?></span>
        </div>
    </div>
</section>

<section class="admin-card">
    <header class="admin-card__header">
        <h2 class="admin-card__title">Integrações — AppsFlyer &amp; KOBE</h2>
    </header>

    <div class="config-list">
        <?php foreach ($integrationRows ?? [] as $row): ?>
            <div class="config-list__item">
                <span class="config-list__label"><?= e((string) ($row['name'] ?? '')) ?></span>
                <span class="config-list__value">
                    <?= e((string) ($row['emoji'] ?? '')) ?> <?= e((string) ($row['label'] ?? '')) ?>
                    <?php if (!empty($row['detail'])): ?>
                        <span class="config-list__value--inactive"> · <?= e((string) $row['detail']) ?></span>
                    <?php endif; ?>
                </span>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="admin-card">
    <header class="admin-card__header">
        <h2 class="admin-card__title">Outras Integrações</h2>
    </header>

    <div class="config-list">
        <div class="config-list__item">
            <span class="config-list__label">VTEX</span>
            <span class="config-list__value config-list__value--inactive">Não integrado</span>
        </div>
        <div class="config-list__item">
            <span class="config-list__label">WhatsApp</span>
            <span class="config-list__value config-list__value--inactive">Não integrado</span>
        </div>
    </div>
</section>
