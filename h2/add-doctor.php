<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$error = '';
$success = '';

// Get departments for dropdown
$pdo = getConnection();
$departments = $pdo->query("SELECT * FROM departments ORDER BY name")->fetchAll();

if ($_POST) {
    $full_name = sanitize($_POST['full_name']);
    $specialization = sanitize($_POST['specialization']);
    $qualification = sanitize($_POST['qualification']);
    $experience = sanitize($_POST['experience']);
    $phone = sanitize($_POST['phone']);
    $email = sanitize($_POST['email']);
    $department_id = sanitize($_POST['department_id']);
    
    if (empty($full_name) || empty($specialization) || empty($qualification) || empty($experience) || empty($phone) || empty($email) || empty($department_id)) {
        $error = 'Please fill in all required fields';
    } elseif (!validateEmail($email)) {
        $error = 'Please enter a valid email address';
    } elseif (!validatePhone($phone)) {
        $error = 'Please enter a valid 10-digit phone number';
    } else {
        // Check if email already exists
        $check_email = $pdo->prepare("SELECT COUNT(*) FROM doctors WHERE email = ?");
        $check_email->execute([$email]);
        
        if ($check_email->fetchColumn() > 0) {
            $error = 'Email already exists. Please use a different email address.';
        } else {
            $doc_id = generateDoctorId();
            
            $stmt = $pdo->prepare("INSERT INTO doctors (doc_id, name, email, password, phone, department_id, specialization, qualification, experience, consultation_fee) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 500.00)");
            
            if ($stmt->execute([$doc_id, $full_name, $email, password_hash('doctor123', PASSWORD_DEFAULT), $phone, $department_id, $specialization, $qualification, $experience])) {
                header('Location: admin_dashboard.php?success=doctor_added');
                exit();
            } else {
                $error = 'Failed to add doctor. Please try again.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Doctor - Hospital Management</title>
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
                        <h2><i class="fas fa-user-md"></i> Add New Doctor</h2>
                        <p class="text-center text-white mb-4">
                            Enter the doctor's details to add them to the hospital system.
                        </p>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" id="doctorForm">
                            <div class="mb-3">
                                <label for="full_name" class="form-label text-white">Full Name</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" required value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="department_id" class="form-label text-white">Department</label>
                                <select class="form-control" id="department_id" name="department_id" required>
                                    <option value="">Select Department</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?php echo $dept['id']; ?>" <?php echo (isset($_POST['department_id']) && $_POST['department_id'] == $dept['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($dept['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="specialization" class="form-label text-white">Specialization</label>
                                <input type="text" class="form-control" id="specialization" name="specialization" required value="<?php echo isset($_POST['specialization']) ? htmlspecialchars($_POST['specialization']) : ''; ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="qualification" class="form-label text-white">Qualification</label>
                                <input type="text" class="form-control" id="qualification" name="qualification" required value="<?php echo isset($_POST['qualification']) ? htmlspecialchars($_POST['qualification']) : ''; ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="experience" class="form-label text-white">Experience</label>
                                <input type="number" class="form-control" id="experience" name="experience" required value="<?php echo isset($_POST['experience']) ? htmlspecialchars($_POST['experience']) : ''; ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label text-white">Contact Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label text-white">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="fas fa-user-plus"></i> Add Doctor
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