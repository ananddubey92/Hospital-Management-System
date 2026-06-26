<?php
require_once __DIR__ . '/config/database.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Patients - Hospital Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <div class="auth-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="auth-card glass">
                        <h2><i class="fas fa-users"></i> Total Patients</h2>
                        <p class="text-center text-white mb-4">
                            View the complete number of registered patients in the hospital system.
                        </p>
                        
                        <div class="text-center">
                            <a href="total-patients.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-users"></i> View Total Patients
                            </a>
                        </div>
                        
                        <div class="text-center mt-4">
                            <a href="index.php" class="text-info">Back to Home</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>