<?php

class AuthController {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            
            $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email');
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['last_activity'] = time();
                
                // Redirect based on role
                switch ($user['role']) {
                    case 'admin':
                        header("Location: index.php?page=admin.dashboard");
                        break;
                    case 'professor':
                        header("Location: index.php?page=professor.grades");
                        break;
                    case 'student':
                        header("Location: index.php?page=student.dashboard");
                        break;
                }
                exit;
            } else {
                $error = "Invalid email or password";
            }
        }
        
        require_once 'views/login.php';
    }
    
    public function logout() {
        session_destroy();
        header("Location: index.php?page=login");
        exit;
    }
}