<?php
require_once __DIR__ . '/config/database.php';

try {
    $pdo = getConnection();
    
    // Create patients table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS patients (
        id INT AUTO_INCREMENT PRIMARY KEY,
        pat_id VARCHAR(20) UNIQUE NOT NULL,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(15) NOT NULL,
        address TEXT,
        date_of_birth DATE,
        gender ENUM('Male', 'Female', 'Other'),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM patients");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalPatients = $result['total'];
} catch(PDOException $e) {
    $totalPatients = 0;
    $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Total Patients - Hospital Management</title>
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
                        <h2><i class="fas fa-users"></i> Total Patients</h2>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="text-center mb-4">
                            <div class="display-1 text-primary">
                                <i class="fas fa-users"></i>
                            </div>
                            <h1 class="display-2 text-white"><?php echo $totalPatients; ?></h1>
                            <p class="lead text-white">Total Registered Patients</p>
                        </div>
                        
                        <div class="text-center">
                            <a href="view-patients.php" class="btn btn-secondary me-2">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                            <a href="index.php" class="btn btn-primary">
                                <i class="fas fa-home"></i> Home
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>