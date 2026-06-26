<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = getConnection();

// Create inpatients table if not exists
$pdo->exec("CREATE TABLE IF NOT EXISTS inpatients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admission_id VARCHAR(20),
    patient_name VARCHAR(100),
    patient_phone VARCHAR(15),
    room_type VARCHAR(50),
    room_number VARCHAR(10),
    admission_date DATE,
    discharge_date DATE,
    attending_doctor VARCHAR(100),
    condition_status VARCHAR(50),
    daily_cost DECIMAL(10,2),
    total_cost DECIMAL(10,2),
    status ENUM('Admitted', 'Discharged', 'Transferred') DEFAULT 'Admitted',
    medical_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

if ($_POST) {
    $patient_name = sanitize($_POST['patient_name']);
    $patient_phone = sanitize($_POST['patient_phone']);
    $room_type = sanitize($_POST['room_type']);
    $admission_date = sanitize($_POST['admission_date']);
    $attending_doctor = sanitize($_POST['attending_doctor']);
    $condition_status = sanitize($_POST['condition_status']);
    $medical_notes = sanitize($_POST['medical_notes']);
    
    $room_costs = [
        'General Ward' => 2500,
        'Semi-Private Room' => 4000,
        'Private Room' => 6000,
        'Deluxe Room' => 8000,
        'ICU' => 12000,
        'NICU' => 15000,
        'CCU' => 14000,
        'Isolation Room' => 7000
    ];
    
    $daily_cost = $room_costs[$room_type] ?? 2500;
    
    // Generate room number based on type
    $room_prefixes = [
        'General Ward' => 'GW',
        'Semi-Private Room' => 'SP',
        'Private Room' => 'PR',
        'Deluxe Room' => 'DX',
        'ICU' => 'IC',
        'NICU' => 'NI',
        'CCU' => 'CC',
        'Isolation Room' => 'IS'
    ];
    
    $room_number = $room_prefixes[$room_type] . rand(101, 999);
    $admission_id = 'ADM' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
    
    $stmt = $pdo->prepare("INSERT INTO inpatients (admission_id, patient_name, patient_phone, room_type, room_number, admission_date, attending_doctor, condition_status, daily_cost, medical_notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if ($stmt->execute([$admission_id, $patient_name, $patient_phone, $room_type, $room_number, $admission_date, $attending_doctor, $condition_status, $daily_cost, $medical_notes])) {
        $success = "Patient admitted successfully! Admission ID: $admission_id | Room: $room_number";
    } else {
        $error = "Failed to admit patient.";
    }
}

$current_patients = $pdo->query("SELECT * FROM inpatients WHERE status = 'Admitted' ORDER BY admission_date DESC LIMIT 5")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inpatient Care - Comprehensive Patient Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #6f42c122, #ffffff); }
        .inpatient-header { 
            background: linear-gradient(135deg, #6f42c1, #5a32a3); 
            color: white; 
            padding: 40px 0; 
            text-align: center;
            box-shadow: 0 4px 15px rgba(111, 66, 193, 0.3);
        }
        .service-card { 
            border: none; 
            border-radius: 15px; 
            box-shadow: 0 8px 25px rgba(0,0,0,0.1); 
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }
        .service-card:hover { 
            transform: translateY(-5px); 
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }
        .btn-inpatient { 
            background: linear-gradient(135deg, #6f42c1, #5a32a3);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-inpatient:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(111, 66, 193, 0.4);
        }
    </style>
</head>
<body>
    <div class="inpatient-header">
        <div class="container">
            <h1 class="display-4 mb-3">
                <i class="fas fa-bed"></i> Inpatient Care
            </h1>
            <p class="lead">Comprehensive Patient Admission & Room Management</p>
            <p class="mb-0">24/7 nursing care • Modern facilities • Patient comfort</p>
        </div>
    </div>

    <div class="container my-5">
        <div class="row mb-5">
            <div class="col-md-3">
                <div class="service-card text-center p-4">
                    <i class="fas fa-bed fa-3x text-primary mb-3"></i>
                    <h4>50+</h4>
                    <p class="text-muted">Patient Beds</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="service-card text-center p-4">
                    <i class="fas fa-user-nurse fa-3x text-success mb-3"></i>
                    <h4>24/7</h4>
                    <p class="text-muted">Nursing Care</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="service-card text-center p-4">
                    <i class="fas fa-heartbeat fa-3x text-danger mb-3"></i>
                    <h4>ICU</h4>
                    <p class="text-muted">Critical Care</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="service-card text-center p-4">
                    <i class="fas fa-shield-alt fa-3x text-warning mb-3"></i>
                    <h4>Safe</h4>
                    <p class="text-muted">Environment</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="service-card">
                    <div class="card-header text-white" style="background: #6f42c1;">
                        <h5 class="mb-0"><i class="fas fa-user-plus"></i> Admit Patient</h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Patient Name</label>
                                <input type="text" class="form-control" name="patient_name" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Contact Number</label>
                                <input type="tel" class="form-control" name="patient_phone" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Room Type</label>
                                <select class="form-control" name="room_type" required>
                                    <option value="">Select Room Type</option>
                                    <option value="General Ward">General Ward (₹2,500/day)</option>
                                    <option value="Semi-Private Room">Semi-Private Room (₹4,000/day)</option>
                                    <option value="Private Room">Private Room (₹6,000/day)</option>
                                    <option value="Deluxe Room">Deluxe Room (₹8,000/day)</option>
                                    <option value="ICU">ICU (₹12,000/day)</option>
                                    <option value="NICU">NICU (₹15,000/day)</option>
                                    <option value="CCU">CCU (₹14,000/day)</option>
                                    <option value="Isolation Room">Isolation Room (₹7,000/day)</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Admission Date</label>
                                <input type="date" class="form-control" name="admission_date" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Attending Doctor</label>
                                <select class="form-control" name="attending_doctor" required>
                                    <option value="">Select Doctor</option>
                                    <option value="Dr. Rajesh Kumar">Dr. Rajesh Kumar - Internal Medicine</option>
                                    <option value="Dr. Priya Sharma">Dr. Priya Sharma - Cardiology</option>
                                    <option value="Dr. Amit Patel">Dr. Amit Patel - Neurology</option>
                                    <option value="Dr. Sunita Gupta">Dr. Sunita Gupta - Orthopedics</option>
                                    <option value="Dr. Vikram Singh">Dr. Vikram Singh - General Surgery</option>
                                    <option value="Dr. Meera Joshi">Dr. Meera Joshi - Pediatrics</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Patient Condition</label>
                                <select class="form-control" name="condition_status" required>
                                    <option value="">Select Condition</option>
                                    <option value="Stable">Stable</option>
                                    <option value="Critical">Critical</option>
                                    <option value="Serious">Serious</option>
                                    <option value="Fair">Fair</option>
                                    <option value="Good">Good</option>
                                    <option value="Recovering">Recovering</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Medical Notes</label>
                                <textarea class="form-control" name="medical_notes" rows="3" placeholder="Diagnosis, treatment plan, special instructions"></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-inpatient text-white w-100">
                                <i class="fas fa-bed"></i> Admit Patient
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="service-card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-users"></i> Current Inpatients</h5>
                    </div>
                    <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                        <?php if (count($current_patients) > 0): ?>
                            <?php foreach ($current_patients as $patient): ?>
                            <div class="card mb-2">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between">
                                        <strong><?php echo htmlspecialchars($patient['patient_name']); ?></strong>
                                        <span class="badge bg-<?php 
                                            echo $patient['condition_status'] === 'Critical' ? 'danger' : 
                                                ($patient['condition_status'] === 'Serious' ? 'warning' : 'success'); 
                                        ?>">
                                            <?php echo $patient['condition_status']; ?>
                                        </span>
                                    </div>
                                    <p class="mb-1">
                                        <i class="fas fa-bed"></i> <?php echo htmlspecialchars($patient['room_type']); ?> - <?php echo $patient['room_number']; ?>
                                    </p>
                                    <small class="text-muted">
                                        <i class="fas fa-user-md"></i> <?php echo htmlspecialchars($patient['attending_doctor']); ?><br>
                                        <i class="fas fa-calendar"></i> Admitted: <?php echo date('d M Y', strtotime($patient['admission_date'])); ?><br>
                                        <i class="fas fa-rupee-sign"></i> ₹<?php echo number_format($patient['daily_cost'], 2); ?>/day
                                    </small>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted text-center">No current inpatients.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Room Availability -->
        <div class="row mt-5">
            <div class="col-md-12">
                <h3 class="text-center mb-4">Room Availability Status</h3>
            </div>
            
            <div class="col-md-3">
                <div class="service-card text-center p-3">
                    <h4 class="text-success">15</h4>
                    <p class="mb-0">General Ward Available</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="service-card text-center p-3">
                    <h4 class="text-warning">8</h4>
                    <p class="mb-0">Private Rooms Available</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="service-card text-center p-3">
                    <h4 class="text-info">3</h4>
                    <p class="mb-0">ICU Beds Available</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="service-card text-center p-3">
                    <h4 class="text-danger">2</h4>
                    <p class="mb-0">Deluxe Rooms Available</p>
                </div>
            </div>
        </div>

        <!-- Services -->
        <div class="row mt-5">
            <div class="col-md-6">
                <div class="service-card p-4">
                    <h5><i class="fas fa-user-nurse text-primary"></i> Nursing Services</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success"></i> 24/7 Nursing Care</li>
                        <li><i class="fas fa-check text-success"></i> Medication Management</li>
                        <li><i class="fas fa-check text-success"></i> Vital Signs Monitoring</li>
                        <li><i class="fas fa-check text-success"></i> Patient Assistance</li>
                    </ul>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="service-card p-4">
                    <h5><i class="fas fa-utensils text-warning"></i> Patient Amenities</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success"></i> Nutritious Meals</li>
                        <li><i class="fas fa-check text-success"></i> Wi-Fi Access</li>
                        <li><i class="fas fa-check text-success"></i> Television & Entertainment</li>
                        <li><i class="fas fa-check text-success"></i> Visitor Facilities</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="text-center mt-5">
            <a href="index.php" class="btn btn-secondary me-3">
                <i class="fas fa-home"></i> Back to Home
            </a>
            <a href="patient-records.php" class="btn btn-outline-primary me-3">
                <i class="fas fa-file-medical"></i> Patient Records
            </a>
            <a href="book-appointment.php" class="btn btn-primary">
                <i class="fas fa-calendar-plus"></i> Book Appointment
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>