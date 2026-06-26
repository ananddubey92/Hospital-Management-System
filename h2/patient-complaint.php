<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['patient_id'])) {
    header('Location: login.php');
    exit();
}

$pdo = getConnection();
$patient = $pdo->prepare("SELECT * FROM patients WHERE id = ?");
$patient->execute([$_SESSION['patient_id']]);
$patient_data = $patient->fetch();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $complaint_type = $_POST['complaint_type'];
        $complaint_text = trim($_POST['complaint_text']);
        
        if (empty($complaint_text)) {
            throw new Exception('Please enter your complaint details.');
        }
        
        // Generate complaint ID
        $complaint_id = 'CMP' . date('Ymd') . rand(100, 999);
        
        $stmt = $pdo->prepare("INSERT INTO complaints (complaint_id, patient_name, patient_id, phone, complaint_type, complaint_text, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'Pending', NOW())");
        $stmt->execute([
            $complaint_id, 
            $patient_data['name'], 
            $patient_data['pat_id'], 
            $patient_data['phone'], 
            $complaint_type, 
            $complaint_text
        ]);
        
        $success = 'Your complaint has been submitted successfully! Complaint ID: ' . $complaint_id;
        $_POST = array();
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Complaint - Patient Portal</title>
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
                        <div class="text-center mb-4">
                            <h2><i class="fas fa-comment-medical text-warning"></i> Submit Complaint</h2>
                            <p class="text-white">Hello, <?php echo htmlspecialchars($patient_data['name']); ?></p>
                        </div>

                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label text-white">Patient Name</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($patient_data['name']); ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-white">Patient ID</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($patient_data['pat_id']); ?>" readonly>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label text-white">Complaint Type</label>
                                <select class="form-control" name="complaint_type" required>
                                    <option value="Service Quality" <?php echo ($_POST['complaint_type'] ?? '') == 'Service Quality' ? 'selected' : ''; ?>>Service Quality</option>
                                    <option value="Staff Behavior" <?php echo ($_POST['complaint_type'] ?? '') == 'Staff Behavior' ? 'selected' : ''; ?>>Staff Behavior</option>
                                    <option value="Billing Issue" <?php echo ($_POST['complaint_type'] ?? '') == 'Billing Issue' ? 'selected' : ''; ?>>Billing Issue</option>
                                    <option value="Facility Issue" <?php echo ($_POST['complaint_type'] ?? '') == 'Facility Issue' ? 'selected' : ''; ?>>Facility Issue</option>
                                    <option value="Appointment Issue" <?php echo ($_POST['complaint_type'] ?? '') == 'Appointment Issue' ? 'selected' : ''; ?>>Appointment Issue</option>
                                    <option value="Other" <?php echo ($_POST['complaint_type'] ?? '') == 'Other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label text-white">Complaint Details *</label>
                                <textarea class="form-control" name="complaint_text" rows="5" 
                                          placeholder="Please describe your complaint in detail..." required><?php echo $_POST['complaint_text'] ?? ''; ?></textarea>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-paper-plane"></i> Submit Complaint
                                </button>
                                <a href="patient_dashboard.php" class="btn btn-outline-light">
                                    <i class="fas fa-arrow-left"></i> Back to Dashboard
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