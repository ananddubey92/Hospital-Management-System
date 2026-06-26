<?php
require_once __DIR__ . '/config/database.php';

$dept_id = $_GET['id'] ?? 0;

try {
    $pdo = getConnection();
    
    // Get department details
    $stmt = $pdo->prepare("SELECT * FROM departments WHERE id = ?");
    $stmt->execute([$dept_id]);
    $department = $stmt->fetch();
    
    // Get doctors in this department
    $stmt = $pdo->prepare("SELECT * FROM doctors WHERE department_id = ? AND status = 'active'");
    $stmt->execute([$dept_id]);
    $doctors = $stmt->fetchAll();
    
} catch(PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

$dept_colors = [
    'Cardiology' => ['bg-danger', 'text-white', '#dc3545'],
    'Neurology' => ['bg-primary', 'text-white', '#0d6efd'],
    'Orthopedics' => ['bg-success', 'text-white', '#198754']
];

$current_color = $dept_colors[$department['name']] ?? ['bg-info', 'text-white', '#0dcaf0'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($department['name']); ?> Department</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, <?php echo $current_color[2]; ?>22, #ffffff); }
        .dept-header { background: <?php echo $current_color[2]; ?>; color: white; padding: 40px 0; }
        .card { border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .btn-dept { background: <?php echo $current_color[2]; ?>; border: none; }
        .btn-dept:hover { background: <?php echo $current_color[2]; ?>dd; }
    </style>
</head>
<body>
    <div class="dept-header text-center">
        <div class="container">
            <h1><i class="fas fa-hospital"></i> <?php echo htmlspecialchars($department['name']); ?></h1>
            <p class="lead"><?php echo htmlspecialchars($department['description']); ?></p>
            <p><strong>Head of Department:</strong> <?php echo htmlspecialchars($department['head_of_department'] ?? 'Not Assigned'); ?></p>
        </div>
    </div>

    <div class="container my-5">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header <?php echo $current_color[0] . ' ' . $current_color[1]; ?>">
                        <h4><i class="fas fa-info-circle"></i> Department Information</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($department['name'] == 'Cardiology'): ?>
                            <h5>Services Offered:</h5>
                            <ul>
                                <li>Cardiac Catheterization</li>
                                <li>Echocardiography</li>
                                <li>Stress Testing</li>
                                <li>Heart Surgery</li>
                                <li>Pacemaker Implantation</li>
                            </ul>
                        <?php elseif ($department['name'] == 'Neurology'): ?>
                            <h5>Services Offered:</h5>
                            <ul>
                                <li>Brain MRI & CT Scans</li>
                                <li>Stroke Treatment</li>
                                <li>Epilepsy Management</li>
                                <li>Neurological Surgery</li>
                                <li>Memory Disorder Clinic</li>
                            </ul>
                        <?php elseif ($department['name'] == 'Orthopedics'): ?>
                            <h5>Services Offered:</h5>
                            <ul>
                                <li>Joint Replacement Surgery</li>
                                <li>Sports Medicine</li>
                                <li>Fracture Treatment</li>
                                <li>Spine Surgery</li>
                                <li>Arthroscopy</li>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header <?php echo $current_color[0] . ' ' . $current_color[1]; ?>">
                        <h4><i class="fas fa-user-md"></i> Our Doctors</h4>
                    </div>
                    <div class="card-body">
                        <?php if (count($doctors) > 0): ?>
                            <div class="row">
                                <?php foreach ($doctors as $doctor): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <h6><?php echo htmlspecialchars($doctor['name']); ?></h6>
                                            <p class="text-muted"><?php echo htmlspecialchars($doctor['specialization']); ?></p>
                                            <p><small><?php echo $doctor['experience']; ?> years experience</small></p>
                                            <p class="text-success">₹<?php echo number_format($doctor['consultation_fee'], 2); ?></p>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p>No doctors available in this department.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header <?php echo $current_color[0] . ' ' . $current_color[1]; ?>">
                        <h5><i class="fas fa-calendar-plus"></i> Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="book-appointment.php?dept=<?php echo $dept_id; ?>" class="btn btn-dept text-white">
                                <i class="fas fa-calendar-check"></i> Book Appointment
                            </a>
                            <a href="emergency-services.php" class="btn btn-danger">
                                <i class="fas fa-ambulance"></i> Emergency
                            </a>
                            <a href="doctors.php" class="btn btn-outline-secondary">
                                <i class="fas fa-user-md"></i> All Doctors
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header <?php echo $current_color[0] . ' ' . $current_color[1]; ?>">
                        <h6><i class="fas fa-clock"></i> Working Hours</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>Monday - Friday:</strong> 8:00 AM - 6:00 PM</p>
                        <p><strong>Saturday:</strong> 9:00 AM - 4:00 PM</p>
                        <p><strong>Sunday:</strong> Emergency Only</p>
                        <p class="text-danger"><strong>Emergency:</strong> 24/7</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-home"></i> Back to Home
            </a>
        </div>
    </div>
</body>
</html>