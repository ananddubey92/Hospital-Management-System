<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$error = '';
$success = '';

$pdo = getConnection();

// Get doctors and departments
$doctors = $pdo->query("SELECT d.*, dept.name as department_name FROM doctors d LEFT JOIN departments dept ON d.department_id = dept.id WHERE d.status = 'active' ORDER BY d.name")->fetchAll();
$departments = $pdo->query("SELECT * FROM departments ORDER BY name")->fetchAll();

if ($_POST) {
    $patient_name = sanitize($_POST['patient_name']);
    $patient_phone = sanitize($_POST['patient_phone']);
    $patient_email = sanitize($_POST['patient_email']);
    $doctor_id = sanitize($_POST['doctor_id']);
    $appointment_date = sanitize($_POST['appointment_date']);
    $appointment_time = sanitize($_POST['appointment_time']);
    $reason = sanitize($_POST['reason']);
    
    if (empty($patient_name) || empty($patient_phone) || empty($doctor_id) || empty($appointment_date) || empty($appointment_time)) {
        $error = 'Please fill in all required fields';
    } else {
        $appt_id = generateAppointmentId();
        
        $stmt = $pdo->prepare("INSERT INTO appointments (appt_id, patient_id, doctor_id, department_id, appointment_date, appointment_time, reason, status, consultation_fee, patient_name, patient_phone) VALUES (?, 1, ?, 1, ?, ?, ?, 'pending', 500.00, ?, ?)");
        
        if ($stmt->execute([$appt_id, $doctor_id, $appointment_date, $appointment_time, $reason, $patient_name, $patient_phone])) {
            $success = "Appointment booked successfully! Appointment ID: $appt_id";
        } else {
            $error = 'Failed to book appointment. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment</title>
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
                        <h2><i class="fas fa-calendar-plus"></i> Book Appointment</h2>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label text-white">Patient Name</label>
                                        <input type="text" class="form-control" name="patient_name" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label text-white">Phone Number</label>
                                        <input type="tel" class="form-control" name="patient_phone" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label text-white">Email (Optional)</label>
                                <input type="email" class="form-control" name="patient_email">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label text-white">Select Doctor</label>
                                <select class="form-control" name="doctor_id" required>
                                    <option value="">Choose Doctor</option>
                                    <?php foreach ($doctors as $doctor): ?>
                                        <option value="<?php echo $doctor['id']; ?>">
                                            <?php echo htmlspecialchars($doctor['name']); ?> - <?php echo htmlspecialchars($doctor['specialization']); ?> (<?php echo htmlspecialchars($doctor['department_name']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label text-white">Appointment Date</label>
                                        <input type="date" class="form-control" name="appointment_date" min="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label text-white">Appointment Time</label>
                                        <select class="form-control" name="appointment_time" required>
                                            <option value="">Select Time</option>
                                            <option value="09:00:00">09:00 AM</option>
                                            <option value="10:00:00">10:00 AM</option>
                                            <option value="11:00:00">11:00 AM</option>
                                            <option value="12:00:00">12:00 PM</option>
                                            <option value="14:00:00">02:00 PM</option>
                                            <option value="15:00:00">03:00 PM</option>
                                            <option value="16:00:00">04:00 PM</option>
                                            <option value="17:00:00">05:00 PM</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label text-white">Reason for Visit</label>
                                <textarea class="form-control" name="reason" rows="3" placeholder="Describe your symptoms or reason for appointment"></textarea>
                            </div>
                            
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-calendar-check"></i> Book Appointment
                                </button>
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>