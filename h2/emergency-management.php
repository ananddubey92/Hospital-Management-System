<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = getConnection();

if ($_POST) {
    $patient_name = sanitize($_POST['patient_name']);
    $contact_number = sanitize($_POST['contact_number']);
    $emergency_type = sanitize($_POST['emergency_type']);
    $priority_level = sanitize($_POST['priority_level']);
    $notes = sanitize($_POST['notes']);
    
    $case_id = 'EMR' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    $stmt = $pdo->prepare("INSERT INTO emergency_cases (case_id, patient_name, contact_number, emergency_type, priority_level, notes) VALUES (?, ?, ?, ?, ?, ?)");
    
    if ($stmt->execute([$case_id, $patient_name, $contact_number, $emergency_type, $priority_level, $notes])) {
        $success = "Emergency case registered! Case ID: $case_id";
    } else {
        $error = "Failed to register emergency case.";
    }
}

// Get recent cases
$cases = $pdo->query("SELECT * FROM emergency_cases ORDER BY created_at DESC LIMIT 10")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emergency Care Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #dc354522, #ffffff); }
        .emergency-header { background: #dc3545; color: white; padding: 30px 0; }
        .priority-critical { border-left: 5px solid #dc3545; }
        .priority-high { border-left: 5px solid #fd7e14; }
        .priority-medium { border-left: 5px solid #ffc107; }
        .priority-low { border-left: 5px solid #28a745; }
    </style>
</head>
<body>
    <div class="emergency-header text-center">
        <div class="container">
            <h1><i class="fas fa-ambulance"></i> Emergency Care Management</h1>
            <p>24/7 Critical Care Services</p>
        </div>
    </div>

    <div class="container my-5">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h5><i class="fas fa-plus-circle"></i> Register Emergency Case</h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Patient Name</label>
                                <input type="text" class="form-control" name="patient_name" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Contact Number</label>
                                <input type="tel" class="form-control" name="contact_number" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Emergency Type</label>
                                <select class="form-control" name="emergency_type" required>
                                    <option value="">Select Type</option>
                                    <option value="Cardiac Arrest">Cardiac Arrest</option>
                                    <option value="Stroke">Stroke</option>
                                    <option value="Trauma">Trauma</option>
                                    <option value="Respiratory Distress">Respiratory Distress</option>
                                    <option value="Poisoning">Poisoning</option>
                                    <option value="Burns">Burns</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Priority Level</label>
                                <select class="form-control" name="priority_level" required>
                                    <option value="">Select Priority</option>
                                    <option value="Critical">Critical</option>
                                    <option value="High">High</option>
                                    <option value="Medium">Medium</option>
                                    <option value="Low">Low</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control" name="notes" rows="3"></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-ambulance"></i> Register Emergency
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h5><i class="fas fa-list"></i> Recent Emergency Cases</h5>
                    </div>
                    <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                        <?php foreach ($cases as $case): ?>
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
                                <small class="text-muted">
                                    <?php echo $case['emergency_type']; ?> | 
                                    <?php echo date('d M Y H:i', strtotime($case['created_at'])); ?>
                                </small>
                            </div>
                        </div>
                        <?php endforeach; ?>
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