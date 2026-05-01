<?php
class User {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByRole($role) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE role = ? ORDER BY name ASC");
        $stmt->execute([$role]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchByRole($role, $search = '', $limit = 10, $offset = 0) {
        $sql = "SELECT * FROM users WHERE role = ? AND (name LIKE ? OR email LIKE ?) ORDER BY name ASC LIMIT ? OFFSET ?";
        $term = '%' . $search . '%';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(1, $role);
        $stmt->bindValue(2, $term);
        $stmt->bindValue(3, $term);
        $stmt->bindValue(4, (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(5, (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countByRole($role, $search = '') {
        $term = '%' . $search . '%';
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE role = ? AND (name LIKE ? OR email LIKE ?)");
        $stmt->execute([$role, $term, $term]);
        return (int)$stmt->fetchColumn();
    }

    public function emailExists($email, $excludeId = null) {
        if ($excludeId) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $excludeId]);
        } else {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
        }
        return $stmt->fetchColumn() > 0;
    }

    public function create($data) {
        $stmt = $this->db->prepare(
            "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)"
        );
        return $stmt->execute([
            $data['full_name'],
            $data['email'],
            $data['password'],
            $data['role']
        ]);
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare(
            "UPDATE users SET name = ?, email = ? WHERE id = ?"
        );
        return $stmt->execute([$data['full_name'], $data['email'], $id]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function updatePassword($id, $passwordHash) {
        $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
        return $stmt->execute([$passwordHash, $id]);
    }
}
