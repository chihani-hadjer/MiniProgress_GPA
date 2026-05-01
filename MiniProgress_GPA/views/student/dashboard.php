<?php include __DIR__ . '/../admin/layout/header.php'; ?>
<?php include __DIR__ . '/../admin/layout/sidebar.php'; ?>

<h2 class="page-title"><?= t('transcript') ?></h2>

<?php if (!$activeSemester): ?>
    <section class="panel" style="margin:0 28px;">
        <p style="color:var(--muted);">
            <?= currentLang()==='ar' ? 'لا يوجد فصل نشط حالياً.' : (currentLang()==='fr' ? 'Aucun semestre actif.' : 'No active semester.') ?>
        </p>
    </section>
<?php elseif (empty($grades)): ?>
    <section class="panel" style="margin:0 28px;">
        <div style="display:flex;align-items:center;gap:12px;">
            <span style="font-size:32px;">📋</span>
            <div>
                <div style="font-weight:600;color:var(--ink);">
                    <?= htmlspecialchars($activeSemester['label']) ?> — <?= htmlspecialchars($activeSemester['year']) ?>
                </div>
                <div style="font-size:13px;color:var(--muted);margin-top:2px;">
                    <?= currentLang()==='ar' ? 'لا توجد مواد مسجلة في هذا الفصل.' : (currentLang()==='fr' ? 'Aucun cours pour ce semestre.' : 'No courses enrolled in this semester.') ?>
                </div>
            </div>
        </div>
    </section>
<?php else: ?>

<div style="margin:0 28px 24px;">
    <!-- Semester Header -->
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:18px;">
        <div>
            <div style="font-size:13px;font-weight:700;color:var(--primary);text-transform:uppercase;letter-spacing:.7px;">
                <?= currentLang()==='ar' ? 'الفصل الحالي' : (currentLang()==='fr' ? 'Semestre actuel' : 'Current Semester') ?>
            </div>
            <div style="font-size:20px;font-weight:800;color:var(--ink);margin-top:2px;letter-spacing:-.4px;">
                <?= htmlspecialchars($activeSemester['label']) ?>
                <span style="color:var(--muted);font-weight:500;font-size:16px;margin-<?= isRtl()?'right':'left' ?>:6px;">
                    <?= htmlspecialchars($activeSemester['year']) ?>
                </span>
            </div>
        </div>
        <span class="badge badge-active">● <?= currentLang()==='ar'?'نشط':(currentLang()==='fr'?'Actif':'Active') ?></span>
    </div>

    <!-- Courses List -->
    <section class="panel">
        <h3>
            <?= currentLang()==='ar' ? 'نقاط المواد' : (currentLang()==='fr' ? 'Notes des matières' : 'Course Scores') ?>
        </h3>

        <div class="course-list">
            <?php foreach ($grades as $g): ?>
            <?php
                $score = $g['score'];
                $pillClass = 'pending';
                $scoreLabel = currentLang()==='ar' ? 'قيد الانتظار' : (currentLang()==='fr' ? 'En attente' : 'Pending');
                if ($score !== null) {
                    $scoreLabel = number_format((float)$score, 2) . ' / 20';
                    if ($score >= 14)     $pillClass = 'high';
                    elseif ($score >= 10) $pillClass = 'mid';
                    else                  $pillClass = 'low';
                }
            ?>
            <div class="course-row">
                <div>
                    <div class="course-name"><?= htmlspecialchars($g['course_name']) ?></div>
                    <div class="course-meta">
                        👨‍🏫 <?= htmlspecialchars($g['professor_name']) ?>
                        &nbsp;·&nbsp;
                        <?= (int)$g['credits'] ?> <?= currentLang()==='ar' ? 'وحدات' : (currentLang()==='fr' ? 'crédits' : 'credits') ?>
                    </div>
                </div>
                <span class="score-pill <?= $pillClass ?>">
                    <?= $scoreLabel ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Credits Summary only (no GPA here) -->
        <?php
            $totalCredits = 0;
            $earnedCredits = 0;
            foreach ($grades as $g) {
                $totalCredits += (int)$g['credits'];
                if ($g['score'] !== null && $g['score'] >= 10) {
                    $earnedCredits += (int)$g['credits'];
                }
            }
        ?>
        <div style="margin-top:20px;padding-top:16px;border-top:1px solid var(--line);display:flex;gap:20px;flex-wrap:wrap;">
            <div style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--muted);">
                <span style="font-weight:700;font-size:16px;color:var(--ink);"><?= $totalCredits ?></span>
                <?= currentLang()==='ar' ? 'إجمالي الوحدات' : (currentLang()==='fr' ? 'Total crédits' : 'Total credits') ?>
            </div>
            <div style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--muted);">
                <span style="font-weight:700;font-size:16px;color:var(--success);"><?= $earnedCredits ?></span>
                <?= currentLang()==='ar' ? 'وحدات مكتسبة' : (currentLang()==='fr' ? 'Crédits acquis' : 'Earned credits') ?>
            </div>
            <?php
            $gradedCount = count(array_filter($grades, fn($g) => $g['score'] !== null));
            $totalCount  = count($grades);
            ?>
            <div style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--muted);">
                <span style="font-weight:700;font-size:16px;color:var(--primary);"><?= $gradedCount ?>/<?= $totalCount ?></span>
                <?= currentLang()==='ar' ? 'مواد منقطة' : (currentLang()==='fr' ? 'Matières notées' : 'Graded courses') ?>
            </div>
        </div>

        <?php if ($gradedCount < $totalCount): ?>
        <div style="margin-top:12px;">
            <div class="alert alert-info" style="margin:0;font-size:12.5px;">
                ℹ️ <?= currentLang()==='ar' ? 'لم تُدخَل بعض النقاط بعد. ارجع لاحقاً.' : (currentLang()==='fr' ? 'Certaines notes ne sont pas encore saisies.' : 'Some grades have not been entered yet.') ?>
            </div>
        </div>
        <?php endif; ?>
    </section>

    <div style="margin-top:14px;text-align:end;">
        <a href="<?= pageUrl('student.history') ?>" class="btn btn-ghost btn-sm">
            <?= currentLang()==='ar' ? 'عرض السجل الكامل والمعدل ←' : (currentLang()==='fr' ? 'Voir historique complet →' : 'View full history & GPA →') ?>
        </a>
    </div>
</div>

<?php endif; ?>

<?php include __DIR__ . '/../admin/layout/footer.php'; ?>
