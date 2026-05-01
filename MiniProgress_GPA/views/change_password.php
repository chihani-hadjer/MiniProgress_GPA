<?php include __DIR__ . '/admin/layout/header.php'; ?>
<?php include __DIR__ . '/admin/layout/sidebar.php'; ?>

<h2 class="page-title"><?= currentLang() === 'fr' ? 'Changer le mot de passe' : (currentLang() === 'en' ? 'Change password' : 'تغيير كلمة المرور') ?></h2>

<form class="panel form-grid" method="POST" action="<?= pageUrl('doChangePassword') ?>">
    <label>
        <?= currentLang() === 'fr' ? 'Mot de passe actuel' : (currentLang() === 'en' ? 'Current password' : 'كلمة المرور الحالية') ?>
        <input type="password" name="current_password" required>
    </label>
    <label>
        <?= currentLang() === 'fr' ? 'Nouveau mot de passe' : (currentLang() === 'en' ? 'New password' : 'كلمة المرور الجديدة') ?>
        <input type="password" name="new_password" required minlength="6">
    </label>
    <button class="btn btn-primary"><?= t('save') ?></button>
</form>

<?php include __DIR__ . '/admin/layout/footer.php'; ?>
