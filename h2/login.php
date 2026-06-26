<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$user_type = isset($_GET['type']) ? sanitize($_GET['type']) : 'patient';
$error = '';
$success = '';

if ($_POST) {
    if ($user_type === 'patient') {
        $patient_id = sanitize($_POST['patient_id']);
        $password = $_POST['password'];
        
        if (empty($patient_id) || empty($password)) {
            $error = 'Please fill in all fields';
        } else {
            $pdo = getConnection();
            $stmt = $pdo->prepare("SELECT * FROM patients WHERE pat_id = ?");
            $stmt->execute([$patient_id]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['patient_id'] = $user['id'];
                $_SESSION['patient_name'] = $user['name'];
                $_SESSION['patient_pat_id'] = $user['pat_id'];
                header('Location: patient_dashboard.php');
                exit();
            } else {
                $error = 'Invalid Patient ID or password';
            }
        }
    } elseif ($user_type === 'doctor') {
        $doctor_id = sanitize($_POST['doctor_id']);
        $password = $_POST['password'];
        
        if (empty($doctor_id) || empty($password)) {
            $error = 'Please fill in all fields';
        } else {
            $pdo = getConnection();
            $stmt = $pdo->prepare("SELECT * FROM doctors WHERE doc_id = ? AND status = 'active'");
            $stmt->execute([$doctor_id]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['doctor_id'] = $user['id'];
                $_SESSION['doctor_name'] = $user['name'];
                $_SESSION['doctor_doc_id'] = $user['doc_id'];
                header('Location: doctor_dashboard.php');
                exit();
            } else {
                $error = 'Invalid Doctor ID or password';
            }
        }
    } else {
        $email = sanitize($_POST['email']);
        $password = $_POST['password'];
        
        if (empty($email) || empty($password)) {
            $error = 'Please fill in all fields';
        } else {
            $pdo = getConnection();
            $stmt = $pdo->prepare("SELECT * FROM admin WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_name'] = $user['full_name'];
                header('Location: admin_dashboard.php');
                exit();
            } else {
                $error = 'Invalid email or password';
            }
        }
    }
}

$page_titles = [
    'admin' => 'Admin Login',
    'doctor' => 'Doctor Login',
    'patient' => 'Patient Login'
];

$page_icons = [
    'admin' => 'fa-user-shield',
    'doctor' => 'fa-user-md',
    'patient' => 'fa-user'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_titles[$user_type]; ?> - Hospital Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <div class="auth-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="auth-card glass">
                        <h2><i class="fas <?php echo $page_icons[$user_type]; ?>"></i> <?php echo $page_titles[$user_type]; ?></h2>
                        
                        <?php if ($user_type === 'admin'): ?>
                        <p class="text-center text-white mb-4">
                            Welcome, Administrator. Please enter your credentials to access the hospital management dashboard.
                        </p>
                        <?php elseif ($user_type === 'patient'): ?>
                        <p class="text-center text-white mb-4">
                            Welcome to the Hospital Management System.<br>
                            Please enter your registered Patient ID and Password to access your medical records, appointments, and prescriptions.
                        </p>
                        <?php elseif ($user_type === 'doctor'): ?>
                        <p class="text-center text-white mb-4">
                            Welcome Doctor. Please enter your credentials to access your dashboard and patient records.
                        </p>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" id="loginForm">
                            <?php if ($user_type === 'patient'): ?>
                                <div class="mb-3">
                                    <label for="patient_id" class="form-label">Patient ID</label>
                                    <input type="text" class="form-control" id="patient_id" name="patient_id" placeholder="Enter your Patient ID (e.g., PAT0001)" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                                </div>
                            <?php elseif ($user_type === 'doctor'): ?>
                                <div class="mb-3">
                                    <label for="doctor_id" class="form-label">Doctor ID</label>
                                    <input type="text" class="form-control" id="doctor_id" name="doctor_id" placeholder="Enter your Doctor ID (e.g., DOC0001)" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                                </div>
                            <?php else: ?>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                            <?php endif; ?>
                            
                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </button>
                        </form>
                        
                        <div class="text-center mb-3">
                            <a href="forgot-password.php?type=<?php echo $user_type; ?>" class="text-info">
                                <i class="fas fa-key"></i> Forgot Password?
                            </a>
                        </div>
                        
                        <div class="text-center">
                            <?php if ($user_type === 'patient'): ?>
                                <p class="text-white">Don't have an account? <a href="register.php" class="text-info">Register here</a></p>
                            <?php elseif ($user_type === 'doctor'): ?>
                                <p class="text-white">Don't have an account? <a href="doctor-register.php" class="text-info">Register here</a></p>
                            <?php elseif ($user_type === 'admin'): ?>
                                <p class="text-white">Don't have an account? <a href="admin-register.php" class="text-info">Register here</a></p>
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <p class="text-white mb-2">Login as:</p>
                                <a href="login.php?type=admin" class="btn btn-outline-light btn-sm me-2 <?php echo $user_type === 'admin' ? 'active' : ''; ?>">Admin</a>
                                <a href="login.php?type=doctor" class="btn btn-outline-light btn-sm me-2 <?php echo $user_type === 'doctor' ? 'active' : ''; ?>">Doctor</a>
                                <a href="login.php?type=patient" class="btn btn-outline-light btn-sm <?php echo $user_type === 'patient' ? 'active' : ''; ?>">Patient</a>
                            </div>
                            
                            <a href="index.php" class="text-info">Back to Home</a>
                        </div>
                        
                        <?php if ($user_type === 'admin'): ?>
                        <div class="mt-4 p-3 rounded" style="background:rgba(0,188,212,0.08);border:1px solid rgba(0,188,212,0.2);">
                            <small class="text-white">
                                <i class="fas fa-info-circle me-1 text-info"></i>
                                Don't have an account yet?
                                <a href="admin-register.php" class="text-info fw-bold">Register as Admin</a>
                            </small>
                        </div>
                        <?php elseif ($user_type === 'doctor'): ?>
                        <div class="mt-4 p-3 bg-success bg-opacity-10 rounded">
                            <small class="text-white">
                                <strong>Demo Doctor:</strong><br>
                                Email: john@hospital.com<br>
                                Password: password
                            </small>
                        </div>
                        <?php elseif ($user_type === 'patient'): ?>
                        <div class="mt-4 p-3 bg-warning bg-opacity-10 rounded">
                            <small class="text-white">
                                <strong>Demo Patient:</strong><br>
                                Patient ID: PAT001<br>
                                Password: password
                            </small>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/main.js"></script>
    
    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            if (!validateForm('loginForm')) {
                e.preventDefault();
                showAlert('error', 'Validation Error', 'Please fill in all required fields');
            }
        });
    </script>
</body>
</html>