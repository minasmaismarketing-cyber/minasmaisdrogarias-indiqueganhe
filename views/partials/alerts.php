<?php if (!empty($errors['_form'])): ?>
    <div class="alert alert--error" role="alert"><?= e($errors['_form']) ?></div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert--success" role="status"><?= e($success) ?></div>
<?php endif; ?>
