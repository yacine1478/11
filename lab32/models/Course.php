<?php

class Course {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAll() {
        $stmt = $this->pdo->query('SELECT c.*, s.label as semester_label, s.academic_year FROM courses c JOIN semesters s ON c.semester_id = s.id ORDER BY s.academic_year DESC, s.label, c.name');
        return $stmt->fetchAll();
    }

    public function create(string $name, int $semesterId, int $credits): int {
        $stmt = $this->pdo->prepare('INSERT INTO courses (semester_id, name, credits) VALUES (:semester_id, :name, :credits)');
        $stmt->execute([
            ':semester_id' => $semesterId,
            ':name' => $name,
            ':credits' => $credits,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function delete(int $id): bool {
        $this->pdo->prepare('DELETE FROM assignments WHERE course_id = :id')->execute([':id' => $id]);
        $this->pdo->prepare('DELETE FROM grades WHERE course_id = :id')->execute([':id' => $id]);
        $stmt = $this->pdo->prepare('DELETE FROM courses WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }
}