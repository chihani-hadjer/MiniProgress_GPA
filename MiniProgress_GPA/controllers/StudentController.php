<?php
class StudentController {
    private $db;
    private $studentId;

    public function __construct($db) {
        $this->db = $db;
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
            redirect('login');
        }
        $this->studentId = $_SESSION['user']['id'];
    }

    public function dashboard() {
        require_once __DIR__ . '/../models/Grade.php';
        require_once __DIR__ . '/../models/Semester.php';
        $semesterModel  = new Semester($this->db);
        $activeSemester = $semesterModel->getActive();
        $grades         = [];
        $gpa            = null;
        $hasGrades      = false;
        if ($activeSemester) {
            $gradeModel = new Grade($this->db);
            $grades     = $gradeModel->getBySemester($this->studentId, $activeSemester['id']);
            $hasGrades  = $gradeModel->hasGradesForSemester($this->studentId, $activeSemester['id']);
            if ($hasGrades) {
                $gpa = $gradeModel->calculateGPAForSemester($this->studentId, $activeSemester['id']);
            }
        }
        require_once __DIR__ . '/../views/student/dashboard.php';
    }

    public function history() {
        require_once __DIR__ . '/../models/Grade.php';
        require_once __DIR__ . '/../models/Enrollment.php';
        $gradeModel   = new Grade($this->db);
        $enrollModel  = new Enrollment($this->db);
        $grouped      = $gradeModel->getGroupedBySemester($this->studentId);
        $semesters    = $enrollModel->getSemestersByStudent($this->studentId);
        require_once __DIR__ . '/../views/student/history.php';
    }
}
