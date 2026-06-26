<?php
session_start();
require_once __DIR__ . '/config/database.php';

if (!isset($_SESSION['patient_id'])) {
    header('Location: login.php');
    exit();
}

$pdo = getConnection();
$patient = $pdo->prepare("SELECT * FROM patients WHERE id = ?");
$patient->execute([$_SESSION['patient_id']]);
$patient_data = $patient->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <div class="auth-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="auth-card glass">
                        <h2>Welcome, <?php echo htmlspecialchars($patient_data['name']); ?></h2>
                        <p class="text-white">Patient Dashboard</p>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <a href="schedule-appointment.php" class="btn btn-primary w-100">
                                    <i class="fas fa-calendar"></i> Book Appointment
                                </a>
                            </div>
                            <div class="col-md-4 mb-3">
                                <a href="emergency-services.php" class="btn btn-danger w-100">
                                    <i class="fas fa-ambulance"></i> Emergency Services
                                </a>
                            </div>
                            <div class="col-md-4 mb-3">
                                <a href="patient-complaint.php" class="btn btn-warning w-100">
                                    <i class="fas fa-comment-medical"></i> Complaint
                                </a>
                            </div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <a href="logout.php" class="btn btn-secondary">Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>