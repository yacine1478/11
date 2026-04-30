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
}