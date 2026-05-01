<!DOCTYPE html>
<html lang="<?= currentLang() ?>" dir="<?= isRtl() ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('login_title') ?> — <?= t('app_name') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../public/css/style.css">
    <style>
    /* Floating particles */
    .particles { position: fixed; inset: 0; z-index: 0; overflow: hidden; pointer-events: none; }
    .particle {
        position: absolute; border-radius: 50%;
        background: rgba(165,180,252,.3);
        animation: float linear infinite;
    }
    @keyframes float {
        0%   { transform: translateY(110vh) translateX(0) scale(1); opacity: 0; }
        10%  { opacity: 1; }
        90%  { opacity: 1; }
        100% { transform: translateY(-10vh) translateX(40px) scale(.6); opacity: 0; }
    }
    .login-divider {
        display: flex; align-items: center; gap: 12px; margin: 4px 0;
        color: rgba(255,255,255,.3); font-size: 12px;
    }
    .login-divider::before, .login-divider::after {
        content: ''; flex: 1; height: 1px; background: rgba(255,255,255,.12);
    }
    </style>
</head>
<body class="login-page">
    <!-- Animated particles -->
    <div class="particles" id="particles"></div>

    <section class="panel login-card">
        <!-- Lang switch -->
        <div style="display:flex;justify-content:flex-end;margin-bottom:20px;">
            <div class="lang-switch" style="background:rgba(255,255,255,.08);border-color:rgba(255,255,255,.15);">
                <a class="<?= currentLang() === 'ar' ? 'active' : '' ?>" href="<?= langUrl('ar') ?>">AR</a>
                <a class="<?= currentLang() === 'fr' ? 'active' : '' ?>" href="<?= langUrl('fr') ?>">FR</a>
                <a class="<?= currentLang() === 'en' ? 'active' : '' ?>" href="<?= langUrl('en') ?>">EN</a>
            </div>
        </div>

        <!-- Brand -->
        <div class="brand">
            <img class="brand-logo login-logo" src="../public/images/miniprogress-logo.png" alt="<?= t('app_name') ?>">
            <span>
                <strong><?= t('app_name') ?></strong>
                <small><?= t('app_subtitle') ?></small>
            </span>
        </div>

        <h1><?= t('login_title') ?></h1>

        <?php $flash = getFlash(); ?>
        <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] === 'error' ? 'error' : 'success' ?>" style="margin:0 0 14px;">
                <?= htmlspecialchars($flash['msg']) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= pageUrl('doLogin') ?>">
            <label>
                <?= t('email') ?>
                <input type="email" name="email" required autofocus autocomplete="email"
                       placeholder="you@example.com">
            </label>
            <label>
                <?= t('password') ?>
                <input type="password" name="password" required autocomplete="current-password"
                       placeholder="••••••••">
            </label>
            <button type="submit"><?= t('sign_in') ?></button>
        </form>

        <p style="margin-top:20px;font-size:12px;color:rgba(255,255,255,.3);text-align:center;">
            <?= currentLang()==='ar' ? 'نظام إدارة النقاط الأكاديمية' : (currentLang()==='fr' ? 'Système de gestion des notes' : 'Academic Grade Management System') ?>
        </p>
    </section>

    <script>
    // Generate floating particles
    const container = document.getElementById('particles');
    for (let i = 0; i < 22; i++) {
        const p = document.createElement('div');
        p.className = 'particle';
        const size = Math.random() * 8 + 3;
        p.style.cssText = `
            width:${size}px; height:${size}px;
            left:${Math.random() * 100}%;
            animation-duration:${Math.random() * 12 + 8}s;
            animation-delay:${Math.random() * 10}s;
            background: rgba(${Math.random()>0.5?'165,180,252':'6,182,212'},.25);
        `;
        container.appendChild(p);
    }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
