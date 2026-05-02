<?php

class Grade {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function upsert(int $studentId, int $courseId, int $semesterId, int $professorId, float $grade): void {
        $sql = "INSERT INTO grades (student_id, course_id, semester_id, professor_id, grade) 
                VALUES (:student_id, :course_id, :semester_id, :professor_id, :grade)
                ON DUPLICATE KEY UPDATE 
                    professor_id = VALUES(professor_id), 
                    grade = VALUES(grade),
                    entered_at = CURRENT_TIMESTAMP";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':student_id' => $studentId,
            ':course_id' => $courseId,
            ':semester_id' => $semesterId,
            ':professor_id' => $professorId,
            ':grade' => $grade,
        ]);
    }
    
    public function get(int $studentId, int $courseId, int $semesterId): ?float {
        $stmt = $this->pdo->prepare(
            'SELECT grade FROM grades WHERE student_id = :student_id AND course_id = :course_id AND semester_id = :semester_id'
        );
        $stmt->execute([
            ':student_id' => $studentId,
            ':course_id' => $courseId,
            ':semester_id' => $semesterId,
        ]);
        $result = $stmt->fetch();
        return $result ? (float) $result['grade'] : null;
    }
    
    public function getStudentGrades(int $studentId, int $semesterId): array {
        $stmt = $this->pdo->prepare(
            'SELECT g.grade, g.course_id, c.name as course_name, c.credits, p.name as professor_name
             FROM grades g
             JOIN courses c ON g.course_id = c.id
             JOIN users p ON g.professor_id = p.id
             WHERE g.student_id = :student_id AND g.semester_id = :semester_id
             ORDER BY c.name'
        );
        $stmt->execute([
            ':student_id' => $studentId,
            ':semester_id' => $semesterId,
        ]);
        return $stmt->fetchAll();
    }
    
    public function deleteByStudent(int $studentId): void {
        $stmt = $this->pdo->prepare('DELETE FROM grades WHERE student_id = :student_id');
        $stmt->execute([':student_id' => $studentId]);
    }
    
    public function deleteByCourse(int $courseId): void {
        $stmt = $this->pdo->prepare('DELETE FROM grades WHERE course_id = :course_id');
        $stmt->execute([':course_id' => $courseId]);
    }
    
    public function countByCourse(int $courseId): int {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM grades WHERE course_id = :course_id');
        $stmt->execute([':course_id' => $courseId]);
        return (int) $stmt->fetchColumn();
    }
    
    public function countByStudentSemester(int $studentId, int $semesterId): int {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM grades WHERE student_id = :student_id AND semester_id = :semester_id'
        );
        $stmt->execute([
            ':student_id' => $studentId,
            ':semester_id' => $semesterId,
        ]);
        return (int) $stmt->fetchColumn();
    }
}