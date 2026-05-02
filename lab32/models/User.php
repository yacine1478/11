<?php

class User {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function findByEmail(string $email): ?array {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }
    
    public function findById(int $id): ?array {
        $stmt = $this->pdo->prepare('SELECT id, name, email, role, created_at FROM users WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }
    
    public function create(string $name, string $email, string $password, string $role): int {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)'
        );
        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':password' => password_hash($password, PASSWORD_BCRYPT),
            ':role' => $role
        ]);
        return (int) $this->pdo->lastInsertId();
    }
    
    public function update(int $id, string $name, string $email): bool {
        $stmt = $this->pdo->prepare('UPDATE users SET name = :name, email = :email WHERE id = :id');
        return $stmt->execute([':id' => $id, ':name' => $name, ':email' => $email]);
    }
    
    public function updatePassword(int $id, string $password): bool {
        $stmt = $this->pdo->prepare('UPDATE users SET password = :password WHERE id = :id');
        return $stmt->execute([':id' => $id, ':password' => password_hash($password, PASSWORD_BCRYPT)]);
    }
    
    public function delete(int $id): bool {
        $stmt = $this->pdo->prepare('DELETE FROM users WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }
    
    public function emailExists(string $email, ?int $excludeId = null): bool {
        if ($excludeId) {
            $stmt = $this->pdo->prepare('SELECT 1 FROM users WHERE email = :email AND id != :exclude_id');
            $stmt->execute([':email' => $email, ':exclude_id' => $excludeId]);
        } else {
            $stmt = $this->pdo->prepare('SELECT 1 FROM users WHERE email = :email');
            $stmt->execute([':email' => $email]);
        }
        return (bool) $stmt->fetch();
    }
    
    public function getAllByRole(string $role): array {
        $stmt = $this->pdo->prepare('SELECT id, name, email, created_at FROM users WHERE role = :role ORDER BY name');
        $stmt->execute([':role' => $role]);
        return $stmt->fetchAll();
    }
    
    public function countByRole(string $role): int {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM users WHERE role = :role');
        $stmt->execute([':role' => $role]);
        return (int) $stmt->fetchColumn();
    }
}