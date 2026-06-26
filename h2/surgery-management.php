<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = getConnection();

// Create surgeries table if not exists
$pdo->exec("CREATE TABLE IF NOT EXISTS surgeries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    surgery_id VARCHAR(20),
    patient_name VARCHAR(100),
    patient_phone VARCHAR(15),
    surgery_type VARCHAR(100),
    surgeon_name VARCHAR(100),
    surgery_date DATE,
    surgery_time TIME,
    operation_theater VARCHAR(10),
    estimated_duration VARCHAR(50),
    cost DECIMAL(10,2),
    status ENUM('Scheduled', 'In Progress', 'Completed', 'Cancelled') DEFAULT 'Scheduled',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

if ($_POST) {
    $patient_name = sanitize($_POST['patient_name']);
    $patient_phone = sanitize($_POST['patient_phone']);
    $surgery_type = sanitize($_POST['surgery_type']);
    $surgeon_name = sanitize($_POST['surgeon_name']);
    $surgery_date = sanitize($_POST['surgery_date']);
    $surgery_time = sanitize($_POST['surgery_time']);
    $operation_theater = sanitize($_POST['operation_theater']);
    $estimated_duration = sanitize($_POST['estimated_duration']);
    $notes = sanitize($_POST['notes']);
    
    $surgery_costs = [
        'General Surgery' => 25000,
        'Cardiac Surgery' => 150000,
        'Neurosurgery' => 200000,
        'Orthopedic Surgery' => 75000,
        'Laparoscopic Surgery' => 50000,
        'Emergency Surgery' => 40000,
        'Plastic Surgery' => 60000,
        'Vascular Surgery' => 80000,
        'Pediatric Surgery' => 45000,
        'Gynecological Surgery' => 35000
    ];
    
    $cost = $surgery_costs[$surgery_type] ?? 25000;
    $surgery_id = 'SRG' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
    
    $stmt = $pdo->prepare("INSERT INTO surgeries (surgery_id, patient_name, patient_phone, surgery_type, surgeon_name, surgery_date, surgery_time, operation_theater, estimated_duration, cost, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if ($stmt->execute([$surgery_id, $patient_name, $patient_phone, $surgery_type, $surgeon_name, $surgery_date, $surgery_time, $operation_theater, $estimated_duration, $cost, $notes])) {
        $success = "Surgery scheduled successfully! Surgery ID: $surgery_id";
    } else {
        $error = "Failed to schedule surgery.";
    }
}

$recent_surgeries = $pdo->query("SELECT * FROM surgeries ORDER BY surgery_date ASC, surgery_time ASC LIMIT 5")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surgery Management - Advanced Surgical Procedures</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #19875422, #ffffff); }
        .surgery-header { 
            background: linear-gradient(135deg, #198754, #157347); 
            color: white; 
            padding: 40px 0; 
            text-align: center;
            box-shadow: 0 4px 15px rgba(25, 135, 84, 0.3);
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
        .btn-surgery { 
            background: linear-gradient(135deg, #198754, #157347);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-surgery:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(25, 135, 84, 0.4);
        }
    </style>
</head>
<body>
    <div class="surgery-header">
        <div class="container">
            <h1 class="display-4 mb-3">
                <i class="fas fa-procedures"></i> Surgery Management
            </h1>
            <p class="lead">Advanced Surgical Procedures & Operation Theater Management</p>
            <p class="mb-0">Expert surgeons • Modern facilities • Comprehensive care</p>
        </div>
    </div>

    <div class="container my-5">
        <div class="row mb-5">
            <div class="col-md-3">
                <div class="service-card text-center p-4">
                    <i class="fas fa-procedures fa-3x text-success mb-3"></i>
                    <h4>10+</h4>
                    <p class="text-muted">Surgery Types</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="service-card text-center p-4">
                    <i class="fas fa-hospital fa-3x text-info mb-3"></i>
                    <h4>4</h4>
                    <p class="text-muted">Operation Theaters</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="service-card text-center p-4">
                    <i class="fas fa-user-md fa-3x text-warning mb-3"></i>
                    <h4>Expert</h4>
                    <p class="text-muted">Surgeons</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="service-card text-center p-4">
                    <i class="fas fa-shield-alt fa-3x text-danger mb-3"></i>
                    <h4>Safe</h4>
                    <p class="text-muted">Procedures</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="service-card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-calendar-plus"></i> Schedule Surgery</h5>
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
                                <label class="form-label">Surgery Type</label>
                                <select class="form-control" name="surgery_type" required>
                                    <option value="">Select Surgery Type</option>
                                    <option value="General Surgery">General Surgery (₹25,000)</option>
                                    <option value="Cardiac Surgery">Cardiac Surgery (₹1,50,000)</option>
                                    <option value="Neurosurgery">Neurosurgery (₹2,00,000)</option>
                                    <option value="Orthopedic Surgery">Orthopedic Surgery (₹75,000)</option>
                                    <option value="Laparoscopic Surgery">Laparoscopic Surgery (₹50,000)</option>
                                    <option value="Emergency Surgery">Emergency Surgery (₹40,000)</option>
                                    <option value="Plastic Surgery">Plastic Surgery (₹60,000)</option>
                                    <option value="Vascular Surgery">Vascular Surgery (₹80,000)</option>
                                    <option value="Pediatric Surgery">Pediatric Surgery (₹45,000)</option>
                                    <option value="Gynecological Surgery">Gynecological Surgery (₹35,000)</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Surgeon Name</label>
                                <select class="form-control" name="surgeon_name" required>
                                    <option value="">Select Surgeon</option>
                                    <option value="Dr. Rajesh Kumar">Dr. Rajesh Kumar - General Surgery</option>
                                    <option value="Dr. Priya Sharma">Dr. Priya Sharma - Cardiac Surgery</option>
                                    <option value="Dr. Amit Patel">Dr. Amit Patel - Neurosurgery</option>
                                    <option value="Dr. Sunita Gupta">Dr. Sunita Gupta - Orthopedic Surgery</option>
                                    <option value="Dr. Vikram Singh">Dr. Vikram Singh - Laparoscopic Surgery</option>
                                </select>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Surgery Date</label>
                                        <input type="date" class="form-control" name="surgery_date" min="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Surgery Time</label>
                                        <select class="form-control" name="surgery_time" required>
                                            <option value="">Select Time</option>
                                            <option value="07:00:00">07:00 AM</option>
                                            <option value="09:00:00">09:00 AM</option>
                                            <option value="11:00:00">11:00 AM</option>
                                            <option value="14:00:00">02:00 PM</option>
                                            <option value="16:00:00">04:00 PM</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Operation Theater</label>
                                        <select class="form-control" name="operation_theater" required>
                                            <option value="">Select OT</option>
                                            <option value="OT-1">Operation Theater 1</option>
                                            <option value="OT-2">Operation Theater 2</option>
                                            <option value="OT-3">Operation Theater 3</option>
                                            <option value="OT-4">Operation Theater 4</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Estimated Duration</label>
                                        <select class="form-control" name="estimated_duration" required>
                                            <option value="">Select Duration</option>
                                            <option value="1-2 hours">1-2 hours</option>
                                            <option value="2-4 hours">2-4 hours</option>
                                            <option value="4-6 hours">4-6 hours</option>
                                            <option value="6+ hours">6+ hours</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Pre-operative Notes</label>
                                <textarea class="form-control" name="notes" rows="3" placeholder="Any special instructions or patient conditions"></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-surgery text-white w-100">
                                <i class="fas fa-procedures"></i> Schedule Surgery
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="service-card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> Upcoming Surgeries</h5>
                    </div>
                    <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                        <?php if (count($recent_surgeries) > 0): ?>
                            <?php foreach ($recent_surgeries as $surgery): ?>
                            <div class="card mb-2">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between">
                                        <strong><?php echo htmlspecialchars($surgery['patient_name']); ?></strong>
                                        <span class="badge bg-<?php 
                                            echo $surgery['status'] === 'Scheduled' ? 'primary' : 
                                                ($surgery['status'] === 'In Progress' ? 'warning' : 'success'); 
                                        ?>">
                                            <?php echo $surgery['status']; ?>
                                        </span>
                                    </div>
                                    <p class="mb-1"><?php echo htmlspecialchars($surgery['surgery_type']); ?></p>
                                    <small class="text-muted">
                                        <i class="fas fa-user-md"></i> <?php echo htmlspecialchars($surgery['surgeon_name']); ?><br>
                                        <i class="fas fa-calendar"></i> <?php echo date('d M Y', strtotime($surgery['surgery_date'])); ?> 
                                        <i class="fas fa-clock"></i> <?php echo date('H:i', strtotime($surgery['surgery_time'])); ?><br>
                                        <i class="fas fa-hospital"></i> <?php echo $surgery['operation_theater']; ?> | 
                                        <i class="fas fa-rupee-sign"></i> <?php echo number_format($surgery['cost'], 2); ?>
                                    </small>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted text-center">No upcoming surgeries scheduled.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-md-12">
                <h3 class="text-center mb-4">Our Surgical Specialties</h3>
            </div>
            
            <div class="col-md-6">
                <div class="service-card p-4">
                    <h5><i class="fas fa-heart text-danger"></i> Cardiac Surgery</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success"></i> Heart Bypass Surgery</li>
                        <li><i class="fas fa-check text-success"></i> Valve Replacement</li>
                        <li><i class="fas fa-check text-success"></i> Angioplasty</li>
                        <li><i class="fas fa-check text-success"></i> Pacemaker Implantation</li>
                    </ul>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="service-card p-4">
                    <h5><i class="fas fa-brain text-info"></i> Neurosurgery</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success"></i> Brain Tumor Surgery</li>
                        <li><i class="fas fa-check text-success"></i> Spine Surgery</li>
                        <li><i class="fas fa-check text-success"></i> Aneurysm Repair</li>
                        <li><i class="fas fa-check text-success"></i> Epilepsy Surgery</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="text-center mt-5">
            <a href="index.php" class="btn btn-secondary me-3">
                <i class="fas fa-home"></i> Back to Home
            </a>
            <a href="surgery-schedule.php" class="btn btn-outline-success me-3">
                <i class="fas fa-calendar"></i> View Surgery Schedule
            </a>
            <a href="book-appointment.php" class="btn btn-primary">
                <i class="fas fa-calendar-plus"></i> Book Consultation
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>