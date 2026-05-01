<?php include __DIR__ . '/layout/header.php'; ?>
<?php include __DIR__ . '/layout/sidebar.php'; ?>

<h2 class="page-title"><?= t('semesters') ?></h2>

<section class="panel">
    <h3><?= $edit ? (currentLang()==='ar'?'تعديل الفصل':(currentLang()==='fr'?'Modifier':'Edit Semester')) : (currentLang()==='ar'?'إضافة فصل':(currentLang()==='fr'?'Ajouter':'Add Semester')) ?></h3>
    <form method="POST" action="<?= pageUrl($edit ? 'admin.saveSemester' : 'admin.saveSemester') ?>" class="form-grid">
        <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
        <label><?= currentLang()==='ar'?'التسمية':(currentLang()==='fr'?'Label':'Label') ?>
            <input type="text" name="label" value="<?= htmlspecialchars($edit['label'] ?? '') ?>" placeholder="S1, S2..." required>
        </label>
        <label><?= currentLang()==='ar'?'السنة':(currentLang()==='fr'?'Année':'Year') ?>
            <input type="text" name="year" value="<?= htmlspecialchars($edit['year'] ?? '') ?>" placeholder="2024-2025" required>
        </label>
        <div>
            <button class="btn btn-primary"><?= t('save') ?></button>
            <?php if ($edit): ?>
            <a href="<?= pageUrl('admin.semesters') ?>" class="btn btn-secondary"><?= currentLang()==='ar'?'إلغاء':(currentLang()==='fr'?'Annuler':'Cancel') ?></a>
            <?php endif; ?>
        </div>
    </form>
</section>

<div class="table-wrap">
    <table>
        <thead><tr>
            <th><?= currentLang()==='ar'?'التسمية':'Label' ?></th>
            <th><?= currentLang()==='ar'?'السنة':'Year' ?></th>
            <th><?= currentLang()==='ar'?'الحالة':'Status' ?></th>
            <th><?= t('actions') ?></th>
        </tr></thead>
        <tbody>
        <?php foreach ($semesters as $s): ?>
        <tr>
            <td><?= htmlspecialchars($s['label']) ?></td>
            <td><?= htmlspecialchars($s['year']) ?></td>
            <td>
                <?php if ($s['is_active']): ?>
                    <span class="badge badge-success"><?= currentLang()==='ar'?'نشط':(currentLang()==='fr'?'Actif':'Active') ?></span>
                <?php else: ?>
                    <span class="badge"><?= currentLang()==='ar'?'غير نشط':(currentLang()==='fr'?'Inactif':'Inactive') ?></span>
                <?php endif; ?>
            </td>
            <td class="actions">
                <a href="<?= pageUrl('admin.semesters', ['id' => $s['id']]) ?>" class="btn btn-secondary btn-sm"><?= currentLang()==='ar'?'تعديل':(currentLang()==='fr'?'Modifier':'Edit') ?></a>
                <?php if (!$s['is_active']): ?>
                <form method="POST" action="<?= pageUrl('admin.activateSemester') ?>" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $s['id'] ?>">
                    <button class="btn btn-primary btn-sm"><?= currentLang()==='ar'?'تفعيل':(currentLang()==='fr'?'Activer':'Activate') ?></button>
                </form>
                <form method="POST" action="<?= pageUrl('admin.deleteSemester') ?>" style="display:inline;"
                      onsubmit="return confirm('<?= currentLang()==='ar'?'حذف؟':'Delete?' ?>')">
                    <input type="hidden" name="id" value="<?= $s['id'] ?>">
                    <button class="btn btn-danger btn-sm"><?= t('delete') ?></button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>
