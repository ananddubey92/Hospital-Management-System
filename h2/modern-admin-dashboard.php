<?php
session_start();
require_once __DIR__ . '/config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$pdo = getConnection();
$admin = $pdo->prepare("SELECT * FROM admin WHERE id = ?");
$admin->execute([$_SESSION['admin_id']]);
$admin_data = $admin->fetch();

// Get statistics
$stats = [
    'total_patients' => $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn(),
    'active_doctors' => $pdo->query("SELECT COUNT(*) FROM doctors WHERE status = 'active'")->fetchColumn(),
    'today_appointments' => $pdo->query("SELECT COUNT(*) FROM appointments WHERE DATE(appointment_date) = CURDATE()")->fetchColumn(),
    'available_rooms' => $pdo->query("SELECT COUNT(*) FROM rooms WHERE status = 'available'")->fetchColumn()
];

// Get recent patients
$recent_patients = $pdo->query("SELECT * FROM patients ORDER BY created_at DESC LIMIT 5")->fetchAll();

// Get doctors
$doctors = $pdo->query("SELECT * FROM doctors WHERE status = 'active' LIMIT 6")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediCare Plus - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --sidebar-width: 260px;
        }
        
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f8f9fa; }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(135deg, var(--primary-color), #34495e);
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-header h4 {
            color: white;
            margin: 0;
            font-size: 1.2rem;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .sidebar-menu .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            border-radius: 0;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        
        .sidebar-menu .nav-link:hover,
        .sidebar-menu .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left-color: var(--secondary-color);
        }
        
        .sidebar-menu .nav-link i {
            margin-right: 10px;
            width: 20px;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px;
            min-height: 100vh;
        }
        
        .top-bar {
            background: white;
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            border-left: 4px solid;
        }
        
        .stat-card:hover { transform: translateY(-5px); }
        .stat-card.primary { border-left-color: var(--secondary-color); }
        .stat-card.success { border-left-color: var(--success-color); }
        .stat-card.warning { border-left-color: var(--warning-color); }
        .stat-card.danger { border-left-color: var(--accent-color); }
        
        .stat-card .stat-icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }
        
        .stat-card .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin: 10px 0 5px 0;
        }
        
        .content-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .content-card .card-header {
            background: none;
            border-bottom: 1px solid #eee;
            padding: 15px 20px;
        }
        
        .content-card .card-body {
            padding: 20px;
        }
        
        .patient-item, .doctor-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            transition: background 0.3s ease;
        }
        
        .patient-item:hover, .doctor-item:hover {
            background: #f8f9fa;
        }
        
        .patient-item:last-child, .doctor-item:last-child {
            border-bottom: none;
        }
        
        .badge-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h4><i class="fas fa-hospital-alt"></i> MediCare Plus</h4>
            <small class="text-white-50">Admin Panel</small>
        </div>
        
        <nav class="sidebar-menu">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link active" href="#dashboard">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#patients">
                        <i class="fas fa-users"></i> Patients
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#doctors">
                        <i class="fas fa-user-md"></i> Doctors
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#appointments">
                        <i class="fas fa-calendar-alt"></i> Appointments
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#lab-reports">
                        <i class="fas fa-flask"></i> Lab Reports
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#surgery">
                        <i class="fas fa-procedures"></i> Surgery Schedule
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#departments">
                        <i class="fas fa-building"></i> Departments
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#rooms">
                        <i class="fas fa-bed"></i> Rooms
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#settings">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </li>
            </ul>
        </nav>
        
        <div class="position-absolute bottom-0 w-100 p-3">
            <a href="logout.php" class="btn btn-outline-light btn-sm w-100">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0">Dashboard Overview</h4>
                <small class="text-muted">Welcome back, <?php echo htmlspecialchars($admin_data['full_name'] ?? 'Admin'); ?>
                <?php if (!empty($admin_data['phone'])): ?>
                    &nbsp;|&nbsp;<i class="fas fa-phone text-success" style="font-size:11px;"></i>
                    <span style="font-size:12px;"><?php echo htmlspecialchars($admin_data['phone']); ?></span>
                <?php endif; ?>
                </small>
            </div>
            <div>
                <span class="badge bg-primary me-2"><?php echo date('M d, Y'); ?></span>
                <span class="badge bg-success"><?php echo date('H:i'); ?></span>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card primary">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="stat-number text-primary"><?php echo $stats['total_patients']; ?></div>
                            <div class="text-muted">Total Patients</div>
                        </div>
                        <div class="stat-icon text-primary">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card success">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="stat-number text-success"><?php echo $stats['active_doctors']; ?></div>
                            <div class="text-muted">Active Doctors</div>
                        </div>
                        <div class="stat-icon text-success">
                            <i class="fas fa-user-md"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card warning">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="stat-number text-warning"><?php echo $stats['today_appointments']; ?></div>
                            <div class="text-muted">Today's Appointments</div>
                        </div>
                        <div class="stat-icon text-warning">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card danger">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="stat-number text-danger"><?php echo $stats['available_rooms']; ?></div>
                            <div class="text-muted">Available Rooms</div>
                        </div>
                        <div class="stat-icon text-danger">
                            <i class="fas fa-bed"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Sections -->
        <div class="row">
            <!-- Recent Patients -->
            <div class="col-lg-6 mb-4">
                <div class="content-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-users text-primary"></i> Recent Patients</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php foreach ($recent_patients as $patient): ?>
                        <div class="patient-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($patient['name']); ?></h6>
                                    <small class="text-muted">ID: <?php echo htmlspecialchars($patient['pat_id']); ?></small>
                                </div>
                                <div>
                                    <span class="badge bg-success badge-status">Active</span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Doctor Profiles -->
            <div class="col-lg-6 mb-4">
                <div class="content-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-user-md text-success"></i> Doctor Profiles</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php foreach ($doctors as $doctor): ?>
                        <div class="doctor-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($doctor['name']); ?></h6>
                                    <small class="text-muted"><?php echo htmlspecialchars($doctor['specialization']); ?></small>
                                </div>
                                <div>
                                    <span class="badge bg-primary badge-status"><?php echo ucfirst($doctor['status']); ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lab Reports & Surgery Schedule -->
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="content-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-flask text-info"></i> Recent Lab Reports</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="mb-1">Blood Test - John Doe</h6>
                                <small class="text-muted">Completed • 2 hours ago</small>
                            </div>
                            <span class="badge bg-success">Normal</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="mb-1">X-Ray - Jane Smith</h6>
                                <small class="text-muted">Pending • 1 hour ago</small>
                            </div>
                            <span class="badge bg-warning">Pending</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">MRI Scan - Mike Johnson</h6>
                                <small class="text-muted">In Progress • 30 min ago</small>
                            </div>
                            <span class="badge bg-info">In Progress</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <div class="content-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-procedures text-danger"></i> Surgery Schedule</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="mb-1">Cardiac Surgery</h6>
                                <small class="text-muted">Dr. Smith • 09:00 AM</small>
                            </div>
                            <span class="badge bg-success">Scheduled</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="mb-1">Orthopedic Surgery</h6>
                                <small class="text-muted">Dr. Johnson • 02:00 PM</small>
                            </div>
                            <span class="badge bg-warning">Preparing</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Emergency Surgery</h6>
                                <small class="text-muted">Dr. Brown • 04:30 PM</small>
                            </div>
                            <span class="badge bg-danger">Urgent</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>