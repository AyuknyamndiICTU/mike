<?php
require_once __DIR__ . '/../config/database.php';

try {
    // Create users table with proper structure
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('user', 'admin') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Create events table
    $pdo->exec("CREATE TABLE IF NOT EXISTS events (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        event_date DATE NOT NULL,
        event_time TIME NOT NULL,
        venue VARCHAR(255) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        capacity INT NOT NULL,
        available_seats INT NOT NULL,
        image_url VARCHAR(255),
        category VARCHAR(50),
        status ENUM('active', 'cancelled', 'completed') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    // Create bookings table
    $pdo->exec("CREATE TABLE IF NOT EXISTS bookings (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        event_id INT NOT NULL,
        quantity INT NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
        booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (event_id) REFERENCES events(id)
    )");

    // Create cart table
    $pdo->exec("CREATE TABLE IF NOT EXISTS cart (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        event_id INT NOT NULL,
        quantity INT NOT NULL,
        added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (event_id) REFERENCES events(id)
    )");

    echo "Database tables created successfully!<br>";
} catch (PDOException $e) {
    die("Error creating tables: " . $e->getMessage());
}

// Create admin account if it doesn't exist
try {
    $admin_username = "admin";
    $admin_email = "admin@example.com";
    $admin_password = "admin123";
    $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

    // Check if admin exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$admin_email, $admin_username]);
    
    if ($stmt->rowCount() === 0) {
        // Create admin account
        $sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'admin')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$admin_username, $admin_email, $hashed_password]);
        
        echo "Admin account created successfully!<br>";
        echo "Username: " . $admin_username . "<br>";
        echo "Email: " . $admin_email . "<br>";
        echo "Password: " . $admin_password . "<br>";
        echo "<br>Please change these credentials immediately after logging in!";
    } else {
        echo "Admin account already exists.<br>";
    }
} catch (PDOException $e) {
    echo "Error creating admin account: " . $e->getMessage();
}
?> 