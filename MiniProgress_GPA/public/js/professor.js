(function () {
    const assignmentSel = document.getElementById('assignment');
    const loadBtn       = document.getElementById('load-students');
    const saveBtn       = document.getElementById('save');
    const exportBtn     = document.getElementById('export-csv');
    const tbody         = document.getElementById('students');
    const msg           = document.getElementById('msg');
    const i18n          = window.MINIPROGRESS_I18N || {};

    let currentAssignmentId = null;
    let currentCourseName   = '';

    if (!loadBtn) return;

    loadBtn.addEventListener('click', loadStudents);
    saveBtn.addEventListener('click', saveGrades);
    if (exportBtn) exportBtn.addEventListener('click', exportCSV);

    function loadStudents() {
        const aId = assignmentSel.value;
        if (!aId) return;
        currentAssignmentId = aId;
        currentCourseName   = assignmentSel.options[assignmentSel.selectedIndex].text;
        tbody.innerHTML     = `<tr><td colspan="3">${i18n.loading || 'Loading...'}</td></tr>`;
        msg.textContent     = '';
        if (exportBtn) exportBtn.style.display = 'none';

        fetch(`../api/grades.php?action=students&assignment_id=${aId}`)
            .then(r => r.ok ? r.json() : Promise.reject(r))
            .then(data => {
                if (!data.length) {
                    tbody.innerHTML = `<tr><td colspan="3">${i18n.noStudents || 'No students'}</td></tr>`;
                    return;
                }
                tbody.innerHTML = data.map(s => `
                    <tr>
                        <td>${esc(s.name)}</td>
                        <td>${s.id}</td>
                        <td><input type="number" class="score-input" data-student="${s.id}"
                             value="${s.score !== null ? s.score : ''}"
                             min="0" max="20" step="0.25"
                             style="width:90px;padding:6px 8px;border:1px solid #e2e8f0;border-radius:6px;"></td>
                    </tr>`).join('');
                if (exportBtn) exportBtn.style.display = '';
            })
            .catch(() => { tbody.innerHTML = `<tr><td colspan="3">${i18n.loadError}</td></tr>`; });
    }

    function saveGrades() {
        if (!currentAssignmentId) return;
        const inputs = tbody.querySelectorAll('.score-input');
        const grades = [...inputs].map(inp => ({
            student_id: parseInt(inp.dataset.student),
            score: inp.value === '' ? null : parseFloat(inp.value)
        })).filter(g => g.score !== null && g.score >= 0 && g.score <= 20);

        fetch('../api/grades.php?action=save', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({assignment_id: currentAssignmentId, grades})
        })
        .then(r => r.json())
        .then(d => {
            msg.textContent = d.ok ? (i18n.saved || 'Saved!') : (d.error || 'Error');
            msg.style.color = d.ok ? 'green' : 'red';
        })
        .catch(() => { msg.textContent = i18n.serverError; msg.style.color = 'red'; });
    }

    function exportCSV() {
        const rows = [['Name', 'ID', 'Score']];
        tbody.querySelectorAll('tr').forEach(tr => {
            const cells = tr.querySelectorAll('td');
            if (cells.length < 3) return;
            const inp   = cells[2].querySelector('input');
            rows.push([cells[0].innerText, cells[1].innerText, inp ? inp.value : '']);
        });
        const csv  = '\uFEFF' + rows.map(r => r.map(c => '"' + String(c).replace(/"/g, '""') + '"').join(',')).join('\n');
        const blob = new Blob([csv], {type: 'text/csv;charset=utf-8;'});
        const url  = URL.createObjectURL(blob);
        const a    = document.createElement('a');
        a.href     = url;
        a.download = 'grades_' + currentCourseName.replace(/\s+/g, '_') + '.csv';
        a.click();
        URL.revokeObjectURL(url);
    }

    function esc(str) {
        const d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }
})();
