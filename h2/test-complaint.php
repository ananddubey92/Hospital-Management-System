<?php
require_once 'config/database.php';

try {
    $pdo = getConnection();
    
    // Create complaints table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS complaints (
        id INT AUTO_INCREMENT PRIMARY KEY,
        complaint_id VARCHAR(20) UNIQUE,
        patient_name VARCHAR(100),
        patient_id VARCHAR(20),
        phone VARCHAR(15),
        complaint_type VARCHAR(50),
        complaint_text TEXT,
        status ENUM('Pending', 'In Review', 'Resolved') DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    echo "✅ Complaints table ready!<br>";
    echo "<a href='patient-complaint.php'>Test Patient Complaint</a> | ";
    echo "<a href='admin-complaints.php'>View Admin Complaints</a>";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>