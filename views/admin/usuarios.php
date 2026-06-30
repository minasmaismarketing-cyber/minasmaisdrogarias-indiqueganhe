<?php declare(strict_types=1); ?>
<?php $subtitle = 'Gerencie os usuários do sistema.'; ?>

<section class="mm-card">
    <div class="mm-card__header">
        <h2 class="mm-card__title">Lista de Usuários</h2>
    </div>

    <?php if ($usuarios === []): ?>
        <p class="mm-card__placeholder">Nenhum usuário cadastrado.</p>
    <?php else: ?>
        <ul class="admin-list">
            <?php foreach ($usuarios as $usuario): ?>
                <li class="admin-list__item">
                    <div class="admin-list__info">
                        <strong><?= e($usuario['nome']) ?></strong>
                        <span class="admin-list__meta"><?= e($usuario['email']) ?></span>
                        <span class="admin-list__meta"><?= e(format_phone((string) $usuario['telefone'])) ?></span>
                    </div>
                    <div class="admin-list__meta">
                        <?= e(date('d/m/Y', strtotime($usuario['created_at']))) ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
