<?php
require_once __DIR__ . '/config/database.php';

try {
    $pdo = getConnection();
    
    // Get upcoming appointments
    $query = "SELECT a.*, d.name as doctor_name, d.specialization, dept.name as department_name 
              FROM appointments a 
              LEFT JOIN doctors d ON a.doctor_id = d.id 
              LEFT JOIN departments dept ON a.department_id = dept.id 
              WHERE a.appointment_date >= CURDATE() 
              ORDER BY a.appointment_date, a.appointment_time";
    $appointments = $pdo->query($query)->fetchAll();
    
} catch(PDOException $e) {
    $appointments = [];
    $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upcoming Appointments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <div class="auth-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div class="auth-card glass">
                        <h2><i class="fas fa-calendar-alt"></i> Upcoming Appointments</h2>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>

                        <?php if (count($appointments) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Appointment ID</th>
                                        <th>Doctor</th>
                                        <th>Department</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Status</th>
                                        <th>Fee</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($appointments as $appointment): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($appointment['appt_id']); ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($appointment['doctor_name']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($appointment['specialization']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($appointment['department_name']); ?></td>
                                        <td><?php echo date('d M Y', strtotime($appointment['appointment_date'])); ?></td>
                                        <td><?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $appointment['status'] === 'confirmed' ? 'success' : 
                                                    ($appointment['status'] === 'pending' ? 'warning' : 'secondary'); 
                                            ?>">
                                                <?php echo ucfirst($appointment['status']); ?>
                                            </span>
                                        </td>
                                        <td>₹<?php echo number_format($appointment['consultation_fee'], 2); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="text-center text-white">
                            <p>No upcoming appointments found.</p>
                        </div>
                        <?php endif; ?>
                        
                        <div class="text-center mt-4">
                            <a href="book-appointment.php" class="btn btn-primary me-3">
                                <i class="fas fa-calendar-plus"></i> Book New Appointment
                            </a>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-home"></i> Back to Home
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>