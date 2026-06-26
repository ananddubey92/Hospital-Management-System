<?php
require_once __DIR__ . '/config/database.php';

$dept_id = $_GET['dept'] ?? 0;

try {
    $pdo = getConnection();
    
    // Get department info
    $stmt = $pdo->prepare("SELECT * FROM departments WHERE id = ?");
    $stmt->execute([$dept_id]);
    $department = $stmt->fetch();
    
    // Get statistics
    $stats = [
        'doctors' => $pdo->prepare("SELECT COUNT(*) FROM doctors WHERE department_id = ? AND status = 'active'"),
        'appointments_today' => $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE department_id = ? AND appointment_date = CURDATE()"),
        'appointments_pending' => $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE department_id = ? AND status = 'pending'"),
        'appointments_total' => $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE department_id = ?")
    ];
    
    foreach ($stats as $key => $stmt) {
        $stmt->execute([$dept_id]);
        $stats[$key] = $stmt->fetchColumn();
    }
    
} catch(PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

$dept_colors = [
    'Cardiology' => '#dc3545',
    'Neurology' => '#0d6efd', 
    'Orthopedics' => '#198754'
];

$color = $dept_colors[$department['name']] ?? '#6c757d';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($department['name']); ?> Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, <?php echo $color; ?>22, #f8f9fa); }
        .dashboard-header { background: <?php echo $color; ?>; color: white; padding: 30px 0; }
        .stat-card { background: white; border-radius: 10px; padding: 20px; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .stat-card h3 { color: <?php echo $color; ?>; font-size: 2.5rem; margin: 0; }
        .btn-dept { background: <?php echo $color; ?>; border: none; }
    </style>
</head>
<body>
    <div class="dashboard-header text-center">
        <div class="container">
            <h1><i class="fas fa-tachometer-alt"></i> <?php echo htmlspecialchars($department['name']); ?> Dashboard</h1>
            <p>Department Management & Statistics</p>
        </div>
    </div>

    <div class="container my-5">
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-user-md fa-2x text-primary mb-2"></i>
                    <h3><?php echo $stats['doctors']; ?></h3>
                    <p>Active Doctors</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-calendar-day fa-2x text-success mb-2"></i>
                    <h3><?php echo $stats['appointments_today']; ?></h3>
                    <p>Today's Appointments</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                    <h3><?php echo $stats['appointments_pending']; ?></h3>
                    <p>Pending Appointments</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-calendar-alt fa-2x text-info mb-2"></i>
                    <h3><?php echo $stats['appointments_total']; ?></h3>
                    <p>Total Appointments</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header" style="background: <?php echo $color; ?>; color: white;">
                        <h5><i class="fas fa-cogs"></i> Department Management</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <a href="add-doctor.php?dept=<?php echo $dept_id; ?>" class="btn btn-dept text-white w-100">
                                    <i class="fas fa-user-plus"></i> Add Doctor
                                </a>
                            </div>
                            <div class="col-md-6 mb-3">
                                <a href="upcoming-appointments.php?dept=<?php echo $dept_id; ?>" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-calendar-check"></i> View Appointments
                                </a>
                            </div>
                            <div class="col-md-6 mb-3">
                                <a href="book-appointment.php?dept=<?php echo $dept_id; ?>" class="btn btn-success w-100">
                                    <i class="fas fa-calendar-plus"></i> Book Appointment
                                </a>
                            </div>
                            <div class="col-md-6 mb-3">
                                <a href="emergency-services.php" class="btn btn-danger w-100">
                                    <i class="fas fa-ambulance"></i> Emergency Cases
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header" style="background: <?php echo $color; ?>; color: white;">
                        <h6><i class="fas fa-info-circle"></i> Quick Info</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>Department:</strong> <?php echo htmlspecialchars($department['name']); ?></p>
                        <p><strong>Head:</strong> <?php echo htmlspecialchars($department['head_of_department'] ?? 'Not Assigned'); ?></p>
                        <p><strong>Status:</strong> <span class="badge bg-success">Active</span></p>
                        <hr>
                        <a href="department-details.php?id=<?php echo $dept_id; ?>" class="btn btn-outline-secondary btn-sm w-100">
                            <i class="fas fa-eye"></i> View Details
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="departments.php" class="btn btn-secondary me-2">
                <i class="fas fa-building"></i> All Departments
            </a>
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="fas fa-home"></i> Home
            </a>
        </div>
    </div>
</body>
</html>