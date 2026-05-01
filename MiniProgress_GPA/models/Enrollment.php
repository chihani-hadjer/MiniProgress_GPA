<?php
class Enrollment {
    private $db;

    public function __construct($db) { $this->db = $db; }

    public function enroll($studentId, $semesterId) {
        $stmt = $this->db->prepare("INSERT IGNORE INTO enrollments (student_id, semester_id) VALUES (?, ?)");
        return $stmt->execute([$studentId, $semesterId]);
    }

    public function unenroll($studentId, $semesterId) {
        $stmt = $this->db->prepare("DELETE FROM enrollments WHERE student_id=? AND semester_id=?");
        return $stmt->execute([$studentId, $semesterId]);
    }

    // sync: replace all students for a semester
    public function syncStudents($semesterId, $studentIds) {
        $this->db->prepare("DELETE FROM enrollments WHERE semester_id=?")->execute([$semesterId]);
        $stmt = $this->db->prepare("INSERT IGNORE INTO enrollments (student_id, semester_id) VALUES (?, ?)");
        foreach ($studentIds as $sid) {
            $stmt->execute([(int)$sid, (int)$semesterId]);
        }
    }

    public function getStudentsBySemester($semesterId) {
        $sql = "SELECT u.id, u.name, u.email FROM enrollments e
                JOIN users u ON e.student_id = u.id
                WHERE e.semester_id = ? ORDER BY u.name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$semesterId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEnrolledIds($semesterId) {
        $stmt = $this->db->prepare("SELECT student_id FROM enrollments WHERE semester_id=?");
        $stmt->execute([$semesterId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getSemestersByStudent($studentId) {
        $sql = "SELECT s.* FROM enrollments e JOIN semesters s ON e.semester_id=s.id
                WHERE e.student_id=? ORDER BY s.year DESC, s.label ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$studentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function isEnrolled($studentId, $semesterId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM enrollments WHERE student_id=? AND semester_id=?");
        $stmt->execute([$studentId, $semesterId]);
        return $stmt->fetchColumn() > 0;
    }
}
