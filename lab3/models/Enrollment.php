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
}