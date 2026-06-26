CREATE TABLE IF NOT EXISTS emergencies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_name VARCHAR(100) NOT NULL,
    contact_number VARCHAR(15) NOT NULL,
    emergency_type VARCHAR(50) NOT NULL,
    priority_level ENUM('Low', 'Medium', 'High', 'Critical') NOT NULL,
    status ENUM('Pending', 'In Progress', 'Completed', 'Cancelled') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);