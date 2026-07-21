<?php

declare(strict_types=1);

$errors = $errors ?? [];
$old = $old ?? [];
$membroDesde = format_date($user['created_at'] ?? null);
$dataCadastro = $membroDesde;
$aceiteCampanha = !empty($user['aceite_lgpd']) ? 'Sim' : 'Não';
$isAtivo = (int) ($user['ativo'] ?? 1) === 1;
$statusParticipacao = $isAtivo ? 'Ativo' : 'Bloqueado';
$nomeExibicao = (string) ($old['nome'] ?? $user['nome'] ?? '');
$whatsappExibicao = (string) ($old['whatsapp'] ?? $user['whatsapp'] ?? '');
$whatsappFormatado = format_phone((string) ($user['whatsapp'] ?? ''));
$cpfFormatado = format_cpf((string) ($user['cpf'] ?? ''));
$codigoIndicador = (string) ($user['codigo_indicador'] ?? '');
$emailExibicao = (string) ($user['email'] ?? '');

$supportMessage = 'Olá! Vim pelo sistema da campanha Indique e Ganhe da Minas Mais e gostaria de receber algumas informações. Pode me ajudar?';
$supportUrl = 'https://wa.me/553599125296?text=' . rawurlencode($supportMessage);

$openEditModal = !empty($errors['nome']) || !empty($errors['whatsapp']);
$openPasswordModal = !empty($errors['senha_atual']) || !empty($errors['nova_senha']) || !empty($errors['confirmar_senha']);
$openDeleteModal = !empty($errors['senha']);
?>

<?php require BASE_PATH . '/views/partials/alerts.php'; ?>

<div class="perfil-stack">
    <header class="perfil-page-header animate-slide">
        <span class="perfil-page-header__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.75"/>
                <circle cx="12" cy="9" r="3.25" stroke="currentColor" stroke-width="1.75"/>
                <path d="M6.5 18.2c1.5-2.2 3.4-3.2 5.5-3.2s4 1 5.5 3.2" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
            </svg>
        </span>
        <div class="perfil-page-header__copy">
            <h1 class="perfil-page-header__title">Meu perfil</h1>
            <p class="perfil-page-header__subtitle">Gerencie seus dados de cadastro.</p>
        </div>
    </header>

    <section class="perfil-card perfil-card--summary" aria-label="Resumo do usuário">
        <div class="perfil-summary__top">
            <img src="<?= e(brand_logo_url()) ?>" alt="" class="perfil-summary__logo" width="56" height="56">
            <div class="perfil-summary__identity">
                <h2 class="perfil-summary__name"><?= e((string) $user['nome']) ?></h2>
                <p class="perfil-summary__since">Membro desde: <?= e($membroDesde !== '' ? $membroDesde : '—') ?></p>
            </div>
        </div>

        <dl class="perfil-summary__meta">
            <div class="perfil-summary__row">
                <dt>Aceita campanha</dt>
                <dd class="perfil-summary__value perfil-summary__value--ok"><?= e($aceiteCampanha) ?></dd>
            </div>
            <div class="perfil-summary__row">
                <dt>Data de cadastro</dt>
                <dd class="perfil-summary__value"><?= e($dataCadastro !== '' ? $dataCadastro : '—') ?></dd>
            </div>
            <div class="perfil-summary__row">
                <dt>Status de participação</dt>
                <dd class="perfil-summary__value <?= $isAtivo ? 'perfil-summary__value--ok' : 'perfil-summary__value--danger' ?>">
                    <?= e($statusParticipacao) ?>
                </dd>
            </div>
        </dl>
    </section>

    <section class="perfil-card" aria-labelledby="perfil-dados-title">
        <h2 id="perfil-dados-title" class="perfil-card__title">Dados do perfil</h2>

        <ul class="perfil-fields">
            <li class="perfil-fields__item">
                <span class="perfil-fields__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="8" r="3.25" stroke="currentColor" stroke-width="1.75"/><path d="M5.5 19c1.6-2.5 3.7-3.7 6.5-3.7s4.9 1.2 6.5 3.7" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/></svg>
                </span>
                <div class="perfil-fields__content">
                    <span class="perfil-fields__label">Nome</span>
                    <span class="perfil-fields__value"><?= e((string) $user['nome']) ?></span>
                </div>
            </li>
            <li class="perfil-fields__item">
                <span class="perfil-fields__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none"><rect x="4" y="6" width="16" height="12" rx="2" stroke="currentColor" stroke-width="1.75"/><path d="M8 10h8M8 14h5" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/></svg>
                </span>
                <div class="perfil-fields__content">
                    <span class="perfil-fields__label">CPF</span>
                    <span class="perfil-fields__value"><?= e($cpfFormatado) ?></span>
                </div>
            </li>
            <li class="perfil-fields__item">
                <span class="perfil-fields__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none"><rect x="3.5" y="5.5" width="17" height="13" rx="2" stroke="currentColor" stroke-width="1.75"/><path d="m4.5 8 7.5 5.5L19.5 8" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <div class="perfil-fields__content">
                    <span class="perfil-fields__label">E-mail</span>
                    <span class="perfil-fields__value"><?= e($emailExibicao) ?></span>
                </div>
            </li>
            <li class="perfil-fields__item">
                <span class="perfil-fields__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none"><path d="M8.5 5.5h2.2l1 3.2-1.5 1.2a10.5 10.5 0 0 0 4.4 4.4l1.2-1.5 3.2 1v2.2A2.2 2.2 0 0 1 17 18.2 12.7 12.7 0 0 1 5.8 7a2.2 2.2 0 0 1 2.7-1.5Z" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/></svg>
                </span>
                <div class="perfil-fields__content">
                    <span class="perfil-fields__label">WhatsApp</span>
                    <span class="perfil-fields__value"><?= e($whatsappFormatado !== '' ? $whatsappFormatado : (string) $user['whatsapp']) ?></span>
                </div>
            </li>
            <li class="perfil-fields__item">
                <span class="perfil-fields__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none"><path d="M7 7h10v10H7V7Z" stroke="currentColor" stroke-width="1.75"/><path d="M9.5 12h5M12 9.5v5" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/></svg>
                </span>
                <div class="perfil-fields__content">
                    <span class="perfil-fields__label">Código indicador</span>
                    <span class="perfil-fields__value"><?= e($codigoIndicador) ?></span>
                </div>
            </li>
        </ul>

        <button type="button" class="btn btn--block btn--primary perfil-edit-trigger" id="btn-edit-profile" data-open-modal="edit-profile">
            <svg class="perfil-btn-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M4 20h4l10.5-10.5a2.1 2.1 0 0 0-3-3L5 17v3Z" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/>
                <path d="m13.5 6.5 3 3" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
            </svg>
            Editar dados
        </button>
    </section>

    <section class="perfil-card perfil-card--action" aria-labelledby="perfil-senha-title">
        <div class="perfil-action__head">
            <span class="perfil-action__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none"><rect x="5" y="10" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.75"/><path d="M8 10V8a4 4 0 0 1 8 0v2" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/></svg>
            </span>
            <div>
                <h2 id="perfil-senha-title" class="perfil-card__title">Trocar senha</h2>
                <p class="perfil-card__help">Altere sua senha de acesso.</p>
            </div>
        </div>
        <button type="button" class="btn btn--block btn--outline" id="btn-change-password" data-open-modal="change-password">
            Trocar senha
        </button>
    </section>

    <section class="perfil-card perfil-card--danger" aria-labelledby="perfil-excluir-title">
        <div class="perfil-action__head">
            <span class="perfil-action__icon perfil-action__icon--danger" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none"><path d="M5 7h14M9 7V5h6v2M8 7l1 12h6l1-12" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </span>
            <div>
                <h2 id="perfil-excluir-title" class="perfil-card__title">Excluir minha conta</h2>
                <p class="perfil-card__help">Esta ação não pode ser desfeita.</p>
            </div>
        </div>
        <button type="button" class="btn btn--block btn--danger" id="btn-delete-account" data-open-modal="delete-account">
            Excluir conta
        </button>
    </section>

    <section class="perfil-card perfil-card--support" aria-labelledby="perfil-suporte-title">
        <h2 id="perfil-suporte-title" class="perfil-card__title">Precisa de ajuda?</h2>
        <p class="perfil-card__help">Fale com nosso suporte pelo WhatsApp.</p>
        <a
            href="<?= e($supportUrl) ?>"
            class="btn btn--block perfil-support__btn"
            target="_blank"
            rel="noopener noreferrer"
            aria-label="Falar com suporte pelo WhatsApp"
        >
            <svg class="perfil-btn-icon" viewBox="0 0 24 24" aria-hidden="true">
                <path fill="currentColor" d="M19.05 4.91A9.82 9.82 0 0 0 12.04 2C6.58 2 2.14 6.44 2.14 11.9c0 1.75.46 3.45 1.32 4.95L2 22l5.3-1.38c1.45.79 3.08 1.21 4.74 1.21h.01c5.46 0 9.9-4.44 9.9-9.9 0-2.65-1.03-5.14-2.9-7.02zm-7.01 15.24h-.01a8.2 8.2 0 0 1-4.18-1.15l-.3-.18-3.14.82.84-3.06-.2-.31a8.2 8.2 0 0 1-1.26-4.37c0-4.54 3.7-8.24 8.25-8.24 2.2 0 4.27.86 5.82 2.42a8.18 8.18 0 0 1 2.41 5.83c.02 4.54-3.68 8.24-8.23 8.24zm4.52-6.16c-.25-.12-1.47-.72-1.7-.81-.23-.08-.4-.12-.56.12-.17.25-.64.8-.79.97-.14.17-.3.19-.55.06-.25-.12-1.05-.39-2-1.23-.74-.66-1.23-1.47-1.38-1.72-.14-.25-.02-.38.11-.51.11-.11.25-.3.37-.44.12-.15.17-.25.25-.41.08-.17.04-.31-.02-.43-.06-.12-.56-1.35-.77-1.84-.2-.49-.41-.42-.56-.43h-.48c-.17 0-.43.06-.66.31-.22.25-.86.85-.86 2.07s.88 2.4 1 2.56c.12.17 1.75 2.67 4.25 3.74.59.26 1.05.41 1.41.52.59.19 1.13.16 1.56.1.48-.07 1.47-.6 1.67-1.18.21-.58.21-1.07.14-1.18-.06-.11-.22-.17-.47-.29z"/>
            </svg>
            Falar com suporte
        </a>
        <p class="perfil-support__note">Ao iniciar o atendimento, informe que veio do sistema de Indique e Ganhe.</p>
    </section>

    <form method="POST" action="<?= url('/logout') ?>" class="perfil-logout">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn--block btn--ghost">Sair da conta</button>
    </form>

    <p class="perfil-footer">Desenvolvido por NEXDEN Digital</p>
</div>

<!-- Modal: Editar dados -->
<div
    id="edit-profile-modal"
    class="perfil-modal"
    hidden
    role="dialog"
    aria-modal="true"
    aria-labelledby="edit-profile-title"
    data-modal
>
    <div class="perfil-modal__overlay" data-close-modal tabindex="-1"></div>
    <div class="perfil-modal__panel" role="document">
        <header class="perfil-modal__header">
            <h3 class="perfil-modal__title" id="edit-profile-title">Editar dados</h3>
            <button type="button" class="perfil-modal__close" data-close-modal aria-label="Fechar">×</button>
        </header>
        <form method="POST" action="<?= url('/perfil') ?>" class="form perfil-modal__form">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="edit-nome">Nome</label>
                <input type="text" id="edit-nome" name="nome" value="<?= e($nomeExibicao) ?>" required maxlength="150" autocomplete="name">
                <?php if (!empty($errors['nome'])): ?><span class="form-error"><?= e($errors['nome']) ?></span><?php endif; ?>
            </div>
            <div class="form-group">
                <label for="edit-cpf">CPF</label>
                <input type="text" id="edit-cpf" value="<?= e($cpfFormatado) ?>" disabled class="input-disabled" tabindex="-1">
                <small class="form-help">CPF não pode ser alterado</small>
            </div>
            <div class="form-group">
                <label for="edit-email">E-mail</label>
                <input type="email" id="edit-email" value="<?= e($emailExibicao) ?>" disabled class="input-disabled" tabindex="-1">
                <small class="form-help">E-mail não pode ser alterado neste fluxo</small>
            </div>
            <div class="form-group">
                <label for="whatsapp">WhatsApp</label>
                <input type="text" id="whatsapp" name="whatsapp" value="<?= e($whatsappExibicao) ?>" inputmode="tel" required autocomplete="tel">
                <?php if (!empty($errors['whatsapp'])): ?><span class="form-error"><?= e($errors['whatsapp']) ?></span><?php endif; ?>
            </div>
            <div class="form-group">
                <label for="edit-codigo">Código indicador</label>
                <input type="text" id="edit-codigo" value="<?= e($codigoIndicador) ?>" disabled class="input-disabled" tabindex="-1">
                <small class="form-help">Código de indicação não pode ser alterado</small>
            </div>
            <div class="perfil-modal__actions">
                <button type="button" class="btn btn--ghost" data-close-modal>Cancelar</button>
                <button type="submit" class="btn btn--primary">Salvar alterações</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Trocar senha -->
<div
    id="change-password-modal"
    class="perfil-modal"
    hidden
    role="dialog"
    aria-modal="true"
    aria-labelledby="change-password-title"
    data-modal
>
    <div class="perfil-modal__overlay" data-close-modal tabindex="-1"></div>
    <div class="perfil-modal__panel" role="document">
        <header class="perfil-modal__header">
            <h3 class="perfil-modal__title" id="change-password-title">Trocar senha</h3>
            <button type="button" class="perfil-modal__close" data-close-modal aria-label="Fechar">×</button>
        </header>
        <form method="POST" action="<?= url('/perfil/senha') ?>" class="form perfil-modal__form">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="senha_atual">Senha atual</label>
                <input type="password" id="senha_atual" name="senha_atual" required autocomplete="current-password">
                <?php if (!empty($errors['senha_atual'])): ?><span class="form-error"><?= e($errors['senha_atual']) ?></span><?php endif; ?>
            </div>
            <div class="form-group">
                <label for="nova_senha">Nova senha</label>
                <input type="password" id="nova_senha" name="nova_senha" required autocomplete="new-password">
                <?php if (!empty($errors['nova_senha'])): ?><span class="form-error"><?= e($errors['nova_senha']) ?></span><?php endif; ?>
            </div>
            <div class="form-group">
                <label for="confirmar_senha">Confirmar nova senha</label>
                <input type="password" id="confirmar_senha" name="confirmar_senha" required autocomplete="new-password">
                <?php if (!empty($errors['confirmar_senha'])): ?><span class="form-error"><?= e($errors['confirmar_senha']) ?></span><?php endif; ?>
            </div>
            <div class="perfil-modal__actions">
                <button type="button" class="btn btn--ghost" data-close-modal>Cancelar</button>
                <button type="submit" class="btn btn--primary">Salvar alterações</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Excluir conta -->
<div
    id="delete-account-modal"
    class="perfil-modal"
    hidden
    role="dialog"
    aria-modal="true"
    aria-labelledby="delete-account-title"
    data-modal
>
    <div class="perfil-modal__overlay" data-close-modal tabindex="-1"></div>
    <div class="perfil-modal__panel" role="document">
        <header class="perfil-modal__header">
            <h3 class="perfil-modal__title" id="delete-account-title">Confirmar exclusão de conta</h3>
            <button type="button" class="perfil-modal__close" data-close-modal aria-label="Fechar">×</button>
        </header>
        <p class="perfil-modal__text">Tem certeza que deseja excluir sua conta? Esta ação não pode ser desfeita.</p>
        <form method="POST" action="<?= url('/perfil/excluir') ?>" class="form perfil-modal__form">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="senha">Digite sua senha para confirmar</label>
                <input type="password" id="senha" name="senha" required autocomplete="current-password">
                <?php if (!empty($errors['senha'])): ?><span class="form-error"><?= e($errors['senha']) ?></span><?php endif; ?>
            </div>
            <div class="perfil-modal__actions">
                <button type="button" class="btn btn--ghost" data-close-modal>Cancelar</button>
                <button type="submit" class="btn btn--danger">Confirmar exclusão</button>
            </div>
        </form>
    </div>
</div>

<script>
    window.__PERFIL__ = {
        openModal: <?= json_encode(
            $openEditModal ? 'edit-profile' : ($openPasswordModal ? 'change-password' : ($openDeleteModal ? 'delete-account' : null)),
            JSON_UNESCAPED_UNICODE
        ) ?>
    };
</script>
<script src="<?= asset('js/profile.js') ?>"></script>
