<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$doctor_id = $_GET['id'] ?? 0;
$pdo = getConnection();

// Get doctor details
$stmt = $pdo->prepare("SELECT * FROM doctors WHERE id = ?");
$stmt->execute([$doctor_id]);
$doctor = $stmt->fetch();

if (!$doctor) {
    header('Location: doctor-management.php');
    exit();
}

// Update doctor
if ($_POST) {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $specialization = sanitize($_POST['specialization']);
    $qualification = sanitize($_POST['qualification']);
    $experience = sanitize($_POST['experience']);
    $consultation_fee = sanitize($_POST['consultation_fee']);
    $department_id = sanitize($_POST['department_id']);
    
    $stmt = $pdo->prepare("UPDATE doctors SET name = ?, email = ?, phone = ?, specialization = ?, qualification = ?, experience = ?, consultation_fee = ?, department_id = ? WHERE id = ?");
    
    if ($stmt->execute([$name, $email, $phone, $specialization, $qualification, $experience, $consultation_fee, $department_id, $doctor_id])) {
        $success = "Doctor updated successfully!";
        // Refresh doctor data
        $stmt = $pdo->prepare("SELECT * FROM doctors WHERE id = ?");
        $stmt->execute([$doctor_id]);
        $doctor = $stmt->fetch();
    } else {
        $error = "Failed to update doctor.";
    }
}

// Get departments
$departments = $pdo->query("SELECT * FROM departments ORDER BY name")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Doctor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #28a74522, #ffffff); }
        .edit-header { 
            background: linear-gradient(135deg, #28a745, #1e7e34); 
            color: white; 
            padding: 30px 0; 
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="edit-header">
        <div class="container">
            <h1><i class="fas fa-user-edit"></i> Edit Doctor</h1>
            <p>Update doctor information</p>
        </div>
    </div>

    <div class="container my-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5><i class="fas fa-edit"></i> Edit Dr. <?php echo htmlspecialchars($doctor['name']); ?></h5>
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
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Doctor ID</label>
                                        <input type="text" class="form-control" value="<?php echo $doctor['doc_id']; ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($doctor['name']); ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($doctor['email']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Phone</label>
                                        <input type="tel" class="form-control" name="phone" value="<?php echo htmlspecialchars($doctor['phone']); ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Department</label>
                                        <select class="form-control" name="department_id" required>
                                            <?php foreach ($departments as $dept): ?>
                                                <option value="<?php echo $dept['id']; ?>" <?php echo $doctor['department_id'] == $dept['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($dept['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Specialization</label>
                                        <input type="text" class="form-control" name="specialization" value="<?php echo htmlspecialchars($doctor['specialization']); ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Qualification</label>
                                <input type="text" class="form-control" name="qualification" value="<?php echo htmlspecialchars($doctor['qualification']); ?>" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Experience (Years)</label>
                                        <input type="number" class="form-control" name="experience" value="<?php echo $doctor['experience']; ?>" min="0" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Consultation Fee (₹)</label>
                                        <input type="number" class="form-control" name="consultation_fee" value="<?php echo $doctor['consultation_fee']; ?>" min="0" step="0.01" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-center">
                                <button type="submit" class="btn btn-success me-2">
                                    <i class="fas fa-save"></i> Update Doctor
                                </button>
                                <a href="doctor-management.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to Management
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>