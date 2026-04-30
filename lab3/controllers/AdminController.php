<?php

class AdminController {
    private $pdo;
    private $userModel;
    private $semesterModel;
    private $courseModel;
    private $enrollmentModel;
    private $assignmentModel;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        require_once 'models/User.php';
        require_once 'models/Semester.php';
        require_once 'models/Course.php';
        require_once 'models/Enrollment.php';
        require_once 'models/Assignment.php';
        
        $this->userModel = new User($pdo);
        $this->semesterModel = new Semester($pdo);
        $this->courseModel = new Course($pdo);
        $this->enrollmentModel = new Enrollment($pdo);
        $this->assignmentModel = new Assignment($pdo);
    }
    
    public function handle($action) {
        requireRole('admin');
        
        switch ($action) {
            case 'dashboard':
                $this->dashboard();
                break;
            case 'semesters':
                $this->semesters();
                break;
            case 'courses':
                $this->courses();
                break;
            case 'professors':
                $this->professors();
                break;
            case 'students':
                $this->students();
                break;
            case 'enrollments':
                $this->enrollments();
                break;
            default:
                $this->dashboard();
                break;
        }
    }
    
    private function dashboard() {
        $studentCount = $this->userModel->countByRole('student');
        $professorCount = $this->userModel->countByRole('professor');
        $semesterCount = count($this->semesterModel->getAll());
        $courseCount = count($this->courseModel->getAll());
        
        require_once 'views/admin/dashboard.php';
    }
    
    private function semesters() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            if ($action === 'create') {
                $label = trim($_POST['label'] ?? '');
                $academicYear = trim($_POST['academic_year'] ?? '');

                if ($label === '' || $academicYear === '') {
                    flash('danger', 'Label and academic year are required.');
                } else {
                    $this->semesterModel->create($label, $academicYear);
                    flash('success', 'Semester created successfully.');
                }
            }

            if ($action === 'activate') {
                $id = (int) ($_POST['id'] ?? 0);
                if ($id > 0) {
                    $this->semesterModel->setActive($id);
                    flash('success', 'Semester activated successfully.');
                } else {
                    flash('danger', 'Invalid semester ID.');
                }
            }

            if ($action === 'delete') {
                $id = (int) ($_POST['id'] ?? 0);
                if ($id > 0) {
                    $deleted = $this->semesterModel->delete($id);
                    if ($deleted) {
                        flash('success', 'Semester deleted successfully.');
                    } else {
                        flash('danger', 'Semester could not be deleted. Remove related courses first.');
                    }
                } else {
                    flash('danger', 'Invalid semester ID.');
                }
            }

            header('Location: index.php?page=admin.semesters');
            exit;
        }

        $semesters = $this->semesterModel->getAll();
        require_once 'views/admin/semesters.php';
    }
    
    private function courses() {
        $courses = $this->courseModel->getAll();
        $semesters = $this->semesterModel->getAll();
        require_once 'views/admin/courses.php';
    }
    
    private function professors() {
        $professors = $this->userModel->getAllByRole('professor');
        require_once 'views/admin/professors.php';
    }
    
    private function students() {
        $students = $this->userModel->getAllByRole('student');
        require_once 'views/admin/students.php';
    }
    
    private function enrollments() {
        $studentId = $_GET['student_id'] ?? 0;
        $students = $this->userModel->getAllByRole('student');
        
        if ($studentId) {
            $allSemesters = $this->semesterModel->getAll();
            $enrolledStatus = [];
            foreach ($allSemesters as $semester) {
                $enrolledStatus[$semester['id']] = $this->enrollmentModel->isEnrolled($studentId, $semester['id']);
            }
            $selectedStudent = $this->userModel->findById($studentId);
        }
        
        require_once 'views/admin/enrollments.php';
    }
}