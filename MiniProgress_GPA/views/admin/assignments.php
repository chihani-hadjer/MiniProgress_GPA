<?php include __DIR__ . '/layout/header.php'; ?>
<?php include __DIR__ . '/layout/sidebar.php'; ?>

<h2 class="page-title"><?= t('assignments') ?></h2>

<!-- Semester Filter -->
<form class="panel mb-3" method="GET" action="index.php" style="display:flex;gap:12px;align-items:flex-end;">
    <input type="hidden" name="page" value="admin.assignments">
    <input type="hidden" name="lang" value="<?= currentLang() ?>">
    <label style="flex:1"><?= currentLang()==='ar'?'الفصل':'Semester' ?>
        <select name="semester_id" onchange="this.form.submit()">
            <option value=""><?= currentLang()==='ar'?'-- الكل --':'-- All --' ?></option>
            <?php foreach ($semesters as $s): ?>
            <option value="<?= $s['id'] ?>" <?= $semId == $s['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($s['label']) ?> - <?= htmlspecialchars($s['year']) ?> <?= $s['is_active'] ? '★' : '' ?>
            </option>
            <?php endforeach; ?>
        </select>
    </label>
</form>

<section class="panel">
    <h3><?= $edit ? (currentLang()==='ar'?'تعديل التعيين':'Edit Assignment') : (currentLang()==='ar'?'تعيين جديد':'New Assignment') ?></h3>
    <form method="POST" action="<?= pageUrl('admin.saveAssignment') ?>" class="form-grid">
        <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
        <input type="hidden" name="semester_id" value="<?= $semId ?>">
        <label><?= t('professor') ?>
            <select name="professor_id" required>
                <option value=""><?= currentLang()==='ar'?'اختر...':'Choose...' ?></option>
                <?php foreach ($professors as $p): ?>
                <option value="<?= $p['id'] ?>" <?= ($edit['professor_id'] ?? '') == $p['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label><?= t('course_name') ?>
            <select name="course_id" required>
                <option value=""><?= currentLang()==='ar'?'اختر...':'Choose...' ?></option>
                <?php foreach ($courses as $c): ?>
                <option value="<?= $c['id'] ?>" <?= ($edit['course_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </label>
        <div>
            <button class="btn btn-primary"><?= $edit ? t('save') : t('assign') ?></button>
            <?php if ($edit): ?>
            <a href="<?= pageUrl('admin.assignments', ['semester_id' => $semId]) ?>" class="btn btn-secondary"><?= currentLang()==='ar'?'إلغاء':'Cancel' ?></a>
            <?php endif; ?>
        </div>
    </form>
</section>

<div class="table-wrap">
    <table>
        <thead><tr>
            <th><?= t('professor') ?></th>
            <th><?= t('course_name') ?></th>
            <th><?= t('credits') ?></th>
            <th><?= t('actions') ?></th>
        </tr></thead>
        <tbody>
        <?php if (empty($assignments)): ?>
            <tr><td colspan="4"><?= currentLang()==='ar'?'لا توجد تعيينات.':'No assignments.' ?></td></tr>
        <?php endif; ?>
        <?php foreach ($assignments as $a): ?>
        <tr>
            <td><?= htmlspecialchars($a['professor_name']) ?></td>
            <td><?= htmlspecialchars($a['course_name']) ?></td>
            <td><?= (int)$a['credits'] ?></td>
            <td class="actions">
                <a href="<?= pageUrl('admin.assignments', ['edit' => $a['id'], 'semester_id' => $semId]) ?>" class="btn btn-secondary btn-sm"><?= currentLang()==='ar'?'تعديل':'Edit' ?></a>
                <form method="POST" action="<?= pageUrl('admin.deleteAssignment') ?>" style="display:inline;"
                      onsubmit="return confirm('<?= currentLang()==='ar'?'حذف؟':'Delete?' ?>')">
                    <input type="hidden" name="id" value="<?= $a['id'] ?>">
                    <input type="hidden" name="semester_id" value="<?= $semId ?>">
                    <button class="btn btn-danger btn-sm"><?= t('delete') ?></button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>
