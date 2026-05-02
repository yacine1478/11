<?php
require_once 'config.php';

$page = $_GET['page'] ?? 'login';

// Route to appropriate controller
switch (true) {
    case $page === 'login':
    case $page === 'logout':
        require_once 'controllers/AuthController.php';
        $controller = new AuthController($pdo);
        if ($page === 'logout') {
            $controller->logout();
        } else {
            $controller->login();
        }
        break;
        
    case str_starts_with($page, 'admin'):
        require_once 'controllers/AdminController.php';
        $controller = new AdminController($pdo);
        $action = str_replace('admin.', '', $page);
        $controller->handle($action);
        break;
        
    case str_starts_with($page, 'professor'):
        require_once 'controllers/ProfessorController.php';
        $controller = new ProfessorController($pdo);
        $action = str_replace('professor.', '', $page);
        $controller->handle($action);
        break;
        
    case str_starts_with($page, 'student'):
        require_once 'controllers/StudentController.php';
        $controller = new StudentController($pdo);
        $action = str_replace('student.', '', $page);
        $controller->handle($action);
        break;
        
    default:
        header("Location: index.php?page=login");
        exit;
}