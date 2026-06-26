<?php
require_once __DIR__ . '/config/database.php';

try {
    $pdo = getConnection();
    
    // Create admin user
    $username = 'admin';
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $email = 'admin@hospital.com';
    $full_name = 'Hospital Administrator';
    
    $stmt = $pdo->prepare("INSERT INTO admin (username, password, email, full_name) VALUES (?, ?, ?, ?)");
    
    if ($stmt->execute([$username, $password, $email, $full_name])) {
        echo "Admin created successfully!<br>";
        echo "Username: admin<br>";
        echo "Password: admin123";
    } else {
        echo "Failed to create admin.";
    }
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>