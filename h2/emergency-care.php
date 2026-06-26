<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = getConnection();

// Create emergency_cases table if not exists
$pdo->exec("CREATE TABLE IF NOT EXISTS emergency_cases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_id VARCHAR(20),
    patient_name VARCHAR(100),
    patient_phone VARCHAR(15),
    emergency_type VARCHAR(50),
    priority_level ENUM('Low', 'Medium', 'High', 'Critical'),
    status ENUM('Pending', 'In Progress', 'Completed', 'Cancelled') DEFAULT 'Pending',
    assigned_doctor VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

if ($_POST) {
    $patient_name = sanitize($_POST['patient_name']);
    $patient_phone = sanitize($_POST['patient_phone']);
    $emergency_type = sanitize($_POST['emergency_type']);
    $priority_level = sanitize($_POST['priority_level']);
    $notes = sanitize($_POST['notes']);
    
    $case_id = 'EMR' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
    
    $stmt = $pdo->prepare("INSERT INTO emergency_cases (case_id, patient_name, patient_phone, emergency_type, priority_level, notes) VALUES (?, ?, ?, ?, ?, ?)");
    
    if ($stmt->execute([$case_id, $patient_name, $patient_phone, $emergency_type, $priority_level, $notes])) {
        $success = "Emergency case registered successfully! Case ID: $case_id";
    } else {
        $error = "Failed to register emergency case.";
    }
}

$recent_cases = $pdo->query("SELECT * FROM emergency_cases ORDER BY 
    CASE priority_level 
        WHEN 'Critical' THEN 1 
        WHEN 'High' THEN 2 
        WHEN 'Medium' THEN 3 
        WHEN 'Low' THEN 4 
    END, created_at DESC LIMIT 5")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emergency Care - 24/7 Critical Care Services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #dc354522, #ffffff); }
        .emergency-header { 
            background: linear-gradient(135deg, #dc3545, #c82333); 
            color: white; 
            padding: 40px 0; 
            text-align: center;
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
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
        .priority-critical { border-left: 5px solid #dc3545; background: #f8d7da; }
        .priority-high { border-left: 5px solid #fd7e14; background: #fff3cd; }
        .priority-medium { border-left: 5px solid #ffc107; background: #fff3cd; }
        .priority-low { border-left: 5px solid #28a745; background: #d4edda; }
        .btn-emergency { 
            background: linear-gradient(135deg, #dc3545, #c82333);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-emergency:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(220, 53, 69, 0.4);
        }
    </style>
</head>
<body>
    <div class="emergency-header">
        <div class="container">
            <h1 class="display-4 mb-3">
                <i class="fas fa-ambulance"></i> Emergency Care
            </h1>
            <p class="lead">24/7 Critical Care & Emergency Response Center</p>
            <p class="mb-0">Immediate medical attention • Expert emergency team • Life-saving care</p>
        </div>
    </div>

    <div class="container my-5">
        <div class="row mb-5">
            <div class="col-md-3">
                <div class="service-card text-center p-4">
                    <i class="fas fa-ambulance fa-3x text-danger mb-3"></i>
                    <h4>24/7</h4>
                    <p class="text-muted">Emergency Response</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="service-card text-center p-4">
                    <i class="fas fa-heartbeat fa-3x text-warning mb-3"></i>
                    <h4>Critical</h4>
                    <p class="text-muted">Life Support</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="service-card text-center p-4">
                    <i class="fas fa-user-md fa-3x text-success mb-3"></i>
                    <h4>Expert</h4>
                    <p class="text-muted">Emergency Doctors</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="service-card text-center p-4">
                    <i class="fas fa-clock fa-3x text-info mb-3"></i>
                    <h4>Instant</h4>
                    <p class="text-muted">Response Time</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="service-card">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0"><i class="fas fa-plus-circle"></i> Register Emergency Case</h5>
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
                                <label class="form-label">Emergency Type</label>
                                <select class="form-control" name="emergency_type" required>
                                    <option value="">Select Emergency Type</option>
                                    <option value="Cardiac Arrest">Cardiac Arrest</option>
                                    <option value="Stroke">Stroke</option>
                                    <option value="Trauma/Accident">Trauma/Accident</option>
                                    <option value="Respiratory Distress">Respiratory Distress</option>
                                    <option value="Poisoning">Poisoning</option>
                                    <option value="Burns">Burns</option>
                                    <option value="Seizure">Seizure</option>
                                    <option value="Severe Bleeding">Severe Bleeding</option>
                                    <option value="Unconsciousness">Unconsciousness</option>
                                    <option value="Other">Other Emergency</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Priority Level</label>
                                <select class="form-control" name="priority_level" required>
                                    <option value="">Select Priority</option>
                                    <option value="Critical">Critical - Life Threatening</option>
                                    <option value="High">High - Urgent Care Needed</option>
                                    <option value="Medium">Medium - Serious but Stable</option>
                                    <option value="Low">Low - Non-urgent</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Additional Notes</label>
                                <textarea class="form-control" name="notes" rows="3" placeholder="Describe symptoms, current condition, or any relevant information"></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-emergency text-white w-100">
                                <i class="fas fa-ambulance"></i> Register Emergency Case
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="service-card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-list-alt"></i> Active Emergency Cases</h5>
                    </div>
                    <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                        <?php if (count($recent_cases) > 0): ?>
                            <?php foreach ($recent_cases as $case): ?>
                            <div class="card mb-2 priority-<?php echo strtolower($case['priority_level']); ?>">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between">
                                        <strong><?php echo htmlspecialchars($case['patient_name']); ?></strong>
                                        <span class="badge bg-<?php 
                                            echo $case['priority_level'] === 'Critical' ? 'danger' : 
                                                ($case['priority_level'] === 'High' ? 'warning' : 
                                                ($case['priority_level'] === 'Medium' ? 'info' : 'success')); 
                                        ?>">
                                            <?php echo $case['priority_level']; ?>
                                        </span>
                                    </div>
                                    <p class="mb-1"><?php echo htmlspecialchars($case['emergency_type']); ?></p>
                                    <small class="text-muted">
                                        <i class="fas fa-phone"></i> <?php echo htmlspecialchars($case['patient_phone']); ?> |
                                        <i class="fas fa-clock"></i> <?php echo date('d M Y H:i', strtotime($case['created_at'])); ?>
                                    </small>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted text-center">No active emergency cases.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-md-12">
                <h3 class="text-center mb-4">Emergency Services Available</h3>
            </div>
            
            <div class="col-md-6">
                <div class="service-card p-4">
                    <h5><i class="fas fa-heartbeat text-danger"></i> Critical Care</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success"></i> Cardiac Emergency</li>
                        <li><i class="fas fa-check text-success"></i> Stroke Management</li>
                        <li><i class="fas fa-check text-success"></i> Respiratory Support</li>
                        <li><i class="fas fa-check text-success"></i> Trauma Care</li>
                        <li><i class="fas fa-check text-success"></i> Life Support Systems</li>
                    </ul>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="service-card p-4">
                    <h5><i class="fas fa-ambulance text-warning"></i> Emergency Response</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success"></i> 24/7 Ambulance Service</li>
                        <li><i class="fas fa-check text-success"></i> Emergency Surgery</li>
                        <li><i class="fas fa-check text-success"></i> Poison Control</li>
                        <li><i class="fas fa-check text-success"></i> Burn Treatment</li>
                        <li><i class="fas fa-check text-success"></i> Emergency Diagnostics</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="text-center mt-5">
            <a href="index.php" class="btn btn-secondary me-3">
                <i class="fas fa-home"></i> Back to Home
            </a>
            <a href="emergency-services.php" class="btn btn-outline-danger me-3">
                <i class="fas fa-list"></i> View All Cases
            </a>
            <a href="book-appointment.php" class="btn btn-primary">
                <i class="fas fa-calendar-plus"></i> Book Appointment
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>