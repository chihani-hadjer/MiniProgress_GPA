<?php include __DIR__ . '/layout/header.php'; ?>
<?php include __DIR__ . '/layout/sidebar.php'; ?>

<h2 class="page-title"><?= t('enrollments') ?></h2>

<!-- Semester Selector -->
<div style="margin:0 28px 18px;">
    <form class="panel" method="GET" action="index.php"
          style="display:flex;gap:14px;align-items:flex-end;padding:16px 20px;">
        <input type="hidden" name="page" value="admin.enrollments">
        <input type="hidden" name="lang" value="<?= currentLang() ?>">
        <label style="flex:1;max-width:360px;">
            <?= currentLang()==='ar' ? 'اختر الفصل' : (currentLang()==='fr' ? 'Sélectionner le semestre' : 'Select Semester') ?>
            <select name="semester_id" onchange="this.form.submit()">
                <option value=""><?= currentLang()==='ar' ? '-- اختر --' : (currentLang()==='fr' ? '-- Choisir --' : '-- Choose --') ?></option>
                <?php foreach ($semesters as $s): ?>
                <option value="<?= $s['id'] ?>" <?= $semId == $s['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($s['label']) ?> — <?= htmlspecialchars($s['year']) ?>
                    <?= $s['is_active'] ? ' ★' : '' ?>
                </option>
                <?php endforeach; ?>
            </select>
        </label>
        <?php if ($semId): ?>
        <div style="padding:10px 16px;background:var(--primary-soft);border-radius:var(--radius);border:1px solid #c7d2fe;font-size:13px;color:var(--primary-dark);font-weight:600;white-space:nowrap;">
            <?= count($enrolledIds) ?> / <?= count($allStudents) ?>
            <?= currentLang()==='ar' ? ' مسجل' : (currentLang()==='fr' ? ' inscrits' : ' enrolled') ?>
        </div>
        <?php endif; ?>
    </form>
</div>

<?php if ($semId): ?>
<div style="margin:0 28px 28px;">
    <form method="POST" action="<?= pageUrl('admin.saveEnrollments') ?>">
        <input type="hidden" name="semester_id" value="<?= $semId ?>">
        <section class="panel">
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:16px;">
                <h3 style="margin:0;">
                    <?= currentLang()==='ar' ? '👥 الطلاب' : (currentLang()==='fr' ? '👥 Étudiants' : '👥 Students') ?>
                </h3>

            </div>

            <!-- Select All checkbox bar -->
            <label class="select-all-bar" for="selectAllCb">
                <input type="checkbox" id="selectAllCb" onchange="toggleAll(this.checked)">
                <span id="selectAllLabel">
                    <?= currentLang()==='ar' ? 'تحديد / إلغاء تحديد جميع الطلاب' : (currentLang()==='fr' ? 'Sélectionner / Désélectionner tout' : 'Select / Deselect All Students') ?>
                </span>
            </label>

            <!-- Search filter -->
            <div style="margin-bottom:12px;">
                <input type="text" id="studentSearch" placeholder="<?= currentLang()==='ar' ? 'ابحث باسم أو بريد...' : (currentLang()==='fr' ? 'Chercher par nom ou email...' : 'Search by name or email...') ?>"
                       oninput="filterStudents(this.value)"
                       style="max-width:340px;">
            </div>

            <div class="student-checkbox-grid" id="studentGrid">
                <?php foreach ($allStudents as $s): ?>
                <?php $isEnrolled = in_array($s['id'], $enrolledIds); ?>
                <label class="student-checkbox-item <?= $isEnrolled ? 'is-enrolled' : '' ?>"
                       data-name="<?= strtolower(htmlspecialchars($s['name'])) ?>"
                       data-email="<?= strtolower(htmlspecialchars($s['email'])) ?>">
                    <input type="checkbox" name="student_ids[]" value="<?= $s['id'] ?>"
                           class="student-cb"
                           <?= $isEnrolled ? 'checked' : '' ?>
                           onchange="updateLabel(this)">
                    <div class="student-info">
                        <strong><?= htmlspecialchars($s['name']) ?></strong>
                        <small><?= htmlspecialchars($s['email']) ?></small>
                    </div>
                </label>
                <?php endforeach; ?>
            </div>

            <?php if (empty($allStudents)): ?>
            <div style="text-align:center;padding:32px;color:var(--muted);">
                <?= currentLang()==='ar' ? 'لا يوجد طلاب مسجلون.' : (currentLang()==='fr' ? 'Aucun étudiant.' : 'No students found.') ?>
            </div>
            <?php endif; ?>

            <div style="margin-top:18px;padding-top:16px;border-top:1px solid var(--line);display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                <button class="btn btn-primary"><?= t('save') ?></button>
                <span id="enrollCount" style="font-size:13px;color:var(--muted);font-weight:600;"></span>
            </div>
        </section>
    </form>
</div>

<script>
function updateCount() {
    const total    = document.querySelectorAll('.student-cb').length;
    const checked  = document.querySelectorAll('.student-cb:checked').length;
    const countEl  = document.getElementById('enrollCount');
    const allCb    = document.getElementById('selectAllCb');
    if (countEl)  countEl.textContent = checked + ' / ' + total + ' <?= currentLang()==='ar' ? 'محدد' : (currentLang()==='fr' ? 'sélectionné(s)' : 'selected') ?>';
    if (allCb)    allCb.indeterminate = checked > 0 && checked < total;
    if (allCb)    allCb.checked = checked === total && total > 0;

    document.querySelectorAll('.student-checkbox-item').forEach(item => {
        const cb = item.querySelector('.student-cb');
        item.classList.toggle('is-enrolled', cb && cb.checked);
    });
}

function toggleAll(state) {
    document.querySelectorAll('.student-cb').forEach(cb => { cb.checked = state; });
    document.getElementById('selectAllCb').checked     = state;
    document.getElementById('selectAllCb').indeterminate = false;
    updateCount();
}

function filterStudents(q) {
    q = q.toLowerCase();
    document.querySelectorAll('.student-checkbox-item').forEach(item => {
        const match = item.dataset.name.includes(q) || item.dataset.email.includes(q);
        item.style.display = match ? '' : 'none';
    });
}

function updateLabel(cb) { updateCount(); }

// Init
document.addEventListener('DOMContentLoaded', () => {
    updateCount();
    document.querySelectorAll('.student-cb').forEach(cb => cb.addEventListener('change', updateCount));
});
</script>

<?php else: ?>
<div style="margin:0 28px;">
    <section class="panel">
        <div style="text-align:center;padding:32px;color:var(--muted);">
            <div style="font-size:48px;margin-bottom:12px;">📋</div>
            <div style="font-weight:600;">
                <?= currentLang()==='ar' ? 'اختر فصلاً لإدارة التسجيلات.' : (currentLang()==='fr' ? 'Sélectionnez un semestre.' : 'Select a semester to manage enrollments.') ?>
            </div>
        </div>
    </section>
</div>
<?php endif; ?>

<?php include __DIR__ . '/layout/footer.php'; ?>
