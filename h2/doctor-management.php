<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = getConnection();

// Handle doctor removal
if (isset($_GET['remove']) && $_GET['remove']) {
    $remove_id = $_GET['remove'];
    $stmt = $pdo->prepare("DELETE FROM doctors WHERE id = ?");
    if ($stmt->execute([$remove_id])) {
        $success = "Doctor removed successfully!";
    } else {
        $error = "Failed to remove doctor.";
    }
}

// Handle status update
if (isset($_GET['toggle_status']) && $_GET['toggle_status']) {
    $doctor_id = $_GET['toggle_status'];
    $stmt = $pdo->prepare("UPDATE doctors SET status = CASE WHEN status = 'active' THEN 'inactive' ELSE 'active' END WHERE id = ?");
    if ($stmt->execute([$doctor_id])) {
        $success = "Doctor status updated successfully!";
    }
}

// Add new doctor
if ($_POST && isset($_POST['add_doctor'])) {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $specialization = sanitize($_POST['specialization']);
    $qualification = sanitize($_POST['qualification']);
    $experience = sanitize($_POST['experience']);
    $consultation_fee = sanitize($_POST['consultation_fee']);
    $department_id = sanitize($_POST['department_id']);
    
    // Check if email already exists
    $check_email = $pdo->prepare("SELECT COUNT(*) FROM doctors WHERE email = ?");
    $check_email->execute([$email]);
    
    if ($check_email->fetchColumn() > 0) {
        $error = "Email already exists. Please use a different email.";
    } else {
        $doc_id = 'DOC' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
        $password = password_hash('doctor123', PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO doctors (doc_id, name, email, password, phone, department_id, specialization, qualification, experience, consultation_fee, rating) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 4.0)");
        
        if ($stmt->execute([$doc_id, $name, $email, $password, $phone, $department_id, $specialization, $qualification, $experience, $consultation_fee])) {
            $success = "Doctor added successfully! ID: $doc_id | Password: doctor123";
        } else {
            $error = "Failed to add doctor.";
        }
    }
}

// Get all doctors
$doctors = $pdo->query("SELECT d.*, dept.name as department_name FROM doctors d LEFT JOIN departments dept ON d.department_id = dept.id ORDER BY d.name")->fetchAll();

// Get departments for dropdown
$departments = $pdo->query("SELECT * FROM departments ORDER BY name")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #28a74522, #ffffff); }
        .management-header { 
            background: linear-gradient(135deg, #28a745, #1e7e34); 
            color: white; 
            padding: 30px 0; 
            text-align: center;
        }
        .service-card { 
            border: none; 
            border-radius: 15px; 
            box-shadow: 0 8px 25px rgba(0,0,0,0.1); 
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="management-header">
        <div class="container">
            <h1><i class="fas fa-user-md"></i> Doctor Management</h1>
            <p>Manage hospital doctors and medical staff</p>
        </div>
    </div>

    <div class="container my-4">
        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Add New Doctor -->
        <div class="service-card">
            <div class="card-header bg-success text-white">
                <h5><i class="fas fa-user-plus"></i> Add New Doctor</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="add_doctor" value="1">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Phone</label>
                                <input type="tel" class="form-control" name="phone" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Department</label>
                                <select class="form-control" name="department_id" required>
                                    <option value="">Select Department</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?php echo $dept['id']; ?>">
                                            <?php echo htmlspecialchars($dept['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Specialization</label>
                                <input type="text" class="form-control" name="specialization" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Qualification</label>
                                <input type="text" class="form-control" name="qualification" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Experience (Years)</label>
                                <input type="number" class="form-control" name="experience" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Consultation Fee (₹)</label>
                                <input type="number" class="form-control" name="consultation_fee" min="0" step="0.01" required>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-user-plus"></i> Add Doctor
                    </button>
                </form>
            </div>
        </div>

        <!-- Doctors List -->
        <div class="service-card">
            <div class="card-header bg-primary text-white">
                <h5><i class="fas fa-users"></i> All Doctors (<?php echo count($doctors); ?>)</h5>
            </div>
            <div class="card-body">
                <?php if (count($doctors) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Doctor</th>
                                    <th>Department</th>
                                    <th>Contact</th>
                                    <th>Experience</th>
                                    <th>Fee</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($doctors as $doctor): ?>
                                <tr>
                                    <td><strong><?php echo $doctor['doc_id']; ?></strong></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($doctor['name']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($doctor['specialization']); ?></small><br>
                                        <small class="text-info"><?php echo htmlspecialchars($doctor['qualification']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($doctor['department_name']); ?></td>
                                    <td>
                                        <i class="fas fa-phone"></i> <?php echo $doctor['phone']; ?><br>
                                        <i class="fas fa-envelope"></i> <?php echo $doctor['email']; ?>
                                    </td>
                                    <td><?php echo $doctor['experience']; ?> years</td>
                                    <td>₹<?php echo number_format($doctor['consultation_fee'], 2); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $doctor['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($doctor['status']); ?>
                                        </span><br>
                                        <small class="text-warning">
                                            <i class="fas fa-star"></i> <?php echo $doctor['rating']; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <a href="?toggle_status=<?php echo $doctor['id']; ?>" class="btn btn-sm btn-<?php echo $doctor['status'] === 'active' ? 'warning' : 'success'; ?>" title="Toggle Status">
                                            <i class="fas fa-<?php echo $doctor['status'] === 'active' ? 'pause' : 'play'; ?>"></i>
                                        </a>
                                        <a href="edit-doctor.php?id=<?php echo $doctor['id']; ?>" class="btn btn-sm btn-info" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?remove=<?php echo $doctor['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Remove Dr. <?php echo htmlspecialchars($doctor['name']); ?>?')" title="Remove">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center">No doctors found.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Statistics -->
        <div class="row">
            <div class="col-md-3">
                <div class="service-card text-center p-4">
                    <i class="fas fa-user-md fa-3x text-success mb-3"></i>
                    <h4><?php echo count(array_filter($doctors, function($d) { return $d['status'] === 'active'; })); ?></h4>
                    <p class="text-muted">Active Doctors</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="service-card text-center p-4">
                    <i class="fas fa-building fa-3x text-info mb-3"></i>
                    <h4><?php echo count($departments); ?></h4>
                    <p class="text-muted">Departments</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="service-card text-center p-4">
                    <i class="fas fa-star fa-3x text-warning mb-3"></i>
                    <h4><?php echo number_format(array_sum(array_column($doctors, 'rating')) / max(count($doctors), 1), 1); ?></h4>
                    <p class="text-muted">Avg Rating</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="service-card text-center p-4">
                    <i class="fas fa-rupee-sign fa-3x text-primary mb-3"></i>
                    <h4>₹<?php echo number_format(array_sum(array_column($doctors, 'consultation_fee')) / max(count($doctors), 1), 0); ?></h4>
                    <p class="text-muted">Avg Fee</p>
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="index.php" class="btn btn-secondary me-2">
                <i class="fas fa-home"></i> Back to Home
            </a>
            <a href="appointment-management.php" class="btn btn-primary">
                <i class="fas fa-calendar-alt"></i> Manage Appointments
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>