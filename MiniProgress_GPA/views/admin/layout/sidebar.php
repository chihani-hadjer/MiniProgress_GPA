<?php
$role = $_SESSION['user']['role'] ?? '';
$nav = [
    'admin' => [
        'admin.dashboard'   => 'dashboard',
        'admin.semesters'   => 'semesters',
        'admin.courses'     => 'courses',
        'admin.professors'  => 'professors',
        'admin.students'    => 'students',
        'admin.enrollments' => 'enrollments',
        'admin.assignments' => 'assignments',
    ],
    'professor' => [
        'professor.grades' => 'enter_grades',
    ],
    'student' => [
        'student.dashboard' => 'dashboard',
        'student.history'   => 'history',
    ],
];
$currentPage = $_GET['page'] ?? '';
?>
<div class="app-shell">
    <aside class="sidebar">
        <a class="brand" href="<?= pageUrl($role === 'admin' ? 'admin.dashboard' : ($role === 'professor' ? 'professor.grades' : 'student.dashboard')) ?>">
            <img class="brand-logo" src="../public/images/miniprogress-logo.png" alt="<?= t('app_name') ?>">
            <span>
                <strong><?= t('app_name') ?></strong>
                <small><?= t('app_subtitle') ?></small>
            </span>
        </a>

        <nav class="nav">
            <?php foreach (($nav[$role] ?? []) as $page => $label): ?>
                <a class="<?= $currentPage === $page ? 'active' : '' ?>" href="<?= pageUrl($page) ?>"><?= t($label) ?></a>
            <?php endforeach; ?>
            <a class="<?= $currentPage === 'changePassword' ? 'active' : '' ?>" href="<?= pageUrl('changePassword') ?>">
                <?= currentLang() === 'fr' ? 'Mot de passe' : (currentLang() === 'en' ? 'Password' : 'كلمة المرور') ?>
            </a>
        </nav>
    </aside>

    <main class="content">
        <header class="topbar">
            <div>
                <span class="eyebrow"><?= t('welcome') ?></span>
                <h1><?= htmlspecialchars($_SESSION['user']['name'] ?? t('app_name')) ?></h1>
            </div>
            <div class="top-actions">
                <div class="lang-switch">
                    <a class="<?= currentLang() === 'ar' ? 'active' : '' ?>" href="<?= langUrl('ar') ?>">AR</a>
                    <a class="<?= currentLang() === 'fr' ? 'active' : '' ?>" href="<?= langUrl('fr') ?>">FR</a>
                    <a class="<?= currentLang() === 'en' ? 'active' : '' ?>" href="<?= langUrl('en') ?>">EN</a>
                </div>
                <a class="btn btn-secondary" href="<?= pageUrl('logout') ?>"><?= t('logout') ?></a>
            </div>
        </header>

        <?php renderFlash(); ?>
        <?php foreach (['error', 'warning', 'success'] as $flashKey): ?>
            <?php if (!empty($_SESSION[$flashKey])): ?>
                <div class="alert alert-<?= $flashKey === 'error' ? 'error' : 'success' ?>">
                    <?= htmlspecialchars($_SESSION[$flashKey]) ?>
                </div>
                <?php unset($_SESSION[$flashKey]); ?>
            <?php endif; ?>
        <?php endforeach; ?>
