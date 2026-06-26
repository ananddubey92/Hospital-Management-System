<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$error = '';
$success = '';

if ($_POST) {
    $name = sanitize($_POST['name']);
    $age = (int)$_POST['age'];
    $gender = sanitize($_POST['gender']);
    $phone = sanitize($_POST['phone']);
    $email = sanitize($_POST['email']);
    $address = sanitize($_POST['address']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($name) || empty($age) || empty($gender) || empty($phone) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields';
    } elseif (!validateEmail($email)) {
        $error = 'Please enter a valid email address';
    } elseif (!validatePhone($phone)) {
        $error = 'Please enter a valid 10-digit phone number';
    } elseif ($age < 1 || $age > 120) {
        $error = 'Please enter a valid age';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        $pdo = getConnection();
        
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM patients WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $error = 'Email address already registered';
        } else {
            // Generate Patient ID and calculate DOB
            $pat_id = generatePatientId();
            $dob = date('Y-m-d', strtotime("-$age years"));
            
            // Handle image upload
            $image_name = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $image_name = uploadImage($_FILES['image'], 'patients');
                if (!$image_name) {
                    $error = 'Failed to upload image';
                }
            }
            
            if (!$error) {
                // Insert new patient
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO patients (pat_id, name, email, phone, dob, age, gender, address, password" . ($image_name ? ", image" : "") . ") VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?" . ($image_name ? ", ?" : "") . ")";
                $params = [$pat_id, $name, $email, $phone, $dob, $age, $gender, $address, $hashed_password];
                if ($image_name) $params[] = $image_name;
                
                $stmt = $pdo->prepare($sql);
                
                if ($stmt->execute($params)) {
                    $success = "Registration successful! Your Patient ID is: $pat_id. You can now login.";
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
    <title>Patient Registration - Hospital Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation Header -->
    <nav class="navbar navbar-expand-lg navbar-dark header fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-hospital-alt"></i> MediCare Plus
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="departments.php">Departments</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="doctors.php">Doctors</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="loginDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="login.php?type=admin"><i class="fas fa-user-shield"></i> Admin Login</a></li>
                            <li><a class="dropdown-item" href="login.php?type=doctor"><i class="fas fa-user-md"></i> Doctor Login</a></li>
                            <li><a class="dropdown-item" href="login.php?type=patient"><i class="fas fa-user"></i> Patient Login</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="register.php">
                            <i class="fas fa-user-plus"></i> Register
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content with Header Spacing -->
    <div class="container-fluid" style="margin-top: 80px; min-height: 100vh; padding: 20px;">
        <div class="row">
            <!-- Left Side - Patient Registration Form -->
            <div class="col-lg-5 col-md-7 col-sm-10">
                <div class="auth-card glass" style="margin-left: 0; margin-right: auto;">
                    <h2><i class="fas fa-user-plus"></i> Patient Registration</h2>
                    <p class="text-center text-white mb-4">
                        Create your patient account to access appointments, reports, prescriptions, and more.
                    </p>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" enctype="multipart/form-data" id="registerForm">
                            <div class="form-row">
                                <div>
                                    <label for="name" class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                                </div>
                                <div>
                                    <label for="age" class="form-label">Age *</label>
                                    <input type="number" class="form-control" id="age" name="age" min="1" max="120" required value="<?php echo isset($_POST['age']) ? $_POST['age'] : ''; ?>">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div>
                                    <label for="gender" class="form-label">Gender *</label>
                                    <select class="form-control" id="gender" name="gender" required>
                                        <option value="">Select Gender</option>
                                        <option value="male" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'male') ? 'selected' : ''; ?>>Male</option>
                                        <option value="female" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'female') ? 'selected' : ''; ?>>Female</option>
                                        <option value="other" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'other') ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="phone" class="form-label">Contact Number *</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" required value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                                </div>
                            </div>
                            
                            <div class="form-row single">
                                <div>
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                </div>
                            </div>
                            
                            <div class="form-row single">
                                <div>
                                    <label for="address" class="form-label">Address</label>
                                    <textarea class="form-control" id="address" name="address" rows="2"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
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
                            
                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="fas fa-user-plus"></i> Register
                            </button>
                        </form>
                        
                        <div class="text-center">
                            <p class="text-white">Already have an account? <a href="login.php?type=patient" class="text-info">Login here</a></p>
                            <a href="index.php" class="text-info">Back to Home</a>
                        </div>
                    </div>
                </div>
            
            <!-- Right Side - Information Panel -->
            <div class="col-lg-7 col-md-5 d-none d-lg-block">
                <div class="info-panel glass" style="padding: 30px; margin-top: 20px; margin-left: 40px;">
                    <h3 class="text-white mb-4"><i class="fas fa-info-circle"></i> Why Register?</h3>
                    <div class="feature-list">
                        <div class="feature-item mb-3">
                            <i class="fas fa-calendar-check text-primary"></i>
                            <span class="text-white ms-3">Book appointments online</span>
                        </div>
                        <div class="feature-item mb-3">
                            <i class="fas fa-file-medical text-success"></i>
                            <span class="text-white ms-3">Access medical reports</span>
                        </div>
                        <div class="feature-item mb-3">
                            <i class="fas fa-prescription-bottle text-warning"></i>
                            <span class="text-white ms-3">View prescriptions</span>
                        </div>
                        <div class="feature-item mb-3">
                            <i class="fas fa-history text-info"></i>
                            <span class="text-white ms-3">Track medical history</span>
                        </div>
                        <div class="feature-item mb-3">
                            <i class="fas fa-bell text-danger"></i>
                            <span class="text-white ms-3">Get appointment reminders</span>
                        </div>
                    </div>
                    
                    <div class="mt-4 p-3" style="background: rgba(0, 188, 212, 0.1); border-radius: 10px; border-left: 4px solid #00bcd4;">
                        <h5 class="text-white"><i class="fas fa-shield-alt"></i> Secure & Private</h5>
                        <p class="text-white-50 mb-0">Your medical information is protected with advanced encryption and privacy measures.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/main.js"></script>
    
    <script>
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const email = document.getElementById('email').value;
            const phone = document.getElementById('phone').value;
            const age = document.getElementById('age').value;
            
            if (!validateForm('registerForm')) {
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
            
            if (age < 1 || age > 120) {
                e.preventDefault();
                showAlert('error', 'Invalid Age', 'Please enter a valid age between 1 and 120');
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