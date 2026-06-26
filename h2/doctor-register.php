<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$error = '';
$success = '';

if ($_POST) {
    $name = sanitize($_POST['name']);
    $specialization = sanitize($_POST['specialization']);
    $qualification = sanitize($_POST['qualification']);
    $experience = (int)$_POST['experience'];
    $phone = sanitize($_POST['phone']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($name) || empty($specialization) || empty($qualification) || empty($experience) || empty($phone) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields';
    } elseif (!validateEmail($email)) {
        $error = 'Please enter a valid email address';
    } elseif (!validatePhone($phone)) {
        $error = 'Please enter a valid 10-digit phone number';
    } elseif ($experience < 0 || $experience > 50) {
        $error = 'Please enter valid years of experience (0-50)';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        $pdo = getConnection();
        
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM doctors WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $error = 'Email address already registered';
        } else {
            // Generate Doctor ID and get default department
            $doc_id = generateDoctorId();
            $department_id = 1; // Default to first department
            
            // Handle image upload
            $image_name = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $image_name = uploadImage($_FILES['image'], 'doctors');
                if (!$image_name) {
                    $error = 'Failed to upload image';
                }
            }
            
            if (!$error) {
                // Insert new doctor
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $consultation_fee = 500.00; // Default fee
                
                $sql = "INSERT INTO doctors (doc_id, name, email, phone, department_id, specialization, qualification, experience, consultation_fee, password" . ($image_name ? ", image" : "") . ") VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?" . ($image_name ? ", ?" : "") . ")";
                $params = [$doc_id, $name, $email, $phone, $department_id, $specialization, $qualification, $experience, $consultation_fee, $hashed_password];
                if ($image_name) $params[] = $image_name;
                
                $stmt = $pdo->prepare($sql);
                
                if ($stmt->execute($params)) {
                    $success = "Registration successful! Your Doctor ID is: $doc_id. Please wait for admin approval.";
                } else {
                    $error = 'Registration failed. Please try again.';
                }
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
    <title>Doctor Registration - Hospital Management</title>
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
                        <h2><i class="fas fa-user-md"></i> Doctor Registration</h2>
                        <p class="text-center text-white mb-4">
                            Register as a doctor to access the hospital system and manage your assigned patients.
                        </p>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" enctype="multipart/form-data" id="doctorRegisterForm">
                            <div class="form-row">
                                <div>
                                    <label for="name" class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                                </div>
                                <div>
                                    <label for="specialization" class="form-label">Specialization *</label>
                                    <input type="text" class="form-control" id="specialization" name="specialization" required value="<?php echo isset($_POST['specialization']) ? htmlspecialchars($_POST['specialization']) : ''; ?>">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div>
                                    <label for="qualification" class="form-label">Qualification *</label>
                                    <input type="text" class="form-control" id="qualification" name="qualification" required value="<?php echo isset($_POST['qualification']) ? htmlspecialchars($_POST['qualification']) : ''; ?>">
                                </div>
                                <div>
                                    <label for="experience" class="form-label">Experience (Years) *</label>
                                    <input type="number" class="form-control" id="experience" name="experience" min="0" max="50" required value="<?php echo isset($_POST['experience']) ? $_POST['experience'] : ''; ?>">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div>
                                    <label for="phone" class="form-label">Contact Number *</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" required value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                                </div>
                                <div>
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div>
                                    <label for="password" class="form-label">Password *</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                <div>
                                    <label for="confirm_password" class="form-label">Confirm Password *</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                            </div>
                            
                            <div class="form-row single">
                                <div>
                                    <label for="image" class="form-label">Profile Photo</label>
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*" onchange="previewImage(this, 'imagePreview')">
                                    <img id="imagePreview" src="#" alt="Preview" style="display: none; width: 100px; height: 100px; object-fit: cover; margin-top: 10px; border-radius: 50%;">
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="fas fa-user-md"></i> Register
                            </button>
                        </form>
                        
                        <div class="text-center">
                            <p class="text-white">Already have an account? <a href="login.php?type=doctor" class="text-info">Login here</a></p>
                            <a href="index.php" class="text-info">Back to Home</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/main.js"></script>
    
    <script>
        document.getElementById('doctorRegisterForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const email = document.getElementById('email').value;
            const phone = document.getElementById('phone').value;
            const experience = document.getElementById('experience').value;
            
            if (!validateForm('doctorRegisterForm')) {
                e.preventDefault();
                showAlert('error', 'Validation Error', 'Please fill in all required fields');
                return;
            }
            
            if (!validateEmail(email)) {
                e.preventDefault();
                showAlert('error', 'Invalid Email', 'Please enter a valid email address');
                return;
            }
            
            if (!validatePhone(phone)) {
                e.preventDefault();
                showAlert('error', 'Invalid Phone', 'Please enter a valid 10-digit phone number');
                return;
            }
            
            if (experience < 0 || experience > 50) {
                e.preventDefault();
                showAlert('error', 'Invalid Experience', 'Please enter valid years of experience (0-50)');
                return;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                showAlert('error', 'Weak Password', 'Password must be at least 6 characters long');
                return;
            }
            
            if (password !== confirmPassword) {
                e.preventDefault();
                showAlert('error', 'Password Mismatch', 'Passwords do not match');
                return;
            }
        });
    </script>
</body>
</html>