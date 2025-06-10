<?php
// Simple test page to debug form submission issues
require_once 'config/database.php';

echo "<h1>Form Submission Test</h1>";

// Test database connection
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "<p>✅ Database connection: OK (Users table has {$result['count']} records)</p>";
} catch (Exception $e) {
    echo "<p>❌ Database connection: FAILED - " . $e->getMessage() . "</p>";
}

// Test POST data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>POST Data Received:</h2>";
    echo "<pre>" . print_r($_POST, true) . "</pre>";
    
    if (isset($_POST['test_login'])) {
        echo "<p>🔍 Testing login process...</p>";
        
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            echo "<p>❌ Empty fields detected</p>";
        } else {
            try {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                
                if ($user) {
                    echo "<p>✅ User found: " . htmlspecialchars($user['username']) . "</p>";
                    if (password_verify($password, $user['password'])) {
                        echo "<p>✅ Password correct</p>";
                        echo "<p>🎉 Login would succeed!</p>";
                    } else {
                        echo "<p>❌ Password incorrect</p>";
                    }
                } else {
                    echo "<p>❌ User not found</p>";
                }
            } catch (Exception $e) {
                echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
            }
        }
    }
    
    if (isset($_POST['test_register'])) {
        echo "<p>🔍 Testing registration process...</p>";
        
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $full_name = trim($_POST['full_name'] ?? '');
        
        if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
            echo "<p>❌ Missing required fields</p>";
        } else {
            echo "<p>✅ All required fields present</p>";
            echo "<p>🎉 Registration would proceed!</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Form Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        form { border: 1px solid #ccc; padding: 20px; margin: 20px 0; }
        input { display: block; margin: 10px 0; padding: 8px; width: 200px; }
        button { padding: 10px 20px; margin: 10px 0; }
    </style>
</head>
<body>

<h2>Test Login Form</h2>
<form method="POST">
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit" name="test_login">Test Login</button>
</form>

<h2>Test Register Form</h2>
<form method="POST">
    <input type="text" name="username" placeholder="Username" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <input type="text" name="full_name" placeholder="Full Name" required>
    <input type="tel" name="phone" placeholder="Phone (optional)">
    <button type="submit" name="test_register">Test Register</button>
</form>

<h2>Existing Users (for testing)</h2>
<?php
try {
    $stmt = $pdo->query("SELECT username, email FROM users LIMIT 5");
    $users = $stmt->fetchAll();
    if ($users) {
        echo "<ul>";
        foreach ($users as $user) {
            echo "<li>" . htmlspecialchars($user['username']) . " (" . htmlspecialchars($user['email']) . ")</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No users found in database</p>";
    }
} catch (Exception $e) {
    echo "<p>Error fetching users: " . $e->getMessage() . "</p>";
}
?>

</body>
</html>
