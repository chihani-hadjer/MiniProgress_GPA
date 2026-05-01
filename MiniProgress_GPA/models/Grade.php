<?php
class Grade {
    private $db;

    public function __construct($db) { $this->db = $db; }

    public function save($studentId, $assignmentId, $score) {
        $stmt = $this->db->prepare(
            "INSERT INTO grades (student_id, assignment_id, score)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE score = VALUES(score)"
        );
        return $stmt->execute([$studentId, $assignmentId, $score]);
    }

    // درجات طالب في فصل معين
    public function getBySemester($studentId, $semesterId) {
        $sql = "SELECT g.score, g.assignment_id,
                       c.name AS course_name, c.credits, c.semester_id,
                       u.name AS professor_name
                FROM grades g
                JOIN assignments a ON g.assignment_id = a.id
                JOIN courses c     ON a.course_id = c.id
                JOIN users u       ON a.professor_id = u.id
                WHERE g.student_id = ? AND c.semester_id = ?
                ORDER BY c.name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$studentId, $semesterId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // كل الفصول التي للطالب درجات فيها
    public function getGroupedBySemester($studentId) {
        $sql = "SELECT g.score, c.name AS course_name, c.credits,
                       u.name AS professor_name,
                       s.id AS semester_id, s.label AS semester_label, s.year AS semester_year
                FROM grades g
                JOIN assignments a ON g.assignment_id = a.id
                JOIN courses c     ON a.course_id = c.id
                JOIN users u       ON a.professor_id = u.id
                JOIN semesters s   ON c.semester_id = s.id
                JOIN enrollments e ON e.student_id = g.student_id AND e.semester_id = s.id
                WHERE g.student_id = ?
                ORDER BY s.year DESC, s.label ASC, c.name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$studentId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $grouped = [];
        foreach ($rows as $r) {
            $key = $r['semester_id'];
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'semester_id'    => $r['semester_id'],
                    'semester_label' => $r['semester_label'],
                    'semester_year'  => $r['semester_year'],
                    'grades'         => [],
                ];
            }
            $grouped[$key]['grades'][] = $r;
        }
        return $grouped;
    }

    // كشف درجات لتعيين معين (الأستاذ)
    public function getGradeSheet($assignmentId, $semesterId = null) {
        // نجلب الطلاب المسجلين في الفصل المرتبط بهذا التعيين
        $sql = "SELECT u.id, u.name, u.email, g.score
                FROM enrollments e
                JOIN users u ON e.student_id = u.id
                JOIN assignments a ON a.id = ?
                JOIN courses c ON a.course_id = c.id AND c.semester_id = e.semester_id
                LEFT JOIN grades g ON g.assignment_id = a.id AND g.student_id = u.id
                WHERE a.id = ?
                ORDER BY u.name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$assignmentId, $assignmentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function calculateGPAForSemester($studentId, $semesterId) {
        $sql = "SELECT SUM(g.score * c.credits) / SUM(c.credits) AS gpa
                FROM grades g
                JOIN assignments a ON g.assignment_id = a.id
                JOIN courses c     ON a.course_id = c.id
                WHERE g.student_id = ? AND c.semester_id = ? AND g.score IS NOT NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$studentId, $semesterId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return round((float)($row['gpa'] ?? 0), 2);
    }

    public function deleteByStudent($studentId) {
        $stmt = $this->db->prepare("DELETE FROM grades WHERE student_id=?");
        return $stmt->execute([$studentId]);
    }

    public function deleteByAssignment($assignmentId) {
        $stmt = $this->db->prepare("DELETE FROM grades WHERE assignment_id=?");
        return $stmt->execute([$assignmentId]);
    }

    // هل وُضعت نقاط فعلاً للفصل؟
    public function hasGradesForSemester($studentId, $semesterId) {
        $sql = "SELECT COUNT(*) FROM grades g
                JOIN assignments a ON g.assignment_id = a.id
                JOIN courses c ON a.course_id = c.id
                WHERE g.student_id = ? AND c.semester_id = ? AND g.score IS NOT NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$studentId, $semesterId]);
        return $stmt->fetchColumn() > 0;
    }
}
