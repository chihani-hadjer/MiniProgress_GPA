<?php
class ProfessorController {
    private $db;
    private $professorId;

    public function __construct($db) {
        $this->db = $db;
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'professor') {
            redirect('login');
        }
        $this->professorId = $_SESSION['user']['id'];
    }

    public function grades() {
        require_once __DIR__ . '/../models/Assignment.php';
        require_once __DIR__ . '/../models/Semester.php';
        $semesterModel = new Semester($this->db);
        $semesters     = $semesterModel->getAll();
        $activeSem     = $semesterModel->getActive();
        $selectedSemId = (int)($_GET['semester_id'] ?? ($activeSem['id'] ?? 0));
        $myAssignments = (new Assignment($this->db))->getByProfessor($this->professorId, $selectedSemId ?: null);
        require_once __DIR__ . '/../views/professor/grades.php';
    }
}
