<?php

class GPA {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function compute(int $studentId, int $semesterId): ?float {
        $stmt = $this->pdo->prepare(
            'SELECT g.grade, c.credits 
             FROM grades g 
             JOIN courses c ON g.course_id = c.id 
             WHERE g.student_id = :student_id AND g.semester_id = :semester_id'
        );
        $stmt->execute([
            ':student_id' => $studentId,
            ':semester_id' => $semesterId,
        ]);
        
        $grades = $stmt->fetchAll();
        $totalPoints = 0;
        $totalCredits = 0;
        
        foreach ($grades as $row) {
            $totalPoints += (float) $row['grade'] * (int) $row['credits'];
            $totalCredits += (int) $row['credits'];
        }
        
        return $totalCredits > 0 ? round($totalPoints / $totalCredits, 2) : null;
    }
    
    public function save(int $studentId, int $semesterId, float $gpa): void {
        $stmt = $this->pdo->prepare(
            'INSERT INTO gpa_records (student_id, semester_id, gpa) 
             VALUES (:student_id, :semester_id, :gpa)
             ON DUPLICATE KEY UPDATE gpa = VALUES(gpa), computed_at = CURRENT_TIMESTAMP'
        );
        $stmt->execute([
            ':student_id' => $studentId,
            ':semester_id' => $semesterId,
            ':gpa' => $gpa,
        ]);
    }
    
    public function recomputeAndSave(int $studentId, int $semesterId): ?float {
        $gpa = $this->compute($studentId, $semesterId);
        if ($gpa !== null) {
            $this->save($studentId, $semesterId, $gpa);
        }
        return $gpa;
    }
    
    public function get(int $studentId, int $semesterId): ?float {
        $stmt = $this->pdo->prepare(
            'SELECT gpa FROM gpa_records WHERE student_id = :student_id AND semester_id = :semester_id'
        );
        $stmt->execute([
            ':student_id' => $studentId,
            ':semester_id' => $semesterId,
        ]);
        $result = $stmt->fetch();
        return $result ? (float) $result['gpa'] : null;
    }
    
    public function getStudentGPAHistory(int $studentId): array {
        $stmt = $this->pdo->prepare(
            'SELECT s.label, s.academic_year, g.gpa
             FROM gpa_records g
             JOIN semesters s ON g.semester_id = s.id
             WHERE g.student_id = :student_id
             ORDER BY s.id ASC'
        );
        $stmt->execute([':student_id' => $studentId]);
        return $stmt->fetchAll();
    }
    
    public function deleteByStudent(int $studentId): void {
        $stmt = $this->pdo->prepare('DELETE FROM gpa_records WHERE student_id = :student_id');
        $stmt->execute([':student_id' => $studentId]);
    }
}