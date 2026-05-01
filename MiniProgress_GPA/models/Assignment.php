<?php
class Assignment {
    private $db;

    public function __construct($db) { $this->db = $db; }

    public function create($professorId, $courseId) {
        $stmt = $this->db->prepare("INSERT IGNORE INTO assignments (professor_id, course_id) VALUES (?, ?)");
        return $stmt->execute([$professorId, $courseId]);
    }

    public function update($id, $professorId, $courseId) {
        $stmt = $this->db->prepare("UPDATE assignments SET professor_id=?, course_id=? WHERE id=?");
        return $stmt->execute([$professorId, $courseId, $id]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM assignments WHERE id=?");
        return $stmt->execute([$id]);
    }

    public function getAll() {
        $sql = "SELECT a.id, a.professor_id, a.course_id,
                       u.name AS professor_name,
                       c.name AS course_name, c.credits,
                       s.label AS semester_label, s.year AS semester_year, s.id AS semester_id
                FROM assignments a
                JOIN users u   ON a.professor_id = u.id
                JOIN courses c ON a.course_id = c.id
                JOIN semesters s ON c.semester_id = s.id
                ORDER BY s.year DESC, s.label ASC, u.name ASC, c.name ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBySemester($semesterId) {
        $sql = "SELECT a.id, a.professor_id, a.course_id,
                       u.name AS professor_name,
                       c.name AS course_name, c.credits
                FROM assignments a
                JOIN users u   ON a.professor_id = u.id
                JOIN courses c ON a.course_id = c.id
                WHERE c.semester_id = ?
                ORDER BY u.name ASC, c.name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$semesterId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM assignments WHERE id=?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByProfessor($professorId, $semesterId = null) {
        $sql = "SELECT a.id, c.name AS course_name, c.credits, a.course_id, s.id AS semester_id, s.label AS semester_label, s.year AS semester_year
                FROM assignments a
                JOIN courses c ON a.course_id = c.id
                JOIN semesters s ON c.semester_id = s.id
                WHERE a.professor_id = ?";
        $params = [$professorId];
        if ($semesterId) {
            $sql .= " AND c.semester_id = ?";
            $params[] = $semesterId;
        }
        $sql .= " ORDER BY s.year DESC, s.label ASC, c.name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getIdsByProfessor($professorId) {
        $stmt = $this->db->prepare("SELECT id FROM assignments WHERE professor_id=?");
        $stmt->execute([$professorId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getIdsByCourse($courseId) {
        $stmt = $this->db->prepare("SELECT id FROM assignments WHERE course_id=?");
        $stmt->execute([$courseId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function isAssignedTo($assignmentId, $professorId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM assignments WHERE id=? AND professor_id=?");
        $stmt->execute([$assignmentId, $professorId]);
        return $stmt->fetchColumn() > 0;
    }
}
