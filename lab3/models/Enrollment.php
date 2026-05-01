<?php

class Enrollment {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function getStudentsBySemester(int $semesterId): array {
        $stmt = $this->pdo->prepare(
            'SELECT u.id, u.name 
             FROM users u
             JOIN enrollments e ON e.student_id = u.id
             WHERE e.semester_id = :semester_id AND u.role = "student"
             ORDER BY u.name'
        );
        $stmt->execute([':semester_id' => $semesterId]);
        return $stmt->fetchAll();
    }

    public function isEnrolled($studentId, $semesterId) {
        $stmt = $this->pdo->prepare('SELECT 1 FROM enrollments WHERE student_id = :student_id AND semester_id = :semester_id');
        $stmt->execute([
            ':student_id' => $studentId,
            ':semester_id' => $semesterId
        ]);
        return $stmt->fetch() !== false;
    }

    public function getStudentSemesterIds(int $studentId): array {
        $stmt = $this->pdo->prepare('SELECT semester_id FROM enrollments WHERE student_id = :student_id');
        $stmt->execute([':student_id' => $studentId]);
        return array_column($stmt->fetchAll(), 'semester_id');
    }

    public function updateForStudent(int $studentId, array $semesterIds): void {
        $delete = $this->pdo->prepare('DELETE FROM enrollments WHERE student_id = :student_id');
        $delete->execute([':student_id' => $studentId]);

        $insert = $this->pdo->prepare('INSERT IGNORE INTO enrollments (student_id, semester_id) VALUES (:student_id, :semester_id)');
        foreach ($semesterIds as $semesterId) {
            $insert->execute([':student_id' => $studentId, ':semester_id' => $semesterId]);
        }
    }
}