<?php
class AdminController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            redirect('login');
        }
    }

    // ── Dashboard ──────────────────────────────────────────────────────────
    public function dashboard() {
        require_once __DIR__ . '/../models/User.php';
        require_once __DIR__ . '/../models/Course.php';
        require_once __DIR__ . '/../models/Semester.php';

        $studentsCount   = count((new User($this->db))->getByRole('student'));
        $professorsCount = count((new User($this->db))->getByRole('professor'));
        $coursesCount    = (new Course($this->db))->count();
        $semesterModel   = new Semester($this->db);
        $activeSemester  = $semesterModel->getActive();
        $semestersCount  = count($semesterModel->getAll());

        // Average GPA per semester for the chart
        $semGpaData = $this->db->query(
            "SELECT s.label, s.year,
                    ROUND(AVG(g.score * c.credits) / NULLIF(AVG(c.credits), 0), 2) AS avg_score,
                    COUNT(DISTINCT e.student_id) AS enrolled_count
             FROM semesters s
             JOIN enrollments e  ON e.semester_id = s.id
             JOIN grades g       ON g.student_id  = e.student_id
             JOIN assignments a  ON g.assignment_id = a.id
             JOIN courses c      ON a.course_id = c.id AND c.semester_id = s.id
             GROUP BY s.id
             ORDER BY s.year ASC, s.label ASC"
        )->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../views/admin/dashboard.php';
    }

    // ── Semesters ──────────────────────────────────────────────────────────
    public function semesters() {
        require_once __DIR__ . '/../models/Semester.php';
        $semesterModel = new Semester($this->db);
        $edit      = !empty($_GET['id']) ? $semesterModel->find($_GET['id']) : null;
        $semesters = $semesterModel->getAll();
        require_once __DIR__ . '/../views/admin/semesters.php';
    }

    public function saveSemester() {
        require_once __DIR__ . '/../models/Semester.php';
        $id    = $_POST['id'] ?? null;
        $label = sanitize($_POST['label'] ?? '');
        $year  = sanitize($_POST['year'] ?? '');
        $model = new Semester($this->db);
        if ($id) {
            $model->update($id, $label, $year);
        } else {
            $model->create($label, $year);
        }
        redirect('admin.semesters');
    }

    public function deleteSemester() {
        require_once __DIR__ . '/../models/Semester.php';
        (new Semester($this->db))->delete($_POST['id']);
        redirect('admin.semesters');
    }

    public function activateSemester() {
        require_once __DIR__ . '/../models/Semester.php';
        (new Semester($this->db))->setActive($_POST['id']);
        redirect('admin.semesters');
    }

    // ── Courses ────────────────────────────────────────────────────────────
    public function courses() {
        require_once __DIR__ . '/../models/Course.php';
        require_once __DIR__ . '/../models/Semester.php';
        $courseModel   = new Course($this->db);
        $semesterModel = new Semester($this->db);
        $activeSem     = $semesterModel->getActive();
        $filterSemId   = (int)($_GET['semester_id'] ?? ($activeSem['id'] ?? 0));
        $edit          = !empty($_GET['id']) ? $courseModel->find($_GET['id']) : null;
        $courses       = $filterSemId ? $courseModel->getBySemester($filterSemId) : $courseModel->getAll();
        $semesters     = $semesterModel->getAll();
        require_once __DIR__ . '/../views/admin/courses.php';
    }

    public function saveCourse() {
        require_once __DIR__ . '/../models/Course.php';
        require_once __DIR__ . '/../models/Semester.php';
        $id         = $_POST['id'] ?? null;
        $name       = sanitize($_POST['name'] ?? '');
        $credits    = (int)($_POST['credits'] ?? 0);
        $semesterId = (int)($_POST['semester_id'] ?? 0);

        if ($credits <= 0) {
            flash('error', currentLang() === 'fr'
                ? 'Les crédits doivent être > 0.'
                : (currentLang() === 'en' ? 'Credits must be > 0.' : 'الساعات يجب أن تكون > 0.'));
        } else {
            $model = new Course($this->db);
            if ($id) {
                $model->update($id, $name, $credits, $semesterId);
            } else {
                $model->create($name, $credits, $semesterId);
            }
        }
        redirect('admin.courses');
    }

    public function deleteCourse() {
        require_once __DIR__ . '/../models/Course.php';
        require_once __DIR__ . '/../models/Assignment.php';
        require_once __DIR__ . '/../models/Grade.php';
        $assignmentModel = new Assignment($this->db);
        $gradeModel      = new Grade($this->db);
        foreach ($assignmentModel->getIdsByCourse($_POST['id']) as $aId) {
            $gradeModel->deleteByAssignment($aId);
            $assignmentModel->delete($aId);
        }
        (new Course($this->db))->delete($_POST['id']);
        redirect('admin.courses');
    }

    // ── Professors ─────────────────────────────────────────────────────────
    public function professors() {
        require_once __DIR__ . '/../models/User.php';
        $page      = max(1, (int)($_GET['p'] ?? 1));
        $search    = sanitize($_GET['q'] ?? '');
        $perPage   = 10;
        $editId    = $_GET['edit'] ?? null;
        $userModel = new User($this->db);
        $professors = $userModel->searchByRole('professor', $search, $perPage, ($page - 1) * $perPage);
        $total     = $userModel->countByRole('professor', $search);
        $edit      = $editId ? $userModel->find($editId) : null;
        require_once __DIR__ . '/../views/admin/professors.php';
    }

    public function saveProfessor() {
        $this->saveUser('professor', 'admin.professors');
    }

    public function deleteProfessor() {
        require_once __DIR__ . '/../models/User.php';
        require_once __DIR__ . '/../models/Assignment.php';
        require_once __DIR__ . '/../models/Grade.php';
        $assignmentModel = new Assignment($this->db);
        $gradeModel      = new Grade($this->db);
        foreach ($assignmentModel->getIdsByProfessor($_POST['id']) as $aId) {
            $gradeModel->deleteByAssignment($aId);
            $assignmentModel->delete($aId);
        }
        (new User($this->db))->delete($_POST['id']);
        redirect('admin.professors');
    }

    // ── Students ───────────────────────────────────────────────────────────
    public function students() {
        require_once __DIR__ . '/../models/User.php';
        $page      = max(1, (int)($_GET['p'] ?? 1));
        $search    = sanitize($_GET['q'] ?? '');
        $perPage   = 10;
        $editId    = $_GET['edit'] ?? null;
        $userModel = new User($this->db);
        $students  = $userModel->searchByRole('student', $search, $perPage, ($page - 1) * $perPage);
        $total     = $userModel->countByRole('student', $search);
        $edit      = $editId ? $userModel->find($editId) : null;
        require_once __DIR__ . '/../views/admin/students.php';
    }

    public function saveStudent() {
        $this->saveUser('student', 'admin.students');
    }

    public function deleteStudent() {
        require_once __DIR__ . '/../models/User.php';
        require_once __DIR__ . '/../models/Grade.php';
        $id = $_POST['id'];
        (new Grade($this->db))->deleteByStudent($id);
        (new User($this->db))->delete($id);
        redirect('admin.students');
    }

    // ── Enrollments ────────────────────────────────────────────────────────
    public function enrollments() {
        require_once __DIR__ . '/../models/Enrollment.php';
        require_once __DIR__ . '/../models/Semester.php';
        require_once __DIR__ . '/../models/User.php';
        $semesterModel  = new Semester($this->db);
        $enrollModel    = new Enrollment($this->db);
        $activeSem      = $semesterModel->getActive();
        $semId          = (int)($_GET['semester_id'] ?? ($activeSem['id'] ?? 0));
        $semesters      = $semesterModel->getAll();
        $allStudents    = (new User($this->db))->getByRole('student');
        $enrolledIds    = $semId ? $enrollModel->getEnrolledIds($semId) : [];
        $enrolled       = $semId ? $enrollModel->getStudentsBySemester($semId) : [];
        require_once __DIR__ . '/../views/admin/enrollments.php';
    }

    public function saveEnrollments() {
        require_once __DIR__ . '/../models/Enrollment.php';
        $semId      = (int)($_POST['semester_id'] ?? 0);
        $studentIds = $_POST['student_ids'] ?? [];
        (new Enrollment($this->db))->syncStudents($semId, $studentIds);
        flash('success', currentLang() === 'ar' ? 'تم حفظ التسجيلات.' : (currentLang() === 'fr' ? 'Inscriptions enregistrées.' : 'Enrollments saved.'));
        header('Location: ' . pageUrl('admin.enrollments', ['semester_id' => $semId]));
        exit;
    }

    // ── Assignments ────────────────────────────────────────────────────────
    public function assignments() {
        require_once __DIR__ . '/../models/Assignment.php';
        require_once __DIR__ . '/../models/User.php';
        require_once __DIR__ . '/../models/Course.php';
        require_once __DIR__ . '/../models/Semester.php';
        $semesterModel = new Semester($this->db);
        $activeSem     = $semesterModel->getActive();
        $semId         = (int)($_GET['semester_id'] ?? ($activeSem['id'] ?? 0));
        $editId        = $_GET['edit'] ?? null;
        $assignModel   = new Assignment($this->db);
        $assignments   = $semId ? $assignModel->getBySemester($semId) : $assignModel->getAll();
        $professors    = (new User($this->db))->getByRole('professor');
        $courses       = $semId ? (new Course($this->db))->getBySemester($semId) : (new Course($this->db))->getAll();
        $semesters     = $semesterModel->getAll();
        $edit          = $editId ? $assignModel->find($editId) : null;
        require_once __DIR__ . '/../views/admin/assignments.php';
    }

    public function saveAssignment() {
        require_once __DIR__ . '/../models/Assignment.php';
        $id          = $_POST['id'] ?? null;
        $professorId = $_POST['professor_id'];
        $courseId    = $_POST['course_id'];
        $model       = new Assignment($this->db);
        if ($id) {
            $model->update($id, $professorId, $courseId);
        } else {
            $model->create($professorId, $courseId);
        }
        $semId = $_POST['semester_id'] ?? '';
        redirect('admin.assignments' . ($semId ? '&semester_id=' . $semId : ''));
    }

    public function deleteAssignment() {
        require_once __DIR__ . '/../models/Assignment.php';
        require_once __DIR__ . '/../models/Grade.php';
        $aId = $_POST['id'];
        (new Grade($this->db))->deleteByAssignment($aId);
        (new Assignment($this->db))->delete($aId);
        $semId = $_POST['semester_id'] ?? '';
        redirect('admin.assignments' . ($semId ? '&semester_id=' . $semId : ''));
    }

    // ── Helper ─────────────────────────────────────────────────────────────
    private function saveUser($role, $redirectPage) {
        require_once __DIR__ . '/../models/User.php';
        $userModel = new User($this->db);
        $id    = $_POST['id'] ?? null;
        $email = sanitize($_POST['email'] ?? '');

        if ($userModel->emailExists($email, $id)) {
            flash('error', currentLang() === 'fr'
                ? 'Cet e-mail est déjà utilisé.'
                : (currentLang() === 'en' ? 'This email is already used.' : 'البريد الإلكتروني مستخدم مسبقًا.'));
            redirect($redirectPage);
        }

        $data = [
            'full_name' => sanitize($_POST['name'] ?? ''),
            'email'     => $email,
            'role'      => $role,
        ];

        if ($id) {
            $userModel->update($id, $data);
        } else {
            $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $userModel->create($data);
        }
        redirect($redirectPage);
    }
}
