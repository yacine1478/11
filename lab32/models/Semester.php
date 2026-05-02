<?php

class Semester {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function getAll(): array {
        $stmt = $this->pdo->query('SELECT * FROM semesters ORDER BY id DESC');
        return $stmt->fetchAll();
    }
    
    public function getActive(): ?array {
        $stmt = $this->pdo->query('SELECT * FROM semesters WHERE is_active = 1 LIMIT 1');
        $semester = $stmt->fetch();
        return $semester ?: null;
    }
    
    public function getById(int $id): ?array {
        $stmt = $this->pdo->prepare('SELECT * FROM semesters WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $semester = $stmt->fetch();
        return $semester ?: null;
    }
    
    public function create(string $label, string $academicYear): int {
        $stmt = $this->pdo->prepare(
            'INSERT INTO semesters (label, academic_year) VALUES (:label, :academic_year)'
        );
        $stmt->execute([':label' => $label, ':academic_year' => $academicYear]);
        return (int) $this->pdo->lastInsertId();
    }
    
    public function update(int $id, string $label, string $academicYear): bool {
        $stmt = $this->pdo->prepare('UPDATE semesters SET label = :label, academic_year = :academic_year WHERE id = :id');
        return $stmt->execute([':id' => $id, ':label' => $label, ':academic_year' => $academicYear]);
    }
    
    public function setActive(int $id): bool {
        $this->pdo->prepare('UPDATE semesters SET is_active = 0')->execute();
        $stmt = $this->pdo->prepare('UPDATE semesters SET is_active = 1 WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }
    
    public function delete(int $id): bool {
        // Check if semester has courses
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM courses WHERE semester_id = :id');
        $stmt->execute([':id' => $id]);
        if ($stmt->fetchColumn() > 0) {
            return false;
        }
        $stmt = $this->pdo->prepare('DELETE FROM semesters WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }
    
    public function getEnrolledSemesters(int $studentId): array {
        $stmt = $this->pdo->prepare(
            'SELECT s.* FROM semesters s
             JOIN enrollments e ON e.semester_id = s.id
             WHERE e.student_id = :student_id
             ORDER BY s.id DESC'
        );
        $stmt->execute([':student_id' => $studentId]);
        return $stmt->fetchAll();
    }
}