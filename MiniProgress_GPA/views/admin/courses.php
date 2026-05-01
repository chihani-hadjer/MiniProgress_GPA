<?php include __DIR__ . '/layout/header.php'; ?>
<?php include __DIR__ . '/layout/sidebar.php'; ?>

<h2 class="page-title"><?= t('courses') ?></h2>


<section class="panel">
    <h3><?= $edit ? (currentLang()==='ar'?'تعديل المقرر':'Edit Course') : (currentLang()==='ar'?'إضافة مقرر':'Add Course') ?></h3>
    <form method="POST" action="<?= pageUrl('admin.saveCourse') ?>" class="form-grid">
        <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
        <label><?= t('course_name') ?><input type="text" name="name" value="<?= htmlspecialchars($edit['name'] ?? '') ?>" required></label>
        <label><?= t('credits') ?><input type="number" name="credits" value="<?= (int)($edit['credits'] ?? 1) ?>" min="1" required></label>
        <label><?= currentLang()==='ar'?'الفصل':'Semester' ?>
            <select name="semester_id" required>
                <option value=""><?= currentLang()==='ar'?'اختر...':'Choose...' ?></option>
                <?php foreach ($semesters as $s): ?>
                <option value="<?= $s['id'] ?>"
                    <?= (($edit['semester_id'] ?? $filterSemId) == $s['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($s['label']) ?> - <?= htmlspecialchars($s['year']) ?>
                    <?= $s['is_active'] ? ' ★' : '' ?>
                </option>
                <?php endforeach; ?>
            </select>
        </label>
        <div>
            <button class="btn btn-primary"><?= $edit ? t('save') : (currentLang()==='ar'?'إضافة':'Add') ?></button>
            <?php if ($edit): ?>
            <a href="<?= pageUrl('admin.courses', ['semester_id' => $filterSemId]) ?>" class="btn btn-secondary"><?= currentLang()==='ar'?'إلغاء':'Cancel' ?></a>
            <?php endif; ?>
        </div>
    </form>
</section>

<div class="table-wrap">
    <table>
        <thead><tr>
            <th><?= t('course_name') ?></th>
            <th><?= t('credits') ?></th>
            <th><?= t('actions') ?></th>
        </tr></thead>
        <tbody>
        <?php if (empty($courses)): ?>
            <tr><td colspan="3"><?= currentLang()==='ar'?'لا توجد مقررات لهذا الفصل.':'No courses for this semester.' ?></td></tr>
        <?php endif; ?>
        <?php foreach ($courses as $c): ?>
        <tr>
            <td><?= htmlspecialchars($c['name']) ?></td>
            <td><?= (int)$c['credits'] ?></td>
            <td class="actions">
                <a href="<?= pageUrl('admin.courses', ['id' => $c['id'], 'semester_id' => $filterSemId]) ?>" class="btn btn-secondary btn-sm"><?= currentLang()==='ar'?'تعديل':'Edit' ?></a>
                <form method="POST" action="<?= pageUrl('admin.deleteCourse') ?>" style="display:inline;"
                      onsubmit="return confirm('<?= currentLang()==='ar'?'حذف؟':'Delete?' ?>')">
                    <input type="hidden" name="id" value="<?= $c['id'] ?>">
                    <button class="btn btn-danger btn-sm"><?= t('delete') ?></button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>
