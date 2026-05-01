<?php
class Semester {
    private $db;

    public function __construct($db) { $this->db = $db; }

    public function create($label, $year) {
        $stmt = $this->db->prepare("INSERT INTO semesters (label, year, is_active) VALUES (?, ?, 0)");
        return $stmt->execute([$label, $year]);
    }

    public function update($id, $label, $year) {
        $stmt = $this->db->prepare("UPDATE semesters SET label=?, year=? WHERE id=?");
        return $stmt->execute([$label, $year, $id]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM semesters WHERE id=?");
        return $stmt->execute([$id]);
    }

    public function setActive($id) {
        $this->db->exec("UPDATE semesters SET is_active=0");
        $stmt = $this->db->prepare("UPDATE semesters SET is_active=1 WHERE id=?");
        return $stmt->execute([$id]);
    }

    public function getActive() {
        return $this->db->query("SELECT * FROM semesters WHERE is_active=1 LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    }

    public function getAll() {
        return $this->db->query("SELECT * FROM semesters ORDER BY year DESC, label ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM semesters WHERE id=?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
