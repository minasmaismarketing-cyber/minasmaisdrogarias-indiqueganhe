<?php declare(strict_types=1); ?>
<?php require BASE_PATH . '/views/partials/alerts.php'; ?>

<div class="cupons-stack">
    <section class="page-hero page-hero--compact animate-slide">
        <h1 class="page-hero__title">Meus Cupons</h1>
        <p class="page-hero__subtitle page-hero__subtitle--visible">Seus benefícios de indicação.</p>
    </section>

    <section class="coupons-list">
        <?php if ($cupons === []): ?>
            <div class="mm-card">
                <div class="mm-empty-state">
                    <span class="mm-empty-state__icon" aria-hidden="true">%</span>
                    <p class="mm-empty-state__title">Você ainda não possui cupons</p>
                    <p class="mm-empty-state__text">Indique amigos e ganhe cupons quando suas indicações forem aprovadas.</p>
                    <a href="<?= url('/dashboard') ?>" class="btn btn--block btn--primary">Ir para o painel</a>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($cupons as $cupom): ?>
                <?php
                $status = (string) ($cupom['status'] ?? '');
                $isDisponivel = $status === Cupom::STATUS_DISPONIVEL;
                $isUtilizado = $status === Cupom::STATUS_UTILIZADO;
                $valor = (float) ($cupom['valor'] ?? 0);
                $isPercent = ($cupom['tipo'] ?? '') === Cupom::TIPO_PERCENTUAL;
                $offLabel = $isPercent
                    ? rtrim(rtrim(number_format($valor, 2, ',', ''), '0'), ',') . '% OFF'
                    : 'R$ ' . number_format($valor, 2, ',', '.');
                $utilizadoEm = !empty($cupom['utilizado_em'])
                    ? date('d/m/Y H:i', strtotime((string) $cupom['utilizado_em']))
                    : null;
                $codigo = (string) ($cupom['codigo'] ?? '');
                ?>
                <article
                    class="coupon-card coupon-card--premium coupon-card--<?= e(strtolower($status)) ?>"
                    aria-label="<?= $isUtilizado ? 'Cupom utilizado' : 'Cupom disponível' ?>"
                >
                    <div class="coupon-card__accent" aria-hidden="true"></div>
                    <div class="coupon-card__inner">
                        <?php if ($isUtilizado): ?>
                            <span class="coupon-card__seal coupon-card__seal--used">Cupom utilizado</span>
                        <?php else: ?>
                            <span class="coupon-card__seal coupon-card__seal--ok">Benefício liberado</span>
                        <?php endif; ?>

                        <h2 class="coupon-card__title">Seu cupom exclusivo</h2>
                        <p class="coupon-card__off" aria-label="<?= e($offLabel) ?>"><?= e($offLabel) ?></p>
                        <p class="coupon-card__hint-text">Use em qualquer compra no App Minas Mais.</p>

                        <?php if ($isDisponivel && $codigo !== ''): ?>
                            <div class="coupon-card__actions">
                                <button
                                    type="button"
                                    class="btn btn--block btn--primary coupon-card__copy"
                                    data-copy="<?= e($codigo) ?>"
                                    data-copy-success="Cupom copiado"
                                    data-copy-toast="Cupom copiado"
                                    aria-label="Copiar meu cupom"
                                >
                                    Copiar meu cupom
                                </button>
                                <p class="coupon-card__warning" role="note">
                                    Uso único e intransferível. Não compartilhe este código.
                                </p>
                            </div>
                        <?php elseif ($isUtilizado): ?>
                            <div class="coupon-card__used" role="status">
                                <?php if ($utilizadoEm !== null): ?>
                                    <p class="coupon-card__used-date">Utilizado em: <?= e($utilizadoEm) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="coupon-card__status-line" role="status">
                                <?= Cupom::statusIcon($status) ?>
                                <?= e(Cupom::statusLabel($status)) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <?php if (!empty($beneficioShares)): ?>
        <section class="beneficio-amigo-list" aria-label="Avisar amigos indicados">
            <?php foreach ($beneficioShares as $share): ?>
                <?php
                $nomeAmigo = trim((string) ($share['nome'] ?? ''));
                if ($nomeAmigo === '' || strcasecmp($nomeAmigo, 'Indicado via API') === 0) {
                    $nomeAmigo = '';
                }
                ?>
                <article class="beneficio-amigo-card">
                    <div class="beneficio-amigo-card__accent" aria-hidden="true"></div>
                    <div class="beneficio-amigo-card__inner">
                        <span class="beneficio-amigo-card__seal">Benefício do amigo</span>
                        <h2 class="beneficio-amigo-card__title">Seu amigo também ganhou! 🎉</h2>
                        <p class="beneficio-amigo-card__text">
                            Avise que o cupom de 5% OFF já está disponível e convide seu amigo a participar do Indique e Ganhe.
                        </p>
                        <form method="POST" action="<?= url('/indicacoes/' . (int) $share['indicacao_id'] . '/compartilhar-beneficio') ?>">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn--block btn--primary">
                                🎁 Entregar benefício ao amigo
                            </button>
                        </form>
                        <p class="beneficio-amigo-card__hint">
                            Enviar para o WhatsApp final <?= e((string) $share['phone_tail']) ?>
                        </p>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
</div>
