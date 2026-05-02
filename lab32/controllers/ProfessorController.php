<?php

class ProfessorController {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function handle($action) {
        requireRole('professor');
        
        switch ($action) {
            case 'grades':
                $this->grades();
                break;
            default:
                $this->grades();
                break;
        }
    }
    
    private function grades() {
        // Get semesters where professor has assignments
        $stmt = $this->pdo->prepare(
            'SELECT DISTINCT s.id, s.label, s.academic_year 
             FROM semesters s
             JOIN assignments a ON a.semester_id = s.id
             WHERE a.professor_id = :professor_id
             ORDER BY s.id DESC'
        );
        $stmt->execute([':professor_id' => $_SESSION['user_id']]);
        $semesters = $stmt->fetchAll();
        
        require_once 'views/professor/grades.php';
    }
}