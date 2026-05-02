<?php

class Assignment {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function assign(int $professorId, int $courseId, int $semesterId): bool {
        $stmt = $this->pdo->prepare(
            'INSERT IGNORE INTO assignments (professor_id, course_id, semester_id) 
             VALUES (:professor_id, :course_id, :semester_id)'
        );
        return $stmt->execute([
            ':professor_id' => $professorId,
            ':course_id' => $courseId,
            ':semester_id' => $semesterId
        ]);
    }
    
    public function unassign(int $professorId, int $courseId, int $semesterId): bool {
        $stmt = $this->pdo->prepare(
            'DELETE FROM assignments WHERE professor_id = :professor_id AND course_id = :course_id AND semester_id = :semester_id'
        );
        return $stmt->execute([
            ':professor_id' => $professorId,
            ':course_id' => $courseId,
            ':semester_id' => $semesterId
        ]);
    }
    
    public function exists(int $professorId, int $courseId, int $semesterId): bool {
        $stmt = $this->pdo->prepare(
            'SELECT 1 FROM assignments WHERE professor_id = :professor_id AND course_id = :course_id AND semester_id = :semester_id'
        );
        $stmt->execute([
            ':professor_id' => $professorId,
            ':course_id' => $courseId,
            ':semester_id' => $semesterId
        ]);
        return (bool) $stmt->fetch();
    }
    
    public function getCoursesByProfessor(int $professorId): array {
        $stmt = $this->pdo->prepare(
            'SELECT c.id, c.name, c.credits, s.id as semester_id, s.label as semester_label
             FROM assignments a
             JOIN courses c ON a.course_id = c.id
             JOIN semesters s ON a.semester_id = s.id
             WHERE a.professor_id = :professor_id
             ORDER BY s.id DESC, c.name'
        );
        $stmt->execute([':professor_id' => $professorId]);
        return $stmt->fetchAll();
    }

    public function getCoursesByProfessorAndSemester(int $professorId, int $semesterId): array {
        $stmt = $this->pdo->prepare(
            'SELECT c.id, c.name, c.credits
             FROM assignments a
             JOIN courses c ON a.course_id = c.id
             WHERE a.professor_id = :professor_id AND a.semester_id = :semester_id
             ORDER BY c.name'
        );
        $stmt->execute([
            ':professor_id' => $professorId,
            ':semester_id' => $semesterId
        ]);
        return $stmt->fetchAll();
    }
    
    public function getProfessorsByCourse(int $courseId, int $semesterId): array {
        $stmt = $this->pdo->prepare(
            'SELECT u.id, u.name FROM assignments a
             JOIN users u ON a.professor_id = u.id
             WHERE a.course_id = :course_id AND a.semester_id = :semester_id'
        );
        $stmt->execute([':course_id' => $courseId, ':semester_id' => $semesterId]);
        return $stmt->fetchAll();
    }
    
    public function courseAlreadyAssigned(int $courseId, int $semesterId): bool {
        $stmt = $this->pdo->prepare(
            'SELECT 1 FROM assignments WHERE course_id = :course_id AND semester_id = :semester_id'
        );
        $stmt->execute([':course_id' => $courseId, ':semester_id' => $semesterId]);
        return (bool) $stmt->fetch();
    }
}