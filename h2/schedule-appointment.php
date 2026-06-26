<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$error = '';
$success = '';

if ($_POST) {
    $patient_name = sanitize($_POST['patient_name']);
    $doctor_name = sanitize($_POST['doctor_name']);
    $appointment_date = sanitize($_POST['appointment_date']);
    $appointment_time = sanitize($_POST['appointment_time']);
    $reason = sanitize($_POST['reason']);
    
    if (empty($patient_name) || empty($doctor_name) || empty($appointment_date) || empty($appointment_time) || empty($reason)) {
        $error = 'Please fill in all required fields';
    } else {
        $pdo = getConnection();
        $appt_id = generateAppointmentId();
        
        $stmt = $pdo->prepare("INSERT INTO appointments (appt_id, patient_id, doctor_id, department_id, appointment_date, appointment_time, reason, consultation_fee) VALUES (?, 1, 1, 1, ?, ?, ?, 500.00)");
        
        if ($stmt->execute([$appt_id, $appointment_date, $appointment_time, $reason])) {
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
    <title>Schedule Appointment - Hospital Management</title>
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
                        <h2><i class="fas fa-calendar-plus"></i> Schedule Appointment</h2>
                        <p class="text-center text-white mb-4">
                            Select patient details, choose a doctor, pick an available date and time, and confirm the appointment.
                        </p>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" id="appointmentForm">
                            <div class="mb-3">
                                <label for="patient_name" class="form-label text-white">Patient Name</label>
                                <input type="text" class="form-control" id="patient_name" name="patient_name" required value="<?php echo isset($_POST['patient_name']) ? htmlspecialchars($_POST['patient_name']) : ''; ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="doctor_name" class="form-label text-white">Doctor Name</label>
                                <input type="text" class="form-control" id="doctor_name" name="doctor_name" required value="<?php echo isset($_POST['doctor_name']) ? htmlspecialchars($_POST['doctor_name']) : ''; ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="appointment_date" class="form-label text-white">Appointment Date</label>
                                <input type="date" class="form-control" id="appointment_date" name="appointment_date" required value="<?php echo isset($_POST['appointment_date']) ? htmlspecialchars($_POST['appointment_date']) : ''; ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="appointment_time" class="form-label text-white">Appointment Time</label>
                                <input type="time" class="form-control" id="appointment_time" name="appointment_time" required value="<?php echo isset($_POST['appointment_time']) ? htmlspecialchars($_POST['appointment_time']) : ''; ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="reason" class="form-label text-white">Reason for Visit</label>
                                <textarea class="form-control" id="reason" name="reason" rows="3" required><?php echo isset($_POST['reason']) ? htmlspecialchars($_POST['reason']) : ''; ?></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="fas fa-calendar-check"></i> Schedule Appointment
                            </button>
                        </form>
                        
                        <div class="text-center">
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