<?php include __DIR__ . '/../admin/layout/header.php'; ?>
<?php include __DIR__ . '/../admin/layout/sidebar.php'; ?>

<h2 class="page-title"><?= t('history') ?></h2>

<?php if (empty($grouped)): ?>
    <section class="panel" style="margin:0 28px;">
        <div style="text-align:center;padding:32px;color:var(--muted);">
            <div style="font-size:48px;margin-bottom:12px;">📂</div>
            <div style="font-weight:600;"><?= t('no_history') ?></div>
        </div>
    </section>
<?php else: ?>

<?php
// Build chart data
$chartLabels = [];
$chartGpas   = [];
$overallWeighted = 0;
$overallCredits  = 0;
foreach ($grouped as $semData) {
    $tw = 0; $tc = 0;
    foreach ($semData['grades'] as $g) {
        if ($g['score'] !== null) {
            $tw += $g['score'] * $g['credits'];
            $tc += $g['credits'];
        }
    }
    if ($tc > 0) {
        $gpa = round($tw / $tc, 2);
        $chartLabels[] = $semData['semester_label'].' '.$semData['semester_year'];
        $chartGpas[]   = $gpa;
        $overallWeighted += $tw;
        $overallCredits  += $tc;
    }
}
$overallGpa = $overallCredits > 0 ? round($overallWeighted / $overallCredits, 2) : null;
?>

<!-- Overall GPA Banner -->
<?php if ($overallGpa !== null): ?>
<div style="margin:0 28px 20px;">
    <section class="panel" style="background:linear-gradient(135deg, #1e1b4b, #4338ca);border:none;color:#fff;">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;">
            <div>
                <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;opacity:.7;margin-bottom:6px;">
                    <?= currentLang()==='ar' ? 'المعدل التراكمي' : (currentLang()==='fr' ? 'Moyenne cumulative' : 'Cumulative GPA') ?>
                </div>
                <div style="display:flex;align-items:baseline;gap:8px;">
                    <span style="font-size:48px;font-weight:900;letter-spacing:-2px;line-height:1;"><?= number_format($overallGpa, 2) ?></span>
                    <span style="font-size:20px;opacity:.6;">/ 20</span>
                </div>
                <div style="margin-top:8px;">
                    <?php
                    if ($overallGpa >= 16)      { $label = currentLang()==='ar'?'امتياز':(currentLang()==='fr'?'Très Bien':'Distinction'); $c = '#34d399'; }
                    elseif ($overallGpa >= 14)   { $label = currentLang()==='ar'?'جيد جداً':(currentLang()==='fr'?'Bien':'Very Good'); $c = '#6ee7b7'; }
                    elseif ($overallGpa >= 12)   { $label = currentLang()==='ar'?'جيد':(currentLang()==='fr'?'Assez Bien':'Good'); $c = '#fbbf24'; }
                    elseif ($overallGpa >= 10)   { $label = currentLang()==='ar'?'مقبول':(currentLang()==='fr'?'Passable':'Pass'); $c = '#fb923c'; }
                    else                          { $label = currentLang()==='ar'?'راسب':(currentLang()==='fr'?'Échec':'Fail'); $c = '#f87171'; }
                    ?>
                    <span style="background:rgba(255,255,255,.15);color:#fff;padding:4px 12px;border-radius:999px;font-size:12px;font-weight:700;">
                        <?= $label ?>
                    </span>
                </div>
            </div>
            <div style="display:flex;gap:12px;flex-wrap:wrap;">
                <button onclick="exportAllCSV()" class="btn btn-ghost btn-sm" style="background:rgba(255,255,255,.12);border-color:rgba(255,255,255,.2);color:#fff;">
                    ⬇ <?= currentLang()==='ar'?'تصدير الكل':(currentLang()==='fr'?'Exporter tout':'Export All') ?>
                </button>
            </div>
        </div>
    </section>
</div>
<?php endif; ?>

<!-- GPA Line Chart -->
<?php if (count($chartGpas) > 0): ?>
<div style="margin:0 28px 22px;">
    <section class="panel">
        <h3>📈 <?= currentLang()==='ar' ? 'منحنى المعدل عبر الفصول' : (currentLang()==='fr' ? 'Courbe de la moyenne' : 'GPA Trend Across Semesters') ?></h3>
        <div class="chart-wrap">
            <canvas id="gpaLineChart"></canvas>
        </div>
        <script>
        (function(){
            const labels = <?= json_encode($chartLabels) ?>;
            const gpas   = <?= json_encode($chartGpas) ?>;
            const ctx    = document.getElementById('gpaLineChart');
            if (!ctx) return;
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: '<?= currentLang()==="ar"?"المعدل":(currentLang()==="fr"?"Moyenne":"GPA") ?>',
                        data: gpas,
                        borderColor: '#4f46e5',
                        backgroundColor: 'rgba(79,70,229,.08)',
                        borderWidth: 3,
                        pointBackgroundColor: gpas.map(v => v >= 14 ? '#10b981' : v >= 10 ? '#4f46e5' : '#ef4444'),
                        pointRadius: 6,
                        pointHoverRadius: 9,
                        tension: .4,
                        fill: true,
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => ` ${ctx.parsed.y.toFixed(2)} / 20`
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false, min: 0, max: 20,
                            grid: { color: 'rgba(0,0,0,.05)' },
                            ticks: {
                                font: { family: 'Inter', size: 11 },
                                callback: v => v + '/20'
                            }
                        },
                        x: { grid: { display: false }, ticks: { font: { family: 'Inter', size: 12 } } }
                    },
                    annotation: {
                        annotations: {
                            passLine: {
                                type: 'line', yMin: 10, yMax: 10,
                                borderColor: 'rgba(239,68,68,.4)', borderWidth: 1.5, borderDash: [4,4]
                            }
                        }
                    }
                }
            });
        })();
        </script>
    </section>
</div>
<?php endif; ?>

<!-- Per-Semester Panels -->
<div style="margin:0 28px 28px;display:grid;gap:16px;">
<?php foreach ($grouped as $semData): ?>
<?php
    $semGrades   = $semData['grades'];
    $totalCred   = 0; $totalWeighted = 0; $hasScores = false;
    foreach ($semGrades as $g) {
        if ($g['score'] !== null) {
            $totalCred     += $g['credits'];
            $totalWeighted += $g['score'] * $g['credits'];
            $hasScores      = true;
        }
    }
    $semGpa = $totalCred > 0 ? round($totalWeighted / $totalCred, 2) : null;
?>
<section class="panel" id="sem-<?= $semData['semester_id'] ?>">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:16px;">
        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <h3 style="margin:0;font-size:16px;">
                <?= htmlspecialchars($semData['semester_label']) ?> — <?= htmlspecialchars($semData['semester_year']) ?>
            </h3>
            <?php if ($hasScores && $semGpa !== null): ?>
                <!-- GPA shown here in history -->
                <span class="badge strong" style="font-size:13px;padding:5px 14px;">
                    <?= currentLang()==='ar'?'المعدل':(currentLang()==='fr'?'Moy.':'GPA') ?>:
                    <strong><?= number_format($semGpa, 2) ?></strong> / 20
                </span>
                <?php if ($semGpa >= 10): ?>
                    <span class="badge badge-success">✓ <?= currentLang()==='ar'?'ناجح':(currentLang()==='fr'?'Admis':'Pass') ?></span>
                <?php else: ?>
                    <span class="badge badge-danger">✗ <?= currentLang()==='ar'?'راسب':(currentLang()==='fr'?'Échec':'Fail') ?></span>
                <?php endif; ?>
            <?php else: ?>
                <span class="badge badge-warning">⏳ <?= currentLang()==='ar'?'قيد الانتظار':(currentLang()==='fr'?'En attente':'Pending') ?></span>
            <?php endif; ?>
        </div>
        <button onclick="exportSemCSV(<?= $semData['semester_id'] ?>, '<?= addslashes($semData['semester_label'].' '.$semData['semester_year']) ?>')"
                class="btn btn-ghost btn-sm">
            ⬇ <?= t('export_csv') ?>
        </button>
    </div>

    <div class="table-wrap">
        <table data-semid="<?= $semData['semester_id'] ?>">
            <thead><tr>
                <th><?= t('course_name') ?></th>
                <th><?= t('professor') ?></th>
                <th><?= t('credits') ?></th>
                <th><?= t('score') ?></th>
                <th><?= currentLang()==='ar'?'الحالة':(currentLang()==='fr'?'Statut':'Status') ?></th>
            </tr></thead>
            <tbody>
            <?php foreach ($semGrades as $g): ?>
            <?php
                $s = $g['score'];
                if ($s === null) {
                    $status = '<span class="badge">⏳ '.( currentLang()==='ar'?'قيد الانتظار':(currentLang()==='fr'?'En attente':'Pending') ).'</span>';
                } elseif ($s >= 10) {
                    $status = '<span class="badge badge-success">✓ '.( currentLang()==='ar'?'ناجح':(currentLang()==='fr'?'Admis':'Pass') ).'</span>';
                } else {
                    $status = '<span class="badge badge-danger">✗ '.( currentLang()==='ar'?'راسب':(currentLang()==='fr'?'Échec':'Fail') ).'</span>';
                }
            ?>
            <tr>
                <td><strong><?= htmlspecialchars($g['course_name']) ?></strong></td>
                <td style="color:var(--muted);"><?= htmlspecialchars($g['professor_name']) ?></td>
                <td><span class="badge"><?= (int)$g['credits'] ?></span></td>
                <td>
                    <?php if ($s !== null): ?>
                    <strong style="font-size:15px;"><?= number_format((float)$s, 2) ?></strong>
                    <span style="color:var(--muted);font-size:12px;">/20</span>
                    <?php else: ?>
                    <span style="color:var(--muted-light);">—</span>
                    <?php endif; ?>
                </td>
                <td><?= $status ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php endforeach; ?>
</div>

<script>
function tableToCSV(table) {
    const rows = [];
    table.querySelectorAll('tr').forEach(tr => {
        rows.push([...tr.querySelectorAll('th,td')].map(c => '"' + c.innerText.replace(/"/g,'""') + '"').join(','));
    });
    return rows.join('\n');
}
function downloadCSV(content, filename) {
    const blob = new Blob(['\uFEFF' + content], {type:'text/csv;charset=utf-8;'});
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href = url; a.download = filename; a.click();
    URL.revokeObjectURL(url);
}
function exportSemCSV(semId, label) {
    const table = document.querySelector('table[data-semid="'+semId+'"]');
    if (table) downloadCSV(tableToCSV(table), 'grades_' + label.replace(/\s+/g,'_') + '.csv');
}
function exportAllCSV() {
    const tables = document.querySelectorAll('table[data-semid]');
    let all = '';
    tables.forEach(t => {
        const semid = t.dataset.semid;
        const heading = document.querySelector('#sem-'+semid+' h3');
        all += (heading ? heading.innerText : 'Semester') + '\n';
        all += tableToCSV(t) + '\n\n';
    });
    downloadCSV(all, 'grades_all_semesters.csv');
}
</script>

<?php endif; ?>

<?php include __DIR__ . '/../admin/layout/footer.php'; ?>
