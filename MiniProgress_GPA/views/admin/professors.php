<?php include __DIR__ . '/layout/header.php'; ?>
<?php include __DIR__ . '/layout/sidebar.php'; ?>

<h2 class="page-title"><?= t('professors') ?></h2>

<form class="panel mb-3" method="GET" action="index.php">
    <input type="hidden" name="page" value="admin.professors">
    <input type="hidden" name="lang" value="<?= currentLang() ?>">
    <div class="input-group">
        <input class="form-control" name="q" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="<?= t('name') ?> / <?= t('email') ?>">
        <button class="btn btn-primary"><?= currentLang()==='ar'?'بحث':(currentLang()==='fr'?'Rechercher':'Search') ?></button>
    </div>
</form>

<section class="panel">
    <h3><?= $edit ? (currentLang()==='ar'?'تعديل الأستاذ':'Edit Professor') : (currentLang()==='ar'?'إضافة أستاذ':'Add Professor') ?></h3>
    <form class="form-grid" method="POST" action="<?= pageUrl('admin.saveProfessor') ?>">
        <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
        <label><?= t('name') ?><input type="text" name="name" value="<?= htmlspecialchars($edit['name'] ?? '') ?>" required></label>
        <label><?= t('email') ?><input type="email" name="email" value="<?= htmlspecialchars($edit['email'] ?? '') ?>" required></label>
        <?php if (!$edit): ?>
        <label><?= t('password') ?><input type="password" name="password" required></label>
        <?php endif; ?>
        <div>
            <button class="btn btn-primary"><?= t('save') ?></button>
            <?php if ($edit): ?>
            <a href="<?= pageUrl('admin.professors') ?>" class="btn btn-secondary"><?= currentLang()==='ar'?'إلغاء':'Cancel' ?></a>
            <?php endif; ?>
        </div>
    </form>
</section>

<div class="table-wrap">
    <table>
        <tr><th><?= t('name') ?></th><th><?= t('email') ?></th><th><?= t('actions') ?></th></tr>
        <?php foreach ($professors ?? [] as $p): ?>
        <tr>
            <td><?= htmlspecialchars($p['name']) ?></td>
            <td><?= htmlspecialchars($p['email']) ?></td>
            <td class="actions">
                <a href="<?= pageUrl('admin.professors', ['edit' => $p['id']]) ?>" class="btn btn-secondary btn-sm"><?= currentLang()==='ar'?'تعديل':'Edit' ?></a>
                <form method="POST" action="<?= pageUrl('admin.deleteProfessor') ?>" style="display:inline;" onsubmit="return confirm('Delete?')">
                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                    <button class="btn btn-danger btn-sm"><?= t('delete') ?></button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php $pages = max(1, ceil(($total ?? 0) / ($perPage ?? 10))); ?>
<nav class="mt-3"><ul class="pagination">
<?php for ($i = 1; $i <= $pages; $i++): ?>
    <li class="page-item <?= $i === ($page ?? 1) ? 'active' : '' ?>">
        <a class="page-link" href="<?= pageUrl('admin.professors', ['p' => $i, 'q' => $search ?? '']) ?>"><?= $i ?></a>
    </li>
<?php endfor; ?>
</ul></nav>

<?php include __DIR__ . '/layout/footer.php'; ?>
