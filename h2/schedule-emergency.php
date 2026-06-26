<?php
require_once __DIR__ . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = getConnection();
        
        // Create emergencies table if it doesn't exist
        $pdo->exec("CREATE TABLE IF NOT EXISTS emergencies (
            id INT AUTO_INCREMENT PRIMARY KEY,
            patient_name VARCHAR(100) NOT NULL,
            contact_number VARCHAR(15) NOT NULL,
            emergency_type VARCHAR(50) NOT NULL,
            priority_level ENUM('Low', 'Medium', 'High', 'Critical') NOT NULL,
            status ENUM('Pending', 'In Progress', 'Completed', 'Cancelled') DEFAULT 'Pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        $stmt = $pdo->prepare("INSERT INTO emergencies (patient_name, contact_number, emergency_type, priority_level, status, created_at) VALUES (?, ?, ?, ?, 'Pending', NOW())");
        
        if ($stmt->execute([$_POST['patient_name'], $_POST['contact_number'], $_POST['emergency_type'], $_POST['priority_level']])) {
            $success = "Emergency case added successfully!";
        } else {
            $error = "Failed to add emergency case.";
        }
    } catch(PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Emergency Case - Hospital Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <div class="auth-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="auth-card glass">
                        <h2><i class="fas fa-plus-circle"></i> Add Emergency Case</h2>
                        
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label text-white">Patient Name</label>
                                <input type="text" class="form-control" name="patient_name" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label text-white">Contact Number</label>
                                <input type="tel" class="form-control" name="contact_number" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label text-white">Emergency Type</label>
                                <select class="form-control" name="emergency_type" required>
                                    <option value="">Select Emergency Type</option>
                                    <option value="Cardiac Arrest">Cardiac Arrest</option>
                                    <option value="Accident">Accident</option>
                                    <option value="Stroke">Stroke</option>
                                    <option value="Respiratory Failure">Respiratory Failure</option>
                                    <option value="Trauma">Trauma</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label text-white">Priority Level</label>
                                <select class="form-control" name="priority_level" required>
                                    <option value="">Select Priority</option>
                                    <option value="Critical">Critical</option>
                                    <option value="High">High</option>
                                    <option value="Medium">Medium</option>
                                    <option value="Low">Low</option>
                                </select>
                            </div>
                            
                            <div class="text-center">
                                <button type="submit" class="btn btn-danger me-2">
                                    <i class="fas fa-save"></i> Add Emergency Case
                                </button>
                                <a href="emergency-services.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>