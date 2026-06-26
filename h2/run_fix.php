<?php
require_once __DIR__ . '/config/database.php';

try {
    $pdo = getConnection();
    
    // Add the new column
    $pdo->exec("ALTER TABLE departments ADD COLUMN head_of_department VARCHAR(100)");
    
    // Copy data from old column to new column
    $pdo->exec("UPDATE departments SET head_of_department = head_doctor");
    
    // Drop the old column
    $pdo->exec("ALTER TABLE departments DROP COLUMN head_doctor");
    
    echo "Database fixed successfully!";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>