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
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            if ($action === 'create') {
                $name = trim($_POST['name'] ?? '');
                $semesterId = (int) ($_POST['semester_id'] ?? 0);
                $credits = (int) ($_POST['credits'] ?? 0);

                if ($name === '' || !$semesterId || $credits <= 0) {
                    flash('danger', 'Course name, semester and credits are required.');
                } else {
                    $this->courseModel->create($name, $semesterId, $credits);
                    flash('success', 'Course created successfully.');
                }
            }

            if ($action === 'delete') {
                $id = (int) ($_POST['id'] ?? 0);
                if ($id > 0) {
                    if ($this->courseModel->delete($id)) {
                        flash('success', 'Course deleted successfully.');
                    } else {
                        flash('danger', 'Course could not be deleted.');
                    }
                }
            }

            header('Location: index.php?page=admin.courses');
            exit;
        }

        $courses = $this->courseModel->getAll();
        $semesters = $this->semesterModel->getAll();
        require_once 'views/admin/courses.php';
    }
    
    private function professors() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            if ($action === 'create') {
                $name = trim($_POST['name'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $password = trim($_POST['password'] ?? '');

                if ($name === '' || $email === '') {
                    flash('danger', 'Name and email are required.');
                } elseif ($this->userModel->emailExists($email)) {
                    flash('danger', 'Email already exists.');
                } else {
                    if ($password === '') {
                        $password = 'password123';
                    }
                    $this->userModel->create($name, $email, $password, 'professor');
                    flash('success', 'Professor account created successfully.');
                }
            }

            if ($action === 'delete') {
                $id = (int) ($_POST['id'] ?? 0);
                if ($id > 0) {
                    $this->pdo->prepare('DELETE FROM assignments WHERE professor_id = :id')->execute([':id' => $id]);
                    if ($this->userModel->delete($id)) {
                        flash('success', 'Professor removed successfully.');
                    } else {
                        flash('danger', 'Professor could not be removed.');
                    }
                }
            }

            header('Location: index.php?page=admin.professors');
            exit;
        }

        $professors = $this->userModel->getAllByRole('professor');
        require_once 'views/admin/professors.php';
    }
    
    private function students() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            if ($action === 'create') {
                $name = trim($_POST['name'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $password = trim($_POST['password'] ?? '');

                if ($name === '' || $email === '') {
                    flash('danger', 'Name and email are required.');
                } elseif ($this->userModel->emailExists($email)) {
                    flash('danger', 'Email already exists.');
                } else {
                    if ($password === '') {
                        $password = 'password123';
                    }
                    $this->userModel->create($name, $email, $password, 'student');
                    flash('success', 'Student account created successfully.');
                }
            }

            if ($action === 'delete') {
                $id = (int) ($_POST['id'] ?? 0);
                if ($id > 0) {
                    $this->pdo->prepare('DELETE FROM grades WHERE student_id = :id')->execute([':id' => $id]);
                    $this->pdo->prepare('DELETE FROM enrollments WHERE student_id = :id')->execute([':id' => $id]);
                    $this->pdo->prepare('DELETE FROM gpa_records WHERE student_id = :id')->execute([':id' => $id]);
                    if ($this->userModel->delete($id)) {
                        flash('success', 'Student removed successfully.');
                    } else {
                        flash('danger', 'Student could not be removed.');
                    }
                }
            }

            header('Location: index.php?page=admin.students');
            exit;
        }

        $students = $this->userModel->getAllByRole('student');
        require_once 'views/admin/students.php';
    }
    
    private function enrollments() {
        $students = $this->userModel->getAllByRole('student');
        $allSemesters = $this->semesterModel->getAll();
        $selectedStudent = null;
        $enrolledSemesterIds = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $studentId = (int) ($_POST['student_id'] ?? 0);
            $semesterIds = array_map('intval', $_POST['semester_ids'] ?? []);

            if ($studentId > 0) {
                $this->enrollmentModel->updateForStudent($studentId, $semesterIds);
                flash('success', 'Enrollments updated successfully.');
                header('Location: index.php?page=admin.enrollments&student_id=' . $studentId);
                exit;
            }
        }

        $selectedId = (int) ($_GET['student_id'] ?? 0);
        if ($selectedId) {
            $selectedStudent = $this->userModel->findById($selectedId);
            if ($selectedStudent) {
                $enrolledSemesterIds = $this->enrollmentModel->getStudentSemesterIds($selectedId);
            }
        }

        require_once 'views/admin/enrollments.php';
    }
}