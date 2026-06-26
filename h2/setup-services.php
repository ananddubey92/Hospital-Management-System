<?php
require_once __DIR__ . '/config/database.php';

try {
    $pdo = getConnection();
    
    // Create services tables
    $pdo->exec("CREATE TABLE IF NOT EXISTS emergency_cases (
        id INT AUTO_INCREMENT PRIMARY KEY,
        case_id VARCHAR(20) UNIQUE,
        patient_name VARCHAR(100),
        contact_number VARCHAR(15),
        emergency_type VARCHAR(50),
        priority_level ENUM('Low', 'Medium', 'High', 'Critical'),
        status ENUM('Pending', 'In Progress', 'Completed', 'Cancelled') DEFAULT 'Pending',
        assigned_doctor INT,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS diagnostic_tests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        test_id VARCHAR(20) UNIQUE,
        patient_name VARCHAR(100),
        test_type VARCHAR(100),
        doctor_id INT,
        test_date DATE,
        status ENUM('Scheduled', 'In Progress', 'Completed', 'Cancelled') DEFAULT 'Scheduled',
        results TEXT,
        cost DECIMAL(10,2),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS surgeries (
        id INT AUTO_INCREMENT PRIMARY KEY,
        surgery_id VARCHAR(20) UNIQUE,
        patient_name VARCHAR(100),
        surgery_type VARCHAR(100),
        surgeon_id INT,
        surgery_date DATE,
        surgery_time TIME,
        status ENUM('Scheduled', 'In Progress', 'Completed', 'Cancelled') DEFAULT 'Scheduled',
        operation_theater VARCHAR(10),
        notes TEXT,
        cost DECIMAL(10,2),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS inpatients (
        id INT AUTO_INCREMENT PRIMARY KEY,
        admission_id VARCHAR(20) UNIQUE,
        patient_name VARCHAR(100),
        room_number VARCHAR(10),
        admission_date DATE,
        discharge_date DATE,
        attending_doctor INT,
        condition_status VARCHAR(100),
        daily_cost DECIMAL(10,2),
        total_cost DECIMAL(10,2),
        status ENUM('Admitted', 'Discharged', 'Transferred') DEFAULT 'Admitted',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Insert sample data
    $services = [
        ['EMRG001', 'Emergency Care', 'Immediate medical attention for critical cases', 0, '24/7', 'emergency'],
        ['DIAG001', 'X-Ray', 'Digital X-ray imaging', 500, '30 minutes', 'scan'],
        ['DIAG002', 'MRI Scan', 'Magnetic Resonance Imaging', 3000, '45 minutes', 'scan'],
        ['SURG001', 'General Surgery', 'Various surgical procedures', 15000, '2-4 hours', 'procedure'],
        ['INPT001', 'General Ward', 'Standard inpatient care', 2000, 'Per day', 'therapy']
    ];
    
    foreach ($services as $service) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO services (service_name, description, price, duration, category) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$service[1], $service[2], $service[3], $service[4], $service[5]]);
    }
    
    header('Location: setup-success.php?message=' . urlencode('Medical services setup completed successfully!') . '&redirect=services.php');
    exit();
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>