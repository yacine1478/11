<?php
require_once '../config.php';

header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if ($_SESSION['role'] !== 'professor') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? null;
$professorId = (int) $_SESSION['user_id'];

require_once '../models/Assignment.php';
require_once '../models/Enrollment.php';
require_once '../models/Grade.php';
require_once '../models/Course.php';
require_once '../models/Semester.php';

$assignmentModel = new Assignment($pdo);
$enrollmentModel = new Enrollment($pdo);
$gradeModel = new Grade($pdo);
$courseModel = new Course($pdo);
$semesterModel = new Semester($pdo);

switch ($action) {
    case 'courses':
        // GET: Get courses assigned to professor for a semester
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
        }
        
        $semesterId = (int) ($_GET['semester_id'] ?? 0);
        
        if (!$semesterId) {
            http_response_code(400);
            echo json_encode(['error' => 'Semester ID required']);
            break;
        }
        
        $courses = $assignmentModel->getCoursesByProfessorAndSemester($professorId, $semesterId);
        echo json_encode($courses);
        break;
        
    case 'students':
        // GET: Get enrolled students with existing grades for a course
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
        }
        
        $semesterId = (int) ($_GET['semester_id'] ?? 0);
        $courseId = (int) ($_GET['course_id'] ?? 0);
        
        if (!$semesterId || !$courseId) {
            http_response_code(400);
            echo json_encode(['error' => 'Semester ID and Course ID required']);
            break;
        }
        
        // Verify professor is assigned to this course/semester
        if (!$assignmentModel->exists($professorId, $courseId, $semesterId)) {
            http_response_code(403);
            echo json_encode(['error' => 'You are not assigned to this course for this semester']);
            break;
        }
        
        $students = $enrollmentModel->getStudentsBySemester($semesterId);
        
        // Add existing grades
        foreach ($students as &$student) {
            $grade = $gradeModel->get($student['id'], $courseId, $semesterId);
            $student['grade'] = $grade !== null ? (string) $grade : null;
        }
        
        echo json_encode($students);
        break;
        
    case 'save':
        // POST: Save grades
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
        }
        
        $semesterId = (int) ($_POST['semester_id'] ?? 0);
        $courseId = (int) ($_POST['course_id'] ?? 0);
        $grades = $_POST['grades'] ?? [];
        
        if (!$semesterId || !$courseId) {
            http_response_code(400);
            echo json_encode(['error' => 'Semester ID and Course ID required']);
            break;
        }
        
        // Verify professor is assigned
        if (!$assignmentModel->exists($professorId, $courseId, $semesterId)) {
            http_response_code(403);
            echo json_encode(['error' => 'You are not assigned to this course for this semester']);
            break;
        }
        
        $saved = 0;
        $validGrades = ['0.0', '1.0', '2.0', '3.0', '4.0'];
        
        foreach ($grades as $entry) {
            $studentId = (int) ($entry['student_id'] ?? 0);
            $grade = trim($entry['grade'] ?? '');
            
            if (!$studentId || !in_array($grade, $validGrades, true)) {
                continue;
            }
            
            $gradeModel->upsert($studentId, $courseId, $semesterId, $professorId, (float) $grade);
            $saved++;
        }
        
        echo json_encode(['success' => true, 'saved' => $saved]);
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
}