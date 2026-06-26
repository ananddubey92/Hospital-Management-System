<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = getConnection();

// Create appointments table if not exists
$pdo->exec("CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    appt_id VARCHAR(20),
    patient_name VARCHAR(100),
    patient_phone VARCHAR(15),
    patient_email VARCHAR(100),
    doctor_id INT,
    department_id INT,
    appointment_date DATE,
    appointment_time TIME,
    reason TEXT,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
    consultation_fee DECIMAL(10,2) DEFAULT 500.00,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Add missing columns if they don't exist
try {
    $pdo->exec("ALTER TABLE appointments ADD COLUMN patient_name VARCHAR(100)");
} catch(PDOException $e) {}
try {
    $pdo->exec("ALTER TABLE appointments ADD COLUMN patient_phone VARCHAR(15)");
} catch(PDOException $e) {}
try {
    $pdo->exec("ALTER TABLE appointments ADD COLUMN patient_email VARCHAR(100)");
} catch(PDOException $e) {}

// Handle appointment removal
if (isset($_GET['remove']) && $_GET['remove']) {
    $remove_id = $_GET['remove'];
    $stmt = $pdo->prepare("DELETE FROM appointments WHERE id = ?");
    if ($stmt->execute([$remove_id])) {
        $success = "Appointment removed successfully!";
    } else {
        $error = "Failed to remove appointment.";
    }
}

// Handle status update
if (isset($_GET['update_status']) && $_GET['status']) {
    $appt_id = $_GET['update_status'];
    $new_status = $_GET['status'];
    $stmt = $pdo->prepare("UPDATE appointments SET status = ? WHERE id = ?");
    if ($stmt->execute([$new_status, $appt_id])) {
        $success = "Appointment status updated successfully!";
    }
}

// Add new appointment
if ($_POST && isset($_POST['add_appointment'])) {
    $patient_name = sanitize($_POST['patient_name']);
    $patient_phone = sanitize($_POST['patient_phone']);
    $patient_email = sanitize($_POST['patient_email']);
    $doctor_id = sanitize($_POST['doctor_id']);
    $appointment_date = sanitize($_POST['appointment_date']);
    $appointment_time = sanitize($_POST['appointment_time']);
    $reason = sanitize($_POST['reason']);
    
    $appt_id = 'APT' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
    
    $stmt = $pdo->prepare("INSERT INTO appointments (appt_id, patient_name, patient_phone, patient_email, doctor_id, department_id, appointment_date, appointment_time, reason, patient_id) VALUES (?, ?, ?, ?, ?, 1, ?, ?, ?, 0)");
    
    if ($stmt->execute([$appt_id, $patient_name, $patient_phone, $patient_email, $doctor_id, $appointment_date, $appointment_time, $reason])) {
        $success = "Appointment scheduled successfully! ID: $appt_id";
    } else {
        $error = "Failed to schedule appointment.";
    }
}

// Get appointments by status
$pending_appointments = $pdo->query("SELECT a.*, d.name as doctor_name, d.specialization FROM appointments a LEFT JOIN doctors d ON a.doctor_id = d.id WHERE a.status = 'pending' ORDER BY a.appointment_date, a.appointment_time")->fetchAll();

$successful_appointments = $pdo->query("SELECT a.*, d.name as doctor_name, d.specialization FROM appointments a LEFT JOIN doctors d ON a.doctor_id = d.id WHERE a.status IN ('confirmed', 'completed') ORDER BY a.appointment_date DESC")->fetchAll();

// Get doctors for dropdown
$doctors = $pdo->query("SELECT * FROM doctors WHERE status = 'active' ORDER BY name")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #007bff22, #ffffff); }
        .management-header { 
            background: linear-gradient(135deg, #007bff, #0056b3); 
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
            <h1><i class="fas fa-calendar-alt"></i> Appointment Management</h1>
            <p>Manage all patient appointments efficiently</p>
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

        <!-- Add New Appointment -->
        <div class="service-card">
            <div class="card-header bg-primary text-white">
                <h5><i class="fas fa-plus-circle"></i> Schedule New Appointment</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="add_appointment" value="1">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Patient Name</label>
                                <input type="text" class="form-control" name="patient_name" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Phone</label>
                                <input type="tel" class="form-control" name="patient_phone" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="patient_email">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Doctor</label>
                                <select class="form-control" name="doctor_id" required>
                                    <option value="">Select Doctor</option>
                                    <?php foreach ($doctors as $doctor): ?>
                                        <option value="<?php echo $doctor['id']; ?>">
                                            <?php echo htmlspecialchars($doctor['name']); ?> - <?php echo htmlspecialchars($doctor['specialization']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Date</label>
                                <input type="date" class="form-control" name="appointment_date" min="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Time</label>
                                <select class="form-control" name="appointment_time" required>
                                    <option value="">Select Time</option>
                                    <option value="09:00:00">09:00 AM</option>
                                    <option value="10:00:00">10:00 AM</option>
                                    <option value="11:00:00">11:00 AM</option>
                                    <option value="14:00:00">02:00 PM</option>
                                    <option value="15:00:00">03:00 PM</option>
                                    <option value="16:00:00">04:00 PM</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason for Visit</label>
                        <textarea class="form-control" name="reason" rows="2"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-calendar-plus"></i> Schedule Appointment
                    </button>
                </form>
            </div>
        </div>

        <!-- Pending Appointments -->
        <div class="service-card">
            <div class="card-header bg-warning text-dark">
                <h5><i class="fas fa-clock"></i> Pending Appointments (<?php echo count($pending_appointments); ?>)</h5>
            </div>
            <div class="card-body">
                <?php if (count($pending_appointments) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Patient</th>
                                    <th>Doctor</th>
                                    <th>Date & Time</th>
                                    <th>Contact</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pending_appointments as $appt): ?>
                                <tr>
                                    <td><strong><?php echo $appt['appt_id']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($appt['patient_name']); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($appt['doctor_name']); ?><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($appt['specialization']); ?></small>
                                    </td>
                                    <td>
                                        <?php echo date('d M Y', strtotime($appt['appointment_date'])); ?><br>
                                        <?php echo date('h:i A', strtotime($appt['appointment_time'])); ?>
                                    </td>
                                    <td>
                                        <i class="fas fa-phone"></i> <?php echo $appt['patient_phone']; ?><br>
                                        <?php if ($appt['patient_email']): ?>
                                            <i class="fas fa-envelope"></i> <?php echo $appt['patient_email']; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="?update_status=<?php echo $appt['id']; ?>&status=confirmed" class="btn btn-sm btn-success" title="Confirm">
                                            <i class="fas fa-check"></i>
                                        </a>
                                        <a href="?update_status=<?php echo $appt['id']; ?>&status=completed" class="btn btn-sm btn-info" title="Complete">
                                            <i class="fas fa-check-double"></i>
                                        </a>
                                        <a href="?remove=<?php echo $appt['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Remove this appointment?')" title="Remove">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center">No pending appointments.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Successful Appointments -->
        <div class="service-card">
            <div class="card-header bg-success text-white">
                <h5><i class="fas fa-check-circle"></i> Successful Appointments (<?php echo count($successful_appointments); ?>)</h5>
            </div>
            <div class="card-body">
                <?php if (count($successful_appointments) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Patient</th>
                                    <th>Doctor</th>
                                    <th>Date & Time</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($successful_appointments as $appt): ?>
                                <tr>
                                    <td><strong><?php echo $appt['appt_id']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($appt['patient_name']); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($appt['doctor_name']); ?><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($appt['specialization']); ?></small>
                                    </td>
                                    <td>
                                        <?php echo date('d M Y', strtotime($appt['appointment_date'])); ?><br>
                                        <?php echo date('h:i A', strtotime($appt['appointment_time'])); ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $appt['status'] === 'completed' ? 'success' : 'primary'; ?>">
                                            <?php echo ucfirst($appt['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="?remove=<?php echo $appt['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove this appointment?')" title="Remove">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center">No successful appointments yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="index.php" class="btn btn-secondary me-2">
                <i class="fas fa-home"></i> Back to Home
            </a>
            <a href="doctor-management.php" class="btn btn-primary">
                <i class="fas fa-user-md"></i> Manage Doctors
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>