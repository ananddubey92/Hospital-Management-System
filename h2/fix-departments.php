<?php
require_once __DIR__ . '/config/database.php';

try {
    $pdo = getConnection();
    
    // Fix departments table
    $pdo->exec("ALTER TABLE departments ADD COLUMN IF NOT EXISTS head_of_department VARCHAR(100)");
    
    // Insert the three main departments
    $departments = [
        ['CARD001', 'Cardiology', 'Heart and cardiovascular system treatment', 'Dr. John Smith'],
        ['NEUR001', 'Neurology', 'Brain and nervous system disorders', 'Dr. Sarah Johnson'],
        ['ORTH001', 'Orthopedics', 'Bone, joint and muscle treatment', 'Dr. Michael Brown']
    ];
    
    foreach ($departments as $dept) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO departments (dept_id, name, description, head_of_department) VALUES (?, ?, ?, ?)");
        $stmt->execute($dept);
    }
    
    // Fix doctors table
    $pdo->exec("ALTER TABLE doctors ADD COLUMN IF NOT EXISTS status ENUM('active', 'inactive') DEFAULT 'active'");
    
    // Fix appointments table
    $pdo->exec("ALTER TABLE appointments ADD COLUMN IF NOT EXISTS patient_name VARCHAR(100)");
    $pdo->exec("ALTER TABLE appointments ADD COLUMN IF NOT EXISTS patient_phone VARCHAR(15)");
    
    echo "Database fixed successfully!";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>