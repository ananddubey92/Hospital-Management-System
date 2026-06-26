<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$error = '';
$success = '';

if ($_POST) {
    $patient_name = sanitize($_POST['patient_name']);
    $patient_id = sanitize($_POST['patient_id']);
    $phone = sanitize($_POST['phone']);
    $complaint_type = sanitize($_POST['complaint_type']);
    $complaint_text = sanitize($_POST['complaint_text']);
    
    if (empty($patient_name) || empty($patient_id) || empty($phone) || empty($complaint_type) || empty($complaint_text)) {
        $error = 'Please fill in all required fields';
    } else {
        $pdo = getConnection();
        
        // Create complaints table if not exists
        $pdo->exec("CREATE TABLE IF NOT EXISTS complaints (
            id INT AUTO_INCREMENT PRIMARY KEY,
            complaint_id VARCHAR(20),
            patient_name VARCHAR(100),
            patient_id VARCHAR(20),
            phone VARCHAR(15),
            complaint_type VARCHAR(50),
            complaint_text TEXT,
            status ENUM('Pending', 'In Review', 'Resolved') DEFAULT 'Pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        $complaint_id = 'CMP' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
        
        $stmt = $pdo->prepare("INSERT INTO complaints (complaint_id, patient_name, patient_id, phone, complaint_type, complaint_text) VALUES (?, ?, ?, ?, ?, ?)");
        
        if ($stmt->execute([$complaint_id, $patient_name, $patient_id, $phone, $complaint_type, $complaint_text])) {
            $success = "Complaint submitted successfully! Complaint ID: $complaint_id";
        } else {
            $error = 'Failed to submit complaint. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Complaints - MediCare Plus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="glass p-4">
                    <h2 class="text-white text-center mb-4">
                        <i class="fas fa-comment-medical text-warning"></i> Submit Complaint
                    </h2>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="form-row">
                            <div>
                                <label class="form-label">Patient Name *</label>
                                <input type="text" class="form-control" name="patient_name" required>
                            </div>
                            <div>
                                <label class="form-label">Patient ID *</label>
                                <input type="text" class="form-control" name="patient_id" placeholder="e.g., PAT001" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div>
                                <label class="form-label">Phone Number *</label>
                                <input type="tel" class="form-control" name="phone" required>
                            </div>
                            <div>
                                <label class="form-label">Complaint Type *</label>
                                <select class="form-control" name="complaint_type" required>
                                    <option value="">Select Type</option>
                                    <option value="Service Quality">Service Quality</option>
                                    <option value="Staff Behavior">Staff Behavior</option>
                                    <option value="Billing Issue">Billing Issue</option>
                                    <option value="Appointment Delay">Appointment Delay</option>
                                    <option value="Facility Issue">Facility Issue</option>
                                    <option value="Medical Care">Medical Care</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Complaint Details *</label>
                            <textarea class="form-control" name="complaint_text" rows="5" placeholder="Please describe your complaint in detail..." required></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-paper-plane"></i> Submit Complaint
                        </button>
                    </form>
                    
                    <div class="text-center mt-4">
                        <a href="index.php" class="text-info">
                            <i class="fas fa-home"></i> Back to Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>