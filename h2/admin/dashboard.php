<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if admin is logged in (for future use)
// requireAdminLogin();

// Get dashboard statistics
$pdo = getConnection();

// Count statistics
$total_patients = $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();
$total_doctors = $pdo->query("SELECT COUNT(*) FROM doctors")->fetchColumn();
$total_appointments = $pdo->query("SELECT COUNT(*) FROM appointments WHERE DATE(appointment_date) = CURDATE()")->fetchColumn();
$total_departments = $pdo->query("SELECT COUNT(*) FROM departments")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Hospital Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="sidebar-header">
                <h4><i class="fas fa-hospital"></i> Hospital Admin</h4>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link active" href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="patients.php">
                        <i class="fas fa-users"></i> Patients
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="doctors.php">
                        <i class="fas fa-user-md"></i> Doctors
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="appointments.php">
                        <i class="fas fa-calendar-check"></i> Appointments
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="departments.php">
                        <i class="fas fa-building"></i> Departments
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="rooms.php">
                        <i class="fas fa-bed"></i> Rooms
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../login.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <div class="container-fluid">
                <div class="row mb-4">
                    <div class="col-12">
                        <h2><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h2>
                        <p class="text-muted">Welcome to the Hospital Management System</p>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card stat-card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h3><?php echo $total_patients; ?></h3>
                                        <p class="mb-0">Total Patients</p>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="fas fa-users"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h3><?php echo $total_doctors; ?></h3>
                                        <p class="mb-0">Total Doctors</p>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="fas fa-user-md"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h3><?php echo $total_appointments; ?></h3>
                                        <p class="mb-0">Today's Appointments</p>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="fas fa-calendar-check"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h3><?php echo $total_departments; ?></h3>
                                        <p class="mb-0">Departments</p>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="fas fa-building"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-bolt"></i> Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <a href="patients.php" class="btn btn-outline-primary w-100">
                                            <i class="fas fa-user-plus"></i><br>
                                            Add New Patient
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="doctors.php" class="btn btn-outline-success w-100">
                                            <i class="fas fa-user-md"></i><br>
                                            Add New Doctor
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="appointments.php" class="btn btn-outline-warning w-100">
                                            <i class="fas fa-calendar-plus"></i><br>
                                            Schedule Appointment
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="departments.php" class="btn btn-outline-info w-100">
                                            <i class="fas fa-building"></i><br>
                                            Manage Departments
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>