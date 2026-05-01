const labels = window.MINIPROGRESS_I18N || {};

async function loadCurrent() {
  const gpaEl = document.getElementById('gpa');
  const coursesEl = document.getElementById('courses');
  if (!gpaEl || !coursesEl) return;

  const response = await fetch('../api/gpa.php?action=current');
  if (!response.ok) throw new Error('load failed');
  const data = await response.json();
  const gpa = Number.parseFloat(data.gpa || 0);

  gpaEl.textContent = gpa.toFixed(2);
  gpaEl.style.color = gpa < 2 ? '#dc2626' : gpa < 3 ? '#f59e0b' : '#16a34a';

  if (!data.grades || data.grades.length === 0) {
    coursesEl.innerHTML = `<p>${labels.noGrades || 'No grades for the active semester.'}</p>`;
    return;
  }

  coursesEl.innerHTML = data.grades.map((course) => `
    <div class="course-row">
      <strong>${course.course_name}</strong>
      <span>${labels.score || 'Score'}: ${course.score ?? ''}</span>
    </div>
  `).join('');
}

async function loadHistory() {
  const historyEl = document.getElementById('history');
  if (!historyEl) return;

  const response = await fetch('../api/gpa.php?action=history');
  if (!response.ok) throw new Error('load failed');
  const history = await response.json();

  if (!history.length) {
    historyEl.innerHTML = `<p>${labels.noHistory || 'No history yet.'}</p>`;
    return;
  }

  historyEl.innerHTML = history.map((semester) => `
    <div class="course-row">
      <strong>${semester.label} - ${semester.academic_year}</strong>
      <span>${Number.parseFloat(semester.gpa_value).toFixed(2)}</span>
    </div>
  `).join('');

  const chartEl = document.getElementById('gpaLineChart');
  if (chartEl && window.Chart) {
    new Chart(chartEl, {
      type: 'line',
      data: {
        labels: history.map(item => item.label).reverse(),
        datasets: [{
          label: labels.currentGpa || 'GPA',
          data: history.map(item => Number(item.gpa_value || 0)).reverse(),
          borderColor: '#6d28d9',
          backgroundColor: 'rgba(109, 40, 217, .12)',
          tension: .35,
          fill: true
        }]
      },
      options: { scales: { y: { beginAtZero: true, max: 20 } } }
    });
  }
}

loadCurrent().catch(() => {
  const gpaEl = document.getElementById('gpa');
  if (gpaEl) gpaEl.textContent = labels.loadError || 'Error loading data';
});
loadHistory().catch(() => {
  const historyEl = document.getElementById('history');
  if (historyEl) historyEl.innerHTML = `<p>${labels.loadError || 'Error loading data'}</p>`;
});
