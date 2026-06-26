<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = getConnection();

// Create diagnostic_tests table if not exists
$pdo->exec("CREATE TABLE IF NOT EXISTS diagnostic_tests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    test_id VARCHAR(20),
    patient_name VARCHAR(100),
    patient_phone VARCHAR(15),
    test_type VARCHAR(100),
    test_date DATE,
    test_time TIME,
    doctor_referral VARCHAR(100),
    cost DECIMAL(10,2),
    status ENUM('Scheduled', 'In Progress', 'Completed', 'Cancelled') DEFAULT 'Scheduled',
    results TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

if ($_POST) {
    $patient_name = sanitize($_POST['patient_name']);
    $patient_phone = sanitize($_POST['patient_phone']);
    $test_type = sanitize($_POST['test_type']);
    $test_date = sanitize($_POST['test_date']);
    $test_time = sanitize($_POST['test_time']);
    $doctor_referral = sanitize($_POST['doctor_referral']);
    
    $test_costs = [
        'X-Ray Chest' => 500,
        'X-Ray Abdomen' => 600,
        'MRI Brain' => 4000,
        'MRI Spine' => 4500,
        'CT Scan Head' => 2500,
        'CT Scan Chest' => 3000,
        'Ultrasound Abdomen' => 800,
        'Ultrasound Pelvis' => 900,
        'Blood Test Complete' => 350,
        'Blood Sugar Test' => 150,
        'Liver Function Test' => 450,
        'Kidney Function Test' => 400,
        'ECG' => 200,
        'Echo Cardiogram' => 1200,
        'Stress Test' => 1500,
        'Bone Density Scan' => 1800
    ];
    
    $cost = $test_costs[$test_type] ?? 500;
    $test_id = 'TST' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
    
    $stmt = $pdo->prepare("INSERT INTO diagnostic_tests (test_id, patient_name, patient_phone, test_type, test_date, test_time, doctor_referral, cost) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    if ($stmt->execute([$test_id, $patient_name, $patient_phone, $test_type, $test_date, $test_time, $doctor_referral, $cost])) {
        $success = "Diagnostic test scheduled successfully! Test ID: $test_id";
    } else {
        $error = "Failed to schedule test. Please try again.";
    }
}

// Get recent tests
$recent_tests = $pdo->query("SELECT * FROM diagnostic_tests ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostic Services - Advanced Imaging & Lab Tests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #0dcaf022, #ffffff); }
        .diagnostic-header { 
            background: linear-gradient(135deg, #0dcaf0, #0bb5d6); 
            color: white; 
            padding: 40px 0; 
            text-align: center;
            box-shadow: 0 4px 15px rgba(13, 202, 240, 0.3);
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
        .test-category {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #0dcaf0;
        }
        .btn-diagnostic { 
            background: linear-gradient(135deg, #0dcaf0, #0bb5d6);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-diagnostic:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(13, 202, 240, 0.4);
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row mb-5">
            <div class="col-md-3">
                <div class="service-card text-center p-4">
                    <i class="fas fa-x-ray fa-3x text-info mb-3"></i>
                    <h4>16+</h4>
                    <p class="text-muted">Imaging Services</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="service-card text-center p-4">
                    <i class="fas fa-microscope fa-3x text-success mb-3"></i>
                    <h4>25+</h4>
                    <p class="text-muted">Lab Tests</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="service-card text-center p-4">
                    <i class="fas fa-heartbeat fa-3x text-danger mb-3"></i>
                    <h4>10+</h4>
                    <p class="text-muted">Cardiac Tests</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="service-card text-center p-4">
                    <i class="fas fa-clock fa-3x text-warning mb-3"></i>
                    <h4>24/7</h4>
                    <p class="text-muted">Emergency Tests</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="service-card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-calendar-plus"></i> Schedule Diagnostic Test</h5>
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
                                <label class="form-label">Test Type</label>
                                <select class="form-control" name="test_type" required>
                                    <option value="">Select Test</option>
                                    <optgroup label="Imaging Services">
                                        <option value="X-Ray Chest">X-Ray Chest (₹500)</option>
                                        <option value="X-Ray Abdomen">X-Ray Abdomen (₹600)</option>
                                        <option value="MRI Brain">MRI Brain (₹4000)</option>
                                        <option value="MRI Spine">MRI Spine (₹4500)</option>
                                        <option value="CT Scan Head">CT Scan Head (₹2500)</option>
                                        <option value="CT Scan Chest">CT Scan Chest (₹3000)</option>
                                        <option value="Ultrasound Abdomen">Ultrasound Abdomen (₹800)</option>
                                        <option value="Ultrasound Pelvis">Ultrasound Pelvis (₹900)</option>
                                    </optgroup>
                                    <optgroup label="Laboratory Tests">
                                        <option value="Blood Test Complete">Complete Blood Count (₹350)</option>
                                        <option value="Blood Sugar Test">Blood Sugar Test (₹150)</option>
                                        <option value="Liver Function Test">Liver Function Test (₹450)</option>
                                        <option value="Kidney Function Test">Kidney Function Test (₹400)</option>
                                    </optgroup>
                                    <optgroup label="Cardiac Tests">
                                        <option value="ECG">ECG (₹200)</option>
                                        <option value="Echo Cardiogram">Echo Cardiogram (₹1200)</option>
                                        <option value="Stress Test">Stress Test (₹1500)</option>
                                    </optgroup>
                                    <optgroup label="Specialized Tests">
                                        <option value="Bone Density Scan">Bone Density Scan (₹1800)</option>
                                    </optgroup>
                                </select>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Test Date</label>
                                        <input type="date" class="form-control" name="test_date" min="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Preferred Time</label>
                                        <select class="form-control" name="test_time" required>
                                            <option value="">Select Time</option>
                                            <option value="08:00:00">08:00 AM</option>
                                            <option value="09:00:00">09:00 AM</option>
                                            <option value="10:00:00">10:00 AM</option>
                                            <option value="11:00:00">11:00 AM</option>
                                            <option value="14:00:00">02:00 PM</option>
                                            <option value="15:00:00">03:00 PM</option>
                                            <option value="16:00:00">04:00 PM</option>
                                            <option value="17:00:00">05:00 PM</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Doctor Referral (Optional)</label>
                                <input type="text" class="form-control" name="doctor_referral" placeholder="Referring doctor name">
                            </div>
                            
                            <button type="submit" class="btn btn-diagnostic text-white w-100">
                                <i class="fas fa-calendar-check"></i> Schedule Test
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="service-card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-list-alt"></i> Recent Tests</h5>
                    </div>
                    <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                        <?php if (count($recent_tests) > 0): ?>
                            <?php foreach ($recent_tests as $test): ?>
                            <div class="card mb-2">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between">
                                        <strong><?php echo htmlspecialchars($test['patient_name']); ?></strong>
                                        <span class="badge bg-primary"><?php echo $test['status']; ?></span>
                                    </div>
                                    <p class="mb-1"><?php echo htmlspecialchars($test['test_type']); ?></p>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar"></i> <?php echo date('d M Y', strtotime($test['test_date'])); ?> |
                                        <i class="fas fa-rupee-sign"></i> <?php echo number_format($test['cost'], 2); ?>
                                    </small>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted text-center">No recent tests found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-md-12">
                <h3 class="text-center mb-4">Our Diagnostic Services</h3>
            </div>
            
            <div class="col-md-6">
                <div class="test-category">
                    <h5><i class="fas fa-x-ray text-info"></i> Advanced Imaging</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success"></i> Digital X-Ray</li>
                        <li><i class="fas fa-check text-success"></i> MRI Scanning</li>
                        <li><i class="fas fa-check text-success"></i> CT Scan</li>
                        <li><i class="fas fa-check text-success"></i> Ultrasound</li>
                        <li><i class="fas fa-check text-success"></i> Mammography</li>
                    </ul>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="test-category">
                    <h5><i class="fas fa-microscope text-success"></i> Laboratory Tests</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success"></i> Blood Analysis</li>
                        <li><i class="fas fa-check text-success"></i> Urine Tests</li>
                        <li><i class="fas fa-check text-success"></i> Biochemistry</li>
                        <li><i class="fas fa-check text-success"></i> Microbiology</li>
                        <li><i class="fas fa-check text-success"></i> Pathology</li>
                    </ul>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="test-category">
                    <h5><i class="fas fa-heartbeat text-danger"></i> Cardiac Diagnostics</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success"></i> ECG/EKG</li>
                        <li><i class="fas fa-check text-success"></i> Echocardiography</li>
                        <li><i class="fas fa-check text-success"></i> Stress Testing</li>
                        <li><i class="fas fa-check text-success"></i> Holter Monitoring</li>
                        <li><i class="fas fa-check text-success"></i> Cardiac Catheterization</li>
                    </ul>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="test-category">
                    <h5><i class="fas fa-bone text-warning"></i> Specialized Tests</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success"></i> Bone Density Scan</li>
                        <li><i class="fas fa-check text-success"></i> Pulmonary Function</li>
                        <li><i class="fas fa-check text-success"></i> Endoscopy</li>
                        <li><i class="fas fa-check text-success"></i> Biopsy Services</li>
                        <li><i class="fas fa-check text-success"></i> Genetic Testing</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="text-center mt-5">
            <a href="index.php" class="btn btn-secondary me-3">
                <i class="fas fa-home"></i> Back to Home
            </a>
            <a href="diagnostic-results.php" class="btn btn-outline-info me-3">
                <i class="fas fa-file-medical"></i> View Test Results
            </a>
            <a href="book-appointment.php" class="btn btn-primary">
                <i class="fas fa-calendar-plus"></i> Book Appointment
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>