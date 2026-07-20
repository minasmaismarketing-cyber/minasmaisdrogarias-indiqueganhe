<?php declare(strict_types=1); ?>
<?php
$subtitle = 'Importe cupons CSV para o benefício de 10% do indicador.';
$resumo = is_array($pending['resumo'] ?? null) ? $pending['resumo'] : null;
$campanhaSelecionada = (int) ($pending['campanha_id'] ?? 0);
?>

<section class="admin-detail-card" style="margin-bottom: 1.5rem;">
    <p>Os cupons importados ficam no estoque da campanha como <strong>disponíveis</strong> e são destinados ao <strong>indicador (10% OFF)</strong>. O benefício de 5% do indicado permanece no fluxo já existente.</p>
</section>

<form method="POST" action="<?= url('/admin/cupons/importar/validar') ?>" enctype="multipart/form-data" class="form" style="max-width: 40rem;">
    <?= csrf_field() ?>
    <div class="form-group">
        <label for="campanha_id">Campanha</label>
        <select id="campanha_id" name="campanha_id" required>
            <option value="">Selecione</option>
            <?php foreach ($campanhas as $campanha): ?>
                <option
                    value="<?= (int) $campanha['id'] ?>"
                    <?= $campanhaSelecionada === (int) $campanha['id'] ? 'selected' : '' ?>
                ><?= e((string) $campanha['nome']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label for="arquivo">Arquivo CSV (UTF-8, máx. 2 MB)</label>
        <input type="file" id="arquivo" name="arquivo" accept=".csv,text/csv" required>
        <small>Uma coluna por linha. Cabeçalhos opcionais: codigo, cupom ou code. Percentual fixo: 10%.</small>
    </div>
    <div class="form-actions" style="display:flex; gap:0.75rem; flex-wrap:wrap;">
        <a href="<?= url('/admin/cupons') ?>" class="btn btn--ghost">Voltar</a>
        <button type="submit" class="btn btn--primary">Validar arquivo</button>
    </div>
</form>

<?php if ($resumo !== null): ?>
    <section class="admin-stats stats-grid" style="margin-top: 2rem;">
        <article class="stat-card">
            <span class="stat-card__value"><?= (int) ($resumo['total_linhas'] ?? 0) ?></span>
            <span class="stat-card__label">Total de linhas</span>
        </article>
        <article class="stat-card">
            <span class="stat-card__value"><?= (int) ($resumo['validos'] ?? 0) ?></span>
            <span class="stat-card__label">Códigos válidos</span>
        </article>
        <article class="stat-card">
            <span class="stat-card__value"><?= (int) ($resumo['duplicados_arquivo'] ?? 0) ?></span>
            <span class="stat-card__label">Duplicados no arquivo</span>
        </article>
        <article class="stat-card">
            <span class="stat-card__value"><?= (int) ($resumo['existentes_banco'] ?? 0) ?></span>
            <span class="stat-card__label">Já existentes no banco</span>
        </article>
        <article class="stat-card">
            <span class="stat-card__value"><?= (int) ($resumo['invalidos'] ?? 0) ?></span>
            <span class="stat-card__label">Inválidos</span>
        </article>
        <article class="stat-card">
            <span class="stat-card__value"><?= (int) ($resumo['a_importar'] ?? 0) ?></span>
            <span class="stat-card__label">Serão importados</span>
        </article>
    </section>

    <?php if ((int) ($resumo['a_importar'] ?? 0) > 0): ?>
        <form
            method="POST"
            action="<?= url('/admin/cupons/importar/confirmar') ?>"
            class="form"
            style="margin-top: 1.5rem;"
            onsubmit="return confirm('Confirmar importação dos cupons validados?');"
        >
            <?= csrf_field() ?>
            <button type="submit" class="btn btn--primary">
                Confirmar importação
            </button>
        </form>
    <?php else: ?>
        <p style="margin-top: 1.5rem;">Nenhum código novo para importar.</p>
    <?php endif; ?>
<?php endif; ?>
