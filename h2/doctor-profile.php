<?php
require_once __DIR__ . '/config/database.php';

$doctor_id = $_GET['id'] ?? 0;

try {
    $pdo = getConnection();
    
    // Get doctor details with department
    $stmt = $pdo->prepare("SELECT d.*, dept.name as department_name FROM doctors d LEFT JOIN departments dept ON d.department_id = dept.id WHERE d.id = ?");
    $stmt->execute([$doctor_id]);
    $doctor = $stmt->fetch();
    
    if (!$doctor) {
        header('Location: doctors.php');
        exit();
    }
    
} catch(PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dr. <?php echo htmlspecialchars($doctor['name']); ?> - Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #007bff22, #ffffff); }
        .profile-header { 
            background: linear-gradient(135deg, #007bff, #0056b3); 
            color: white; 
            padding: 40px 0; 
        }
        .profile-card { 
            border: none; 
            border-radius: 15px; 
            box-shadow: 0 8px 25px rgba(0,0,0,0.1); 
            margin-bottom: 20px;
        }
        .doctor-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="profile-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-3 text-center">
                    <img src="<?php echo $doctor['image'] ? 'uploads/doctors/' . $doctor['image'] : 'https://via.placeholder.com/150'; ?>" 
                         alt="Dr. <?php echo htmlspecialchars($doctor['name']); ?>" class="doctor-image">
                </div>
                <div class="col-md-9">
                    <h1>Dr. <?php echo htmlspecialchars($doctor['name']); ?></h1>
                    <h4><?php echo htmlspecialchars($doctor['specialization']); ?></h4>
                    <p class="lead"><?php echo htmlspecialchars($doctor['department_name']); ?> Department</p>
                    <div class="d-flex align-items-center">
                        <div class="me-4">
                            <span class="text-warning">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star<?php echo $i <= ($doctor['rating'] ?? 4.0) ? '' : '-o'; ?>"></i>
                                <?php endfor; ?>
                            </span>
                            <span class="ms-2"><?php echo $doctor['rating'] ?? 4.0; ?>/5</span>
                        </div>
                        <div>
                            <span class="badge bg-<?php echo ($doctor['status'] ?? 'active') === 'active' ? 'success' : 'secondary'; ?> fs-6">
                                <?php echo ucfirst($doctor['status'] ?? 'active'); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container my-5">
        <div class="row">
            <div class="col-md-8">
                <div class="profile-card">
                    <div class="card-header bg-primary text-white">
                        <h5><i class="fas fa-user"></i> Professional Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Qualification</h6>
                                <p><?php echo htmlspecialchars($doctor['qualification']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <h6>Experience</h6>
                                <p><?php echo $doctor['experience']; ?> years</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Department</h6>
                                <p><?php echo htmlspecialchars($doctor['department_name']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <h6>Consultation Fee</h6>
                                <p class="text-success fw-bold">₹<?php echo number_format($doctor['consultation_fee'], 2); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="profile-card">
                    <div class="card-header bg-info text-white">
                        <h5><i class="fas fa-phone"></i> Contact Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Phone</h6>
                                <p><i class="fas fa-phone text-success"></i> <?php echo htmlspecialchars($doctor['phone']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <h6>Email</h6>
                                <p><i class="fas fa-envelope text-primary"></i> <?php echo htmlspecialchars($doctor['email']); ?></p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <h6>Address</h6>
                                <p><?php echo htmlspecialchars($doctor['address'] ?? 'Hospital Address: 123 Medical Center, Healthcare City'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="profile-card">
                    <div class="card-header bg-success text-white">
                        <h5><i class="fas fa-calendar-plus"></i> Book Appointment</h5>
                    </div>
                    <div class="card-body text-center">
                        <p>Schedule your consultation with Dr. <?php echo htmlspecialchars($doctor['name']); ?></p>
                        <div class="d-grid gap-2">
                            <a href="book-appointment.php?doctor_id=<?php echo $doctor['id']; ?>" class="btn btn-success">
                                <i class="fas fa-calendar-check"></i> Book Now
                            </a>
                            <a href="appointment-management.php" class="btn btn-outline-primary">
                                <i class="fas fa-calendar-alt"></i> View Appointments
                            </a>
                        </div>
                    </div>
                </div>

                <div class="profile-card">
                    <div class="card-header bg-warning text-dark">
                        <h5><i class="fas fa-clock"></i> Available Hours</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Monday - Friday:</strong><br>9:00 AM - 5:00 PM</p>
                        <p><strong>Saturday:</strong><br>9:00 AM - 1:00 PM</p>
                        <p><strong>Sunday:</strong><br>Emergency Only</p>
                        <hr>
                        <p class="text-muted"><small>Please call ahead to confirm availability</small></p>
                    </div>
                </div>

                <div class="profile-card">
                    <div class="card-header bg-secondary text-white">
                        <h5><i class="fas fa-info-circle"></i> Quick Info</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Doctor ID:</strong> <?php echo htmlspecialchars($doctor['doc_id']); ?></p>
                        <p><strong>Joined:</strong> <?php echo date('M Y', strtotime($doctor['created_at'])); ?></p>
                        <p><strong>Status:</strong> 
                            <span class="badge bg-<?php echo ($doctor['status'] ?? 'active') === 'active' ? 'success' : 'secondary'; ?>">
                                <?php echo ucfirst($doctor['status'] ?? 'active'); ?>
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="doctors.php" class="btn btn-secondary me-2">
                <i class="fas fa-arrow-left"></i> Back to Doctors
            </a>
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="fas fa-home"></i> Home
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>