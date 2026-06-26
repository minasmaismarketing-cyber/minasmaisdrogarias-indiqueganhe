<?php if (!empty($errors['_form'])): ?>
    <div class="alert alert--error"><?= e($errors['_form']) ?></div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert--success"><?= e($success) ?></div>
<?php endif; ?>
