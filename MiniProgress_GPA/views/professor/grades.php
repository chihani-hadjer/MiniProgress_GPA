<?php include __DIR__ . '/../admin/layout/header.php'; ?>
<?php include __DIR__ . '/../admin/layout/sidebar.php'; ?>

<h2 class="page-title"><?= t('enter_grades') ?></h2>

<!-- Semester Selector -->
<form class="panel mb-3" method="GET" action="index.php" style="display:flex;gap:12px;align-items:flex-end;">
    <input type="hidden" name="page" value="professor.grades">
    <input type="hidden" name="lang" value="<?= currentLang() ?>">
    <label style="flex:1"><?= currentLang()==='ar'?'اختر الفصل':'Select Semester' ?>
        <select name="semester_id" onchange="this.form.submit()">
            <option value=""><?= currentLang()==='ar'?'-- الكل --':'-- All --' ?></option>
            <?php foreach ($semesters as $s): ?>
            <option value="<?= $s['id'] ?>" <?= $selectedSemId == $s['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($s['label']) ?> - <?= htmlspecialchars($s['year']) ?> <?= $s['is_active'] ? '★' : '' ?>
            </option>
            <?php endforeach; ?>
        </select>
    </label>
</form>

<?php if (empty($myAssignments)): ?>
    <section class="panel"><p><?= t('no_grades') ?></p></section>
<?php else: ?>
<section class="panel">
    <div class="form-grid">
        <label><?= t('course_name') ?>
            <select id="assignment">
                <option value=""><?= t('load_students') ?></option>
                <?php foreach ($myAssignments as $a): ?>
                    <option value="<?= $a['id'] ?>">
                        <?= htmlspecialchars($a['course_name']) ?> (<?= (int)$a['credits'] ?> cr)
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <button id="load-students" type="button" class="btn btn-secondary"><?= t('load_students') ?></button>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr><th><?= t('name') ?></th><th><?= t('id') ?></th><th><?= t('score') ?> /20</th></tr>
            </thead>
            <tbody id="students"></tbody>
        </table>
    </div>

    <div class="actions" style="margin-top:16px;display:flex;gap:12px;align-items:center;">
        <button id="save" type="button" class="btn btn-primary"><?= t('save') ?></button>
        <button id="export-csv" type="button" class="btn btn-secondary" style="display:none"><?= t('export_csv') ?></button>
        <div id="msg"></div>
    </div>
</section>
<?php endif; ?>

<script>
window.MINIPROGRESS_I18N = {
    noStudents:  <?= json_encode(t('no_students')) ?>,
    loading:     <?= json_encode(t('loading')) ?>,
    saved:       <?= json_encode(t('saved')) ?>,
    loadError:   <?= json_encode(t('load_error')) ?>,
    serverError: <?= json_encode(t('server_error')) ?>
};
window.CURRENT_COURSE_NAME = '';
</script>
<script src="../public/js/professor.js"></script>

<?php include __DIR__ . '/../admin/layout/footer.php'; ?>
