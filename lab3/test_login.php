<?php
require_once 'config.php';

echo "<h1>Login Debug Tool</h1>";

// Test database connection
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $userCount = $stmt->fetchColumn();
    echo "<p>✅ Database connected. Users in database: " . $userCount . "</p>";
} catch (Exception $e) {
    echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
}

// List all users
echo "<h2>Users in Database:</h2>";
$stmt = $pdo->query("SELECT id, name, email, role FROM users");
$users = $stmt->fetchAll();

echo "<table border='1' cellpadding='8'>";
echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Password Check</th></tr>";

foreach ($users as $user) {
    $testPassword = 'password123';
    $hashFromDb = null;
    
    // Get the hash for this user
    $stmt2 = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt2->execute([$user['id']]);
    $hashFromDb = $stmt2->fetchColumn();
    
    $passwordValid = password_verify($testPassword, $hashFromDb);
    $status = $passwordValid ? "✅ Valid" : "❌ Invalid";
    
    echo "<tr>";
    echo "<td>" . $user['id'] . "</td>";
    echo "<td>" . htmlspecialchars($user['name']) . "</td>";
    echo "<td>" . htmlspecialchars($user['email']) . "</td>";
    echo "<td>" . $user['role'] . "</td>";
    echo "<td>" . $status . "</td>";
    echo "</tr>";
}
echo "</table>";

// Test login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>Login Test Result:</h2>";
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    echo "<p>Testing with Email: " . htmlspecialchars($email) . "</p>";
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "<p>✅ User found in database</p>";
        if (password_verify($password, $user['password'])) {
            echo "<p style='color:green'>✅✅✅ PASSWORD MATCHES! Login would succeed!</p>";
        } else {
            echo "<p style='color:red'>❌ Password does NOT match</p>";
            echo "<p>Expected hash for 'password123': " . password_hash('password123', PASSWORD_BCRYPT) . "</p>";
        }
    } else {
        echo "<p style='color:red'>❌ No user found with email: " . htmlspecialchars($email) . "</p>";
    }
}
?>

<h2>Test Login Form:</h2>
<form method="post">
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Test Login</button>
</form>

<h2>If database is empty, run this SQL:</h2>
<pre>
-- Delete existing users and re-insert
DELETE FROM users;

INSERT INTO users (name, email, password, role) VALUES
('Admin User', 'admin@test.com', '<?php echo password_hash('password123', PASSWORD_BCRYPT); ?>', 'admin'),
('Dr. Smith', 'prof@test.com', '<?php echo password_hash('password123', PASSWORD_BCRYPT); ?>', 'professor'),
('Alice Johnson', 'student@test.com', '<?php echo password_hash('password123', PASSWORD_BCRYPT); ?>', 'student');
</pre>