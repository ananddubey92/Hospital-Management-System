<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$pdo = getConnection();

// Create tables if they don't exist
$pdo->exec("CREATE TABLE IF NOT EXISTS doctors (id INT AUTO_INCREMENT PRIMARY KEY, doc_id VARCHAR(20), name VARCHAR(100), email VARCHAR(100), password VARCHAR(255), phone VARCHAR(15), department_id INT, specialization VARCHAR(100), qualification VARCHAR(100), experience INT, consultation_fee DECIMAL(10,2), status ENUM('active','inactive') DEFAULT 'active', rating DECIMAL(2,1) DEFAULT 4.5, image VARCHAR(255), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
$pdo->exec("CREATE TABLE IF NOT EXISTS patients (id INT AUTO_INCREMENT PRIMARY KEY, pat_id VARCHAR(20), name VARCHAR(100), email VARCHAR(100), password VARCHAR(255), phone VARCHAR(15), address TEXT, date_of_birth DATE, gender ENUM('Male','Female','Other'), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
$pdo->exec("CREATE TABLE IF NOT EXISTS departments (id INT AUTO_INCREMENT PRIMARY KEY, dept_id VARCHAR(20), name VARCHAR(100), description TEXT, head_of_department VARCHAR(100), status ENUM('active','inactive') DEFAULT 'active', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
$pdo->exec("CREATE TABLE IF NOT EXISTS appointments (id INT AUTO_INCREMENT PRIMARY KEY, appointment_date DATE, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
$pdo->exec("CREATE TABLE IF NOT EXISTS rooms (id INT AUTO_INCREMENT PRIMARY KEY, status ENUM('available','occupied') DEFAULT 'available')");
$pdo->exec("CREATE TABLE IF NOT EXISTS services (id INT AUTO_INCREMENT PRIMARY KEY, status ENUM('active','inactive') DEFAULT 'active')");

// Get comprehensive statistics
$stats = [
    'total_doctors' => $pdo->query("SELECT COUNT(*) FROM doctors WHERE status = 'active'")->fetchColumn(),
    'total_patients' => $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn(),
    'total_departments' => $pdo->query("SELECT COUNT(*) FROM departments")->fetchColumn(),
    'total_appointments' => $pdo->query("SELECT COUNT(*) FROM appointments WHERE appointment_date >= CURDATE()")->fetchColumn(),
    'available_rooms' => $pdo->query("SELECT COUNT(*) FROM rooms WHERE status = 'available'")->fetchColumn(),
    'total_services' => $pdo->query("SELECT COUNT(*) FROM services WHERE status = 'active'")->fetchColumn()
];

// Get featured departments with doctor count
$departments = $pdo->query("
    SELECT d.*,
           COUNT(DISTINCT doc.id) AS total_doctors,
           MIN(doc.consultation_fee) AS min_fee
    FROM departments d
    LEFT JOIN doctors doc ON doc.department_id = d.id AND doc.status = 'active'
    WHERE d.status = 'active'
    GROUP BY d.id
    ORDER BY d.name
    LIMIT 6
")->fetchAll();

// Map department names to icons and gradient colours
$dept_meta = [
    'Cardiology'       => ['icon'=>'fa-heart-pulse',       'gradient'=>'135deg,#ff416c,#ff4b2b', 'hours'=>'Mon–Fri 8AM–6PM'],
    'Neurology'        => ['icon'=>'fa-brain',             'gradient'=>'135deg,#4facfe,#00f2fe', 'hours'=>'Mon–Fri 9AM–5PM'],
    'Orthopedics'      => ['icon'=>'fa-bone',              'gradient'=>'135deg,#43e97b,#38f9d7', 'hours'=>'Mon–Sat 8AM–4PM'],
    'Pediatrics'       => ['icon'=>'fa-child',             'gradient'=>'135deg,#f093fb,#f5576c', 'hours'=>'Mon–Fri 9AM–6PM'],
    'Gynecology'       => ['icon'=>'fa-venus',             'gradient'=>'135deg,#a18cd1,#fbc2eb', 'hours'=>'Mon–Sat 9AM–5PM'],
    'Ophthalmology'    => ['icon'=>'fa-eye',               'gradient'=>'135deg,#fda085,#f6d365', 'hours'=>'Mon–Fri 8AM–5PM'],
    'Dermatology'      => ['icon'=>'fa-hand-dots',         'gradient'=>'135deg,#30cfd0,#330867', 'hours'=>'Mon–Fri 10AM–6PM'],
    'Gastroenterology' => ['icon'=>'fa-stomach',           'gradient'=>'135deg,#96fbc4,#f9f586', 'hours'=>'Mon–Fri 9AM–5PM'],
    'ENT'              => ['icon'=>'fa-ear-listen',        'gradient'=>'135deg,#f7971e,#ffd200', 'hours'=>'Mon–Sat 9AM–4PM'],
    'Oncology'         => ['icon'=>'fa-ribbon',            'gradient'=>'135deg,#667eea,#764ba2', 'hours'=>'Mon–Fri 8AM–4PM'],
    'Radiology'        => ['icon'=>'fa-x-ray',             'gradient'=>'135deg,#0f2027,#203a43', 'hours'=>'24 / 7'],
    'Emergency'        => ['icon'=>'fa-truck-medical',     'gradient'=>'135deg,#e52d27,#b31217', 'hours'=>'24 / 7'],
];
$dept_default_meta = ['icon'=>'fa-hospital','gradient'=>'135deg,#00bcd4,#2196f3','hours'=>'Mon–Fri 9AM–5PM'];


// Get top rated doctors with department name
$top_doctors = $pdo->query("
    SELECT d.*,
           dept.name AS department_name
    FROM doctors d
    LEFT JOIN departments dept ON d.department_id = dept.id
    WHERE d.status = 'active'
    ORDER BY d.rating DESC, d.experience DESC
    LIMIT 6
")->fetchAll();

// Languages fallback (column may not exist yet)
$doc_languages_default = 'English, Hindi';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Management System - Advanced Healthcare Solutions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <style>
        .service-hover { transition: transform 0.3s ease; }
        .service-hover:hover { transform: translateY(-5px); }

        /* ── Section shared ───────────────────────────────────── */
        .section-badge {
            display:inline-block; padding:6px 18px; border-radius:30px;
            font-size:12px; font-weight:700; letter-spacing:1.5px;
            text-transform:uppercase; margin-bottom:12px;
            background:rgba(0,188,212,.15); color:#00bcd4;
            border:1px solid rgba(0,188,212,.3);
        }
        .section-title {
            font-size:clamp(1.6rem,3.5vw,2.4rem); font-weight:800;
            color:#fff; line-height:1.2;
        }
        .section-title span { color:#00bcd4; }
        .section-sub { color:rgba(255,255,255,.65); font-size:15px; max-width:540px; margin:0 auto; }
        .sec-divider {
            width:60px; height:4px; border-radius:2px;
            background:linear-gradient(90deg,#00bcd4,#2196f3);
            margin:14px auto 0;
        }

        /* ── Dept search bar ──────────────────────────────────── */
        .dept-search-wrap {
            max-width:420px; margin:28px auto 0;
            position:relative;
        }
        .dept-search-wrap input {
            width:100%; padding:13px 50px 13px 20px;
            border-radius:40px;
            border:1px solid rgba(255,255,255,.25);
            background:rgba(255,255,255,.1);
            color:#fff; font-size:14px;
            backdrop-filter:blur(8px);
            outline:none; transition:.25s;
        }
        .dept-search-wrap input::placeholder { color:rgba(255,255,255,.55); }
        .dept-search-wrap input:focus { border-color:#00bcd4; background:rgba(255,255,255,.15); }
        .dept-search-wrap .search-ico {
            position:absolute; right:18px; top:50%; transform:translateY(-50%);
            color:rgba(255,255,255,.6); pointer-events:none;
        }

        /* ── Department cards ─────────────────────────────────── */
        .dept-card {
            border-radius:20px; overflow:hidden;
            background:rgba(255,255,255,.07);
            backdrop-filter:blur(14px);
            border:1px solid rgba(255,255,255,.15);
            transition:transform .3s, box-shadow .3s;
            display:flex; flex-direction:column; height:100%;
        }
        .dept-card:hover { transform:translateY(-8px); box-shadow:0 20px 50px rgba(0,0,0,.3); }
        .dept-card-banner {
            height:110px; display:flex; align-items:center; justify-content:center;
            position:relative; overflow:hidden;
        }
        .dept-card-banner::after {
            content:''; position:absolute; inset:0;
            background:rgba(0,0,0,.18);
        }
        .dept-card-banner i {
            font-size:3rem; color:#fff; position:relative; z-index:1;
            filter:drop-shadow(0 2px 8px rgba(0,0,0,.35));
        }
        .dept-card-body { padding:20px; flex:1; display:flex; flex-direction:column; }
        .dept-card-body h5 { color:#fff; font-weight:700; font-size:17px; margin-bottom:6px; }
        .dept-card-body .dept-desc {
            color:rgba(255,255,255,.65); font-size:13px; line-height:1.55;
            display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical;
            overflow:hidden; flex:1; margin-bottom:14px;
        }
        .dept-meta-row {
            display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:16px;
        }
        .dept-meta-item {
            background:rgba(255,255,255,.08); border-radius:10px;
            padding:8px 10px; font-size:12px; color:rgba(255,255,255,.8);
        }
        .dept-meta-item i { margin-right:5px; color:#00bcd4; }
        .dept-meta-item strong { display:block; font-size:13px; color:#fff; }
        .dept-card-footer {
            display:flex; gap:8px; padding:0 20px 20px;
        }
        .dept-card-footer .btn {
            flex:1; font-size:13px; padding:9px 0; border-radius:30px; font-weight:600;
        }
        /* no-data */
        .no-data-box {
            text-align:center; padding:60px 20px;
            color:rgba(255,255,255,.6);
        }
        .no-data-box i { font-size:3.5rem; margin-bottom:16px; opacity:.4; }
        /* hide cards on search filter */
        .dept-card-wrap.d-none-search { display:none !important; }

        /* ── Doctor cards ─────────────────────────────────────── */
        .doc-card {
            border-radius:20px; overflow:hidden;
            background:rgba(255,255,255,.07);
            backdrop-filter:blur(14px);
            border:1px solid rgba(255,255,255,.15);
            transition:transform .3s, box-shadow .3s;
            display:flex; flex-direction:column; height:100%;
        }
        .doc-card:hover { transform:translateY(-8px); box-shadow:0 20px 50px rgba(0,0,0,.3); }
        .doc-card-top {
            position:relative; padding:24px 20px 0; text-align:center;
        }
        .doc-avatar-wrap {
            display:inline-block; position:relative;
        }
        .doc-avatar {
            width:90px; height:90px; border-radius:50%; object-fit:cover;
            border:3px solid rgba(0,188,212,.7);
            box-shadow:0 0 0 5px rgba(0,188,212,.15);
            background:#1a2035;
        }
        .doc-status-dot {
            position:absolute; bottom:4px; right:4px;
            width:14px; height:14px; border-radius:50%;
            border:2px solid rgba(20,25,40,.9);
        }
        .doc-status-dot.available { background:#4caf50; }
        .doc-status-dot.unavailable { background:#f44336; }
        .doc-card-body { padding:14px 18px; flex:1; display:flex; flex-direction:column; }
        .doc-card-body h5 {
            color:#fff; font-weight:700; font-size:16px; margin-bottom:2px;
        }
        .doc-spec {
            color:#00bcd4; font-size:12px; font-weight:600; margin-bottom:4px;
        }
        .doc-dept {
            color:rgba(255,255,255,.5); font-size:12px; margin-bottom:10px;
        }
        .doc-rating i { font-size:12px; }
        .doc-info-grid {
            display:grid; grid-template-columns:1fr 1fr; gap:6px;
            margin:12px 0;
        }
        .doc-info-item {
            background:rgba(255,255,255,.07); border-radius:8px;
            padding:7px 9px; font-size:11px; color:rgba(255,255,255,.75);
        }
        .doc-info-item i { color:#00bcd4; margin-right:4px; }
        .doc-info-item strong { display:block; font-size:12px; color:#fff; margin-top:1px; }
        .doc-lang {
            font-size:11px; color:rgba(255,255,255,.5);
            margin-bottom:12px;
        }
        .doc-lang i { color:#00bcd4; margin-right:4px; }
        .doc-card-footer {
            display:flex; gap:8px; padding:0 18px 18px; margin-top:auto;
        }
        .doc-card-footer .btn {
            flex:1; font-size:12px; padding:9px 0; border-radius:30px; font-weight:600;
        }

        /* ── Filter tabs (doctors) ────────────────────────────── */
        .filter-tabs {
            display:flex; flex-wrap:wrap; justify-content:center;
            gap:8px; margin-top:22px;
        }
        .filter-tab {
            padding:7px 18px; border-radius:30px; font-size:13px; font-weight:600;
            cursor:pointer; border:1px solid rgba(255,255,255,.2);
            background:rgba(255,255,255,.08); color:rgba(255,255,255,.75);
            transition:.2s;
        }
        .filter-tab:hover, .filter-tab.active {
            background:linear-gradient(45deg,#00bcd4,#2196f3);
            border-color:transparent; color:#fff;
        }

        /* ── View-all buttons ─────────────────────────────────── */
        .btn-view-all {
            padding:13px 38px; border-radius:40px; font-weight:700;
            font-size:14px; letter-spacing:.5px; transition:.3s;
            border:2px solid rgba(255,255,255,.4); color:#fff;
            background:rgba(255,255,255,.06); backdrop-filter:blur(6px);
        }
        .btn-view-all:hover {
            background:linear-gradient(45deg,#00bcd4,#2196f3);
            border-color:transparent; color:#fff;
            transform:translateY(-2px); box-shadow:0 8px 25px rgba(0,188,212,.35);
        }
        .btn-view-all-solid {
            padding:13px 38px; border-radius:40px; font-weight:700;
            font-size:14px; letter-spacing:.5px; transition:.3s;
            background:linear-gradient(45deg,#00bcd4,#2196f3);
            border:none; color:#fff;
        }
        .btn-view-all-solid:hover {
            transform:translateY(-2px); box-shadow:0 8px 25px rgba(0,188,212,.45);
            background:linear-gradient(45deg,#2196f3,#00bcd4); color:#fff;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark header fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-hospital-alt"></i> MediCare Plus
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">
                            <i class="fas fa-user-plus"></i> Register
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="available-rooms.php">Available Rooms</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="departments.php">Departments</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="doctors.php">Doctors</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="expert-doctors.php">Expert Doctors</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="services.php">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="appointment-management.php">Appointments</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="loginDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="login.php?type=admin"><i class="fas fa-user-shield me-2"></i> Admin Login</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="login.php?type=doctor"><i class="fas fa-user-md me-2"></i> Doctor Login</a></li>
                            <li><a class="dropdown-item" href="login.php?type=patient"><i class="fas fa-user me-2"></i> Patient Login</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h1 class="fade-in-up">Hospital Management System</h1>
                    <p class="fade-in-up">Experience world-class medical care with our comprehensive hospital management system</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center text-white mb-5">Hospital Overview</h2>
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="stat-card blue neon-blue">
                        <i class="fas fa-user-md"></i>
                        <h3><?php echo number_format($stats['total_doctors']); ?></h3>
                        <p>Expert Doctors</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="stat-card green neon-green">
                        <i class="fas fa-users"></i>
                        <h3><?php echo number_format($stats['total_patients']); ?></h3>
                        <p>Registered Patients</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="stat-card orange neon-orange">
                        <i class="fas fa-building"></i>
                        <h3><?php echo number_format($stats['total_departments']); ?></h3>
                        <p>Specialized Departments</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="stat-card purple neon-purple">
                        <i class="fas fa-calendar-check"></i>
                        <h3><?php echo number_format($stats['total_appointments']); ?></h3>
                        <p>Upcoming Appointments</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="stat-card red neon-red">
                        <i class="fas fa-bed"></i>
                        <h3><?php echo number_format($stats['available_rooms']); ?></h3>
                        <p>Available Rooms</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="stat-card blue neon-blue">
                        <i class="fas fa-microscope"></i>
                        <h3><?php echo number_format($stats['total_services']); ?></h3>
                        <p>Medical Services</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Departments Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center text-white mb-5">Our Departments</h2>
            <div class="row">
                <?php foreach ($departments as $dept): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card glass-card slide-in-left">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-hospital fa-3x text-primary"></i>
                            </div>
                            <h5 class="card-title"><?php echo htmlspecialchars($dept['name']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($dept['description']); ?></p>
                            <p class="text-muted">
                                <i class="fas fa-user-md"></i> Head: <?php echo htmlspecialchars($dept['head_of_department'] ?? 'Not Assigned'); ?>
                            </p>
                            <a href="department-details.php?id=<?php echo $dept['id']; ?>" class="btn btn-primary">
                                <i class="fas fa-info-circle"></i> Learn More
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-4">
                <a href="departments.php" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-building"></i> View All Departments
                </a>
            </div>
        </div>
    </section>

    <!-- Top Doctors Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center text-white mb-5">Our Top Doctors</h2>
            <div class="row">
                <?php foreach ($top_doctors as $doctor): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="profile-card slide-in-right">
                        <div class="card-body text-center">
                            <img src="<?php echo $doctor['image'] ? 'uploads/doctors/' . $doctor['image'] : 'https://via.placeholder.com/100'; ?>" 
                                 alt="<?php echo htmlspecialchars($doctor['name']); ?>" class="mb-3">
                            <h5 class="card-title"><?php echo htmlspecialchars($doctor['name']); ?></h5>
                            <p class="text-primary"><?php echo htmlspecialchars($doctor['specialization']); ?></p>
                            <p class="text-muted"><?php echo htmlspecialchars($doctor['department_name']); ?></p>
                            <div class="mb-2">
                                <span class="text-warning">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star<?php echo $i <= $doctor['rating'] ? '' : '-o'; ?>"></i>
                                    <?php endfor; ?>
                                </span>
                                <span class="text-muted">(<?php echo $doctor['rating']; ?>)</span>
                            </div>
                            <p class="text-success fw-bold"><?php echo formatCurrency($doctor['consultation_fee']); ?></p>
                            <div class="d-grid gap-2">
                                <a href="doctor-profile.php?id=<?php echo $doctor['id']; ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-user"></i> View Profile
                                </a>
                                <a href="book-appointment.php?doctor_id=<?php echo $doctor['id']; ?>" class="btn btn-success btn-sm">
                                    <i class="fas fa-calendar-plus"></i> Book Appointment
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-4">
                <a href="doctors.php" class="btn btn-outline-light btn-lg me-3">
                    <i class="fas fa-user-md"></i> View All Doctors
                </a>
                <a href="expert-doctors.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-star"></i> Expert Doctors
                </a>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center text-white mb-5">Our Services</h2>
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <a href="emergency-care.php" class="text-decoration-none">
                        <div class="card glass text-center service-hover">
                            <div class="card-body">
                                <i class="fas fa-heartbeat fa-3x text-danger mb-3"></i>
                                <h5 class="text-white">Emergency Care</h5>
                                <p class="text-white">24/7 emergency medical services</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <a href="diagnostic-services.php" class="text-decoration-none">
                        <div class="card glass text-center service-hover">
                            <div class="card-body">
                                <i class="fas fa-x-ray fa-3x text-info mb-3"></i>
                                <h5 class="text-white">Diagnostic Services</h5>
                                <p class="text-white">Advanced imaging and lab tests</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <a href="surgery-management.php" class="text-decoration-none">
                        <div class="card glass text-center service-hover">
                            <div class="card-body">
                                <i class="fas fa-procedures fa-3x text-success mb-3"></i>
                                <h5 class="text-white">Surgery</h5>
                                <p class="text-white">State-of-the-art surgical procedures</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <a href="inpatient-care.php" class="text-decoration-none">
                        <div class="card glass text-center service-hover">
                            <div class="card-body">
                                <i class="fas fa-bed fa-3x text-warning mb-3"></i>
                                <h5 class="text-white">Inpatient Care</h5>
                                <p class="text-white">Comfortable rooms and nursing care</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Access Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center text-white mb-5">Quick Access</h2>
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card glass">
                        <div class="card-body">
                            <div class="row text-center justify-content-center">
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <a href="login.php?type=admin" class="btn btn-primary btn-lg w-100 h-100 d-flex flex-column justify-content-center">
                                        <i class="fas fa-user-shield fa-2x mb-2"></i>
                                        <span>Admin Panel</span>
                                        <small class="opacity-75">Hospital Management</small>
                                    </a>
                                </div>
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <a href="login.php?type=doctor" class="btn btn-success btn-lg w-100 h-100 d-flex flex-column justify-content-center">
                                        <i class="fas fa-user-md fa-2x mb-2"></i>
                                        <span>Doctor Portal</span>
                                        <small class="opacity-75">Manage Appointments</small>
                                    </a>
                                </div>
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <a href="login.php?type=patient" class="btn btn-warning btn-lg w-100 h-100 d-flex flex-column justify-content-center">
                                        <i class="fas fa-user fa-2x mb-2"></i>
                                        <span>Patient Portal</span>
                                        <small class="opacity-75">Your Health Dashboard</small>
                                    </a>
                                </div>
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <a href="appointment-management.php" class="btn btn-info btn-lg w-100 h-100 d-flex flex-column justify-content-center">
                                        <i class="fas fa-calendar-alt fa-2x mb-2"></i>
                                        <span>Appointments</span>
                                        <small class="opacity-75">Manage Bookings</small>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5><i class="fas fa-hospital-alt"></i> MediCare Plus</h5>
                    <p>Advanced Hospital Management System providing comprehensive healthcare solutions with modern technology and expert medical care.</p>
                    <div class="social-links">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-instagram fa-lg"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-linkedin fa-lg"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6>Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="departments.php" class="text-white-50">Departments</a></li>
                        <li><a href="doctors.php" class="text-white-50">Doctors</a></li>
                        <li><a href="services.php" class="text-white-50">Services</a></li>
                        <li><a href="rooms.php" class="text-white-50">Rooms</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6>Patient Care</h6>
                    <ul class="list-unstyled">
                        <li><a href="register.php" class="text-white-50">Register</a></li>
                        <li><a href="login.php?type=patient" class="text-white-50">Patient Login</a></li>
                        <li><a href="book-appointment.php" class="text-white-50">Book Appointment</a></li>
                        <li><a href="emergency-services.php" class="text-white-50">Emergency</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 mb-4">
                    <h6>Contact Information</h6>
                    <p class="text-white-50">
                        <i class="fas fa-map-marker-alt"></i> 123 Healthcare Street, Medical City<br>
                        <i class="fas fa-phone"></i> +91 98765 43210<br>
                        <i class="fas fa-envelope"></i> info@medicareplus.com<br>
                        <i class="fas fa-clock"></i> 24/7 Emergency Services
                    </p>
                </div>
            </div>
            <hr class="my-4">
            <div class="row">
                <div class="col-md-6">
                    <p>&copy; 2024 MediCare Plus Hospital Management System. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>Designed for Final Year Project Submission</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/main.js"></script>
</body>
</html>