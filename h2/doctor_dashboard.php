<?php
session_start();
require_once __DIR__ . '/config/database.php';

if (!isset($_SESSION['doctor_id'])) {
    header('Location: login.php');
    exit();
}

$pdo = getConnection();
$doctor = $pdo->prepare("SELECT * FROM doctors WHERE id = ?");
$doctor->execute([$_SESSION['doctor_id']]);
$doctor_data = $doctor->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <div class="auth-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="auth-card glass">
                        <h2>Welcome, Dr. <?php echo htmlspecialchars($doctor_data['name']); ?></h2>
                        <p class="text-white">Doctor Dashboard</p>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <a href="view-patients.php" class="btn btn-primary w-100">
                                    <i class="fas fa-users"></i> View Patients
                                </a>
                            </div>
                            <div class="col-md-6 mb-3">
                                <a href="emergency-services.php" class="btn btn-danger w-100">
                                    <i class="fas fa-ambulance"></i> Emergency Services
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