<?php
class Course {
    private $db;

    public function __construct($db) { $this->db = $db; }

    public function create($name, $credits, $semesterId) {
        $stmt = $this->db->prepare("INSERT INTO courses (name, credits, semester_id) VALUES (?, ?, ?)");
        return $stmt->execute([$name, $credits, $semesterId]);
    }

    public function update($id, $name, $credits, $semesterId) {
        $stmt = $this->db->prepare("UPDATE courses SET name=?, credits=?, semester_id=? WHERE id=?");
        return $stmt->execute([$name, $credits, $semesterId, $id]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM courses WHERE id=?");
        return $stmt->execute([$id]);
    }

    public function getAll() {
        $sql = "SELECT c.*, s.label AS semester_label, s.year AS semester_year
                FROM courses c JOIN semesters s ON c.semester_id=s.id
                ORDER BY s.year DESC, s.label ASC, c.name ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBySemester($semesterId) {
        $stmt = $this->db->prepare("SELECT * FROM courses WHERE semester_id=? ORDER BY name ASC");
        $stmt->execute([$semesterId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM courses WHERE id=?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function count() {
        return (int)$this->db->query("SELECT COUNT(*) FROM courses")->fetchColumn();
    }
}
