<?php include __DIR__ . '/layout/header.php'; ?>
<?php include __DIR__ . '/layout/sidebar.php'; ?>

<h2 class="page-title"><?= t('dashboard') ?></h2>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card color-blue">
        <div class="stat-icon">🎓</div>
        <div class="stat-label"><?= t('students') ?></div>
        <span class="stat-value"><?= $studentsCount ?? 0 ?></span>
    </div>
    <div class="stat-card color-teal">
        <div class="stat-icon">👨‍🏫</div>
        <div class="stat-label"><?= t('professors') ?></div>
        <span class="stat-value"><?= $professorsCount ?? 0 ?></span>
    </div>
    <div class="stat-card color-green">
        <div class="stat-icon">📚</div>
        <div class="stat-label"><?= t('courses') ?></div>
        <span class="stat-value"><?= $coursesCount ?? 0 ?></span>
    </div>
    <div class="stat-card color-amber">
        <div class="stat-icon">📅</div>
        <div class="stat-label"><?= currentLang()==='ar' ? 'الفصول' : (currentLang()==='fr' ? 'Semestres' : 'Semesters') ?></div>
        <span class="stat-value"><?= $semestersCount ?? 0 ?></span>
    </div>
</div>

<!-- Active Semester + Chart -->
<div style="display:grid;grid-template-columns:1fr 2fr;gap:16px;margin:0 28px 24px;">

    <!-- Active Semester Info -->
    <section class="panel" style="display:flex;flex-direction:column;gap:12px;">
        <h3 style="margin:0 0 4px;">
            <?= currentLang()==='ar' ? '🟢 الفصل النشط' : (currentLang()==='fr' ? '🟢 Semestre actif' : '🟢 Active Semester') ?>
        </h3>
        <?php if ($activeSemester): ?>
            <div style="padding:16px;background:var(--primary-soft);border-radius:var(--radius);border:1.5px solid #c7d2fe;">
                <div style="font-size:22px;font-weight:800;color:var(--primary);letter-spacing:-.5px;">
                    <?= htmlspecialchars($activeSemester['label']) ?>
                    <span style="font-size:14px;font-weight:600;color:var(--muted);margin-<?= isRtl()?'right':'left' ?>:6px;">
                        <?= htmlspecialchars($activeSemester['year']) ?>
                    </span>
                </div>
                <div style="margin-top:8px;font-size:12px;color:var(--muted);">
                    <span class="badge badge-active">● <?= currentLang()==='ar'?'نشط':(currentLang()==='fr'?'Actif':'Active') ?></span>
                </div>
            </div>
        <?php else: ?>
            <div style="padding:16px;background:var(--line-2);border-radius:var(--radius);color:var(--muted);font-size:13px;">
                <?= currentLang()==='ar' ? 'لا يوجد فصل نشط' : (currentLang()==='fr' ? 'Aucun semestre actif' : 'No active semester') ?>
            </div>
        <?php endif; ?>

        <?php
        // Current semester live stats
        if ($activeSemester):
            $activeId = $activeSemester['id'];
            $activeEnrolled = $this->db ?? null;
            // quick counts
            $enrollCount = $this->db->prepare("SELECT COUNT(*) FROM enrollments WHERE semester_id=?");
            $enrollCount->execute([$activeId]);
            $enrollTotal = $enrollCount->fetchColumn();

            $gradeCount = $this->db->prepare("SELECT COUNT(DISTINCT g.student_id) FROM grades g JOIN assignments a ON g.assignment_id=a.id JOIN courses c ON a.course_id=c.id WHERE c.semester_id=?");
            $gradeCount->execute([$activeId]);
            $gradedTotal = $gradeCount->fetchColumn();
        ?>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:4px;">
            <div style="padding:12px;background:var(--surface-2);border-radius:var(--radius);border:1px solid var(--line);text-align:center;">
                <div style="font-size:20px;font-weight:800;color:var(--ink);"><?= $enrollTotal ?></div>
                <div style="font-size:11px;color:var(--muted);font-weight:600;margin-top:2px;">
                    <?= currentLang()==='ar'?'مسجل':(currentLang()==='fr'?'Inscrits':'Enrolled') ?>
                </div>
            </div>
            <div style="padding:12px;background:var(--surface-2);border-radius:var(--radius);border:1px solid var(--line);text-align:center;">
                <div style="font-size:20px;font-weight:800;color:var(--success);"><?= $gradedTotal ?></div>
                <div style="font-size:11px;color:var(--muted);font-weight:600;margin-top:2px;">
                    <?= currentLang()==='ar'?'لديهم نقاط':(currentLang()==='fr'?'Notés':'Graded') ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </section>

    <!-- GPA Chart per Semester -->
    <section class="panel">
        <h3 style="margin:0 0 14px;">
            <?= currentLang()==='ar' ? '📊 متوسط النقاط لكل فصل' : (currentLang()==='fr' ? '📊 Moyenne par semestre' : '📊 Average Score per Semester') ?>
        </h3>
        <?php if (!empty($semGpaData)): ?>
        <div class="chart-wrap">
            <canvas id="semGpaChart"></canvas>
        </div>
        <script>
        (function(){
            const labels = <?= json_encode(array_map(fn($r) => $r['label'].' '.$r['year'], $semGpaData)) ?>;
            const scores = <?= json_encode(array_map(fn($r) => (float)$r['avg_score'], $semGpaData)) ?>;
            const enrolled = <?= json_encode(array_map(fn($r) => (int)$r['enrolled_count'], $semGpaData)) ?>;

            new Chart(document.getElementById('semGpaChart'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: '<?= currentLang()==="ar"?"متوسط النقاط":(currentLang()==="fr"?"Moy. Score":"Avg Score") ?>',
                            data: scores,
                            backgroundColor: scores.map(v =>
                                v >= 14 ? 'rgba(16,185,129,.75)' :
                                v >= 10 ? 'rgba(79,70,229,.75)' :
                                          'rgba(239,68,68,.75)'
                            ),
                            borderColor: scores.map(v =>
                                v >= 14 ? '#10b981' : v >= 10 ? '#4f46e5' : '#ef4444'
                            ),
                            borderWidth: 2,
                            borderRadius: 8,
                            borderSkipped: false,
                            yAxisID: 'y'
                        },
                        {
                            label: '<?= currentLang()==="ar"?"الطلاب":(currentLang()==="fr"?"Étudiants":"Students") ?>',
                            data: enrolled,
                            type: 'line',
                            borderColor: '#06b6d4',
                            backgroundColor: 'rgba(6,182,212,.1)',
                            borderWidth: 2.5,
                            pointBackgroundColor: '#06b6d4',
                            pointRadius: 5,
                            tension: .4,
                            fill: true,
                            yAxisID: 'y2'
                        }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { labels: { font: { family: 'Inter', size: 12 }, boxWidth: 14, padding: 16 } },
                        tooltip: {
                            callbacks: {
                                label: ctx => ctx.datasetIndex === 0
                                    ? ` ${ctx.parsed.y.toFixed(2)} / 20`
                                    : ` ${ctx.parsed.y} <?= currentLang()==="ar"?"طالب":(currentLang()==="fr"?"étudiant(s)":"student(s)") ?>`
                            }
                        }
                    },
                    scales: {
                        y:  { beginAtZero: true, max: 20, grid: { color: 'rgba(0,0,0,.05)' },
                              ticks: { font: { family: 'Inter', size: 11 } } },
                        y2: { position: 'right', beginAtZero: true, grid: { drawOnChartArea: false },
                              ticks: { font: { family: 'Inter', size: 11 } } },
                        x:  { grid: { display: false }, ticks: { font: { family: 'Inter', size: 12 } } }
                    }
                }
            });
        })();
        </script>
        <?php else: ?>
            <div style="display:grid;place-items:center;height:200px;color:var(--muted);font-size:13px;">
                <div style="text-align:center;">
                    <div style="font-size:40px;margin-bottom:10px;">📉</div>
                    <?= currentLang()==='ar' ? 'لا توجد بيانات نقاط بعد' : (currentLang()==='fr' ? 'Aucune donnée de notes' : 'No grade data yet') ?>
                </div>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>
