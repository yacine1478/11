<?php

class StudentController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function handle($action) {
        requireRole('student');

        switch ($action) {
            case 'dashboard':
                $this->dashboard();
                break;
            case 'history':
                $this->history();
                break;
            default:
                $this->dashboard();
                break;
        }
    }

    private function dashboard() {
        require_once 'views/student/dashboard.php';
    }

    private function history() {
        require_once 'views/student/history.php';
    }
}