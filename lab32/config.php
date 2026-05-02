<?php

// 🔥 SHOW ERRORS (for development)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 🔹 DATABASE CONNECTION
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=lab3;charset=utf8",
        "root",
        ""
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// 🔹 SESSION START
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 🔐 ROLE CHECK FUNCTION
function requireRole($role)
{
    if (!isset($_SESSION['role'])) {
        header("Location: index.php?page=login");
        exit;
    }

    // Session timeout (30 min)
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
        session_destroy();
        header("Location: index.php?page=login");
        exit;
    }

    if ($_SESSION['role'] !== $role) {
        http_response_code(403);
        die("Access Denied");
    }

    $_SESSION['last_activity'] = time();
}

// 🔹 HELPER (SAFE OUTPUT)
function e($value)
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// 🔹 FLASH MESSAGES
function flash($type, $message)
{
    if (!isset($_SESSION['flash'])) {
        $_SESSION['flash'] = [];
    }
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash()
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}