<?php
require_once '../config.php';

header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if ($_SESSION['role'] !== 'student') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

$action = $_GET['action'] ?? null;
$studentId = (int) $_SESSION['user_id'];

switch ($action) {
    case 'current':
        // Get active semester grades
        $stmt = $pdo->query('SELECT id, label, academic_year FROM semesters WHERE is_active = 1 LIMIT 1');
        $semester = $stmt->fetch();
        
        if (!$semester) {
            echo json_encode(['error' => 'No active semester']);
            break;
        }
        
        // Check if student is enrolled
        $stmt = $pdo->prepare('SELECT 1 FROM enrollments WHERE student_id = :student_id AND semester_id = :semester_id');
        $stmt->execute([
            ':student_id' => $studentId,
            ':semester_id' => $semester['id'],
        ]);
        
        if (!$stmt->fetch()) {
            echo json_encode(['error' => 'Not enrolled in active semester']);
            break;
        }
        
        // Get courses with grades
        $stmt = $pdo->prepare(
            'SELECT c.id, c.name, c.credits, g.grade 
             FROM courses c
             LEFT JOIN grades g ON g.course_id = c.id AND g.student_id = :student_id AND g.semester_id = :semester_id
             WHERE c.semester_id = :semester_id
             ORDER BY c.name'
        );
        $stmt->execute([
            ':student_id' => $studentId,
            ':semester_id' => $semester['id'],
        ]);
        $courses = $stmt->fetchAll();
        
        // Get GPA
        $stmt = $pdo->prepare('SELECT gpa FROM gpa_records WHERE student_id = :student_id AND semester_id = :semester_id');
        $stmt->execute([
            ':student_id' => $studentId,
            ':semester_id' => $semester['id'],
        ]);
        $gpaRecord = $stmt->fetch();
        
        echo json_encode([
            'semester' => $semester,
            'courses' => $courses,
            'gpa' => $gpaRecord ? (float) $gpaRecord['gpa'] : null
        ]);
        break;
        
    case 'history':
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
        }

        $stmt = $pdo->prepare(
            'SELECT gr.semester_id, s.label, s.academic_year, gr.gpa
             FROM gpa_records gr
             JOIN semesters s ON gr.semester_id = s.id
             WHERE gr.student_id = :student_id
             ORDER BY s.academic_year DESC, s.label'
        );
        $stmt->execute([':student_id' => $studentId]);
        $history = $stmt->fetchAll();

        foreach ($history as &$semester) {
            $details = $pdo->prepare(
                'SELECT c.name, c.credits, g.grade
                 FROM courses c
                 LEFT JOIN grades g ON g.course_id = c.id AND g.student_id = :student_id AND g.semester_id = :semester_id
                 WHERE c.semester_id = :semester_id
                 ORDER BY c.name'
            );
            $details->execute([
                ':student_id' => $studentId,
                ':semester_id' => $semester['semester_id'],
            ]);
            $semester['courses'] = $details->fetchAll();
        }

        echo json_encode($history);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
}
