<?php
session_start();
require_once __DIR__ . '/config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$pdo = getConnection();

// Ensure extra columns exist
foreach (['phone VARCHAR(15)', 'image VARCHAR(255)'] as $col) {
    try { $pdo->exec("ALTER TABLE admin ADD COLUMN $col"); } catch (PDOException $e) {}
}

$profile_success = '';
$profile_error   = '';

// ── Handle photo upload ───────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_photo'])) {
    if (isset($_FILES['admin_photo']) && $_FILES['admin_photo']['error'] === UPLOAD_ERR_OK) {
        $file     = $_FILES['admin_photo'];
        $allowed  = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize  = 2 * 1024 * 1024; // 2 MB
        $finfo    = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowed)) {
            $profile_error = 'Only JPG, PNG, GIF, or WEBP images are allowed.';
        } elseif ($file['size'] > $maxSize) {
            $profile_error = 'Image must be smaller than 2 MB.';
        } else {
            $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'admin_' . $_SESSION['admin_id'] . '_' . time() . '.' . strtolower($ext);
            $dest     = __DIR__ . '/uploads/admins/' . $filename;

            if (move_uploaded_file($file['tmp_name'], $dest)) {
                // Delete old photo if exists
                $old = $pdo->prepare("SELECT image FROM admin WHERE id = ?");
                $old->execute([$_SESSION['admin_id']]);
                $oldImg = $old->fetchColumn();
                if ($oldImg && file_exists(__DIR__ . '/uploads/admins/' . $oldImg)) {
                    @unlink(__DIR__ . '/uploads/admins/' . $oldImg);
                }
                $pdo->prepare("UPDATE admin SET image = ? WHERE id = ?")
                    ->execute([$filename, $_SESSION['admin_id']]);
                $profile_success = 'Profile photo updated successfully.';
            } else {
                $profile_error = 'Upload failed. Please try again.';
            }
        }
    } else {
        $profile_error = 'Please select an image file to upload.';
    }
}

// ── Handle photo removal ──────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_photo'])) {
    $old = $pdo->prepare("SELECT image FROM admin WHERE id = ?");
    $old->execute([$_SESSION['admin_id']]);
    $oldImg = $old->fetchColumn();
    if ($oldImg && file_exists(__DIR__ . '/uploads/admins/' . $oldImg)) {
        @unlink(__DIR__ . '/uploads/admins/' . $oldImg);
    }
    $pdo->prepare("UPDATE admin SET image = NULL WHERE id = ?")
        ->execute([$_SESSION['admin_id']]);
    $profile_success = 'Profile photo removed.';
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $upd_name  = htmlspecialchars(strip_tags(trim($_POST['full_name'] ?? '')));
    $upd_email = htmlspecialchars(strip_tags(trim($_POST['email']     ?? '')));
    $upd_phone = trim($_POST['phone'] ?? '');

    if (empty($upd_name) || empty($upd_email) || empty($upd_phone)) {
        $profile_error = 'All fields are required.';
    } elseif (!filter_var($upd_email, FILTER_VALIDATE_EMAIL)) {
        $profile_error = 'Please enter a valid email address.';
    } elseif (!preg_match('/^[0-9]{10}$/', $upd_phone)) {
        $profile_error = 'Phone number must be exactly 10 digits.';
    } else {
        // Check email not taken by another admin
        $chk = $pdo->prepare("SELECT id FROM admin WHERE email = ? AND id != ?");
        $chk->execute([$upd_email, $_SESSION['admin_id']]);
        if ($chk->fetch()) {
            $profile_error = 'That email is already used by another account.';
        } else {
            $pdo->prepare("UPDATE admin SET full_name = ?, email = ?, phone = ? WHERE id = ?")
                ->execute([$upd_name, $upd_email, $upd_phone, $_SESSION['admin_id']]);
            $_SESSION['admin_name'] = $upd_name;
            $profile_success = 'Profile updated successfully.';
        }
    }
}

$pdo = getConnection();
$admin = $pdo->prepare("SELECT * FROM admin WHERE id = ?");
$admin->execute([$_SESSION['admin_id']]);
$admin_data = $admin->fetch();

// Get departments
$departments = $pdo->query("SELECT * FROM departments ORDER BY name")->fetchAll();

// Get new patients (registered today)
$new_patients = $pdo->query("SELECT * FROM patients WHERE DATE(created_at) = CURDATE() ORDER BY created_at DESC")->fetchAll();

// Create complaints table if not exists and get recent complaints
$pdo->exec("CREATE TABLE IF NOT EXISTS complaints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    complaint_id VARCHAR(20),
    patient_name VARCHAR(100),
    patient_id VARCHAR(20),
    phone VARCHAR(15),
    complaint_type VARCHAR(50),
    complaint_text TEXT,
    status ENUM('Pending', 'In Review', 'Resolved') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
$recent_complaints = $pdo->query("SELECT * FROM complaints ORDER BY created_at DESC LIMIT 10")->fetchAll();

// Get statistics
$stats = [
    'doctors' => $pdo->query("SELECT COUNT(*) FROM doctors WHERE status = 'active'")->fetchColumn(),
    'patients' => $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn(),
    'appointments' => $pdo->query("SELECT COUNT(*) FROM appointments WHERE appointment_date = CURDATE()")->fetchColumn(),
    'rooms' => $pdo->query("SELECT COUNT(*) FROM rooms WHERE status = 'available'")->fetchColumn()
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediCare Plus - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <style>
        .admin-card { transition: all 0.3s ease; border-radius: 15px; }
        .admin-card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0,0,0,0.2); }
        .stat-number { font-size: 2.5rem; font-weight: bold; }

        /* ── Header avatar ── */
        .nav-avatar-wrap { position: relative; cursor: pointer; }
        .nav-avatar {
            width: 38px; height: 38px; border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(0,188,212,0.8);
            box-shadow: 0 0 0 3px rgba(0,188,212,0.25);
            transition: box-shadow .25s;
        }
        .nav-avatar-default {
            width: 38px; height: 38px; border-radius: 50%;
            background: linear-gradient(135deg,#00bcd4,#2196f3);
            border: 2px solid rgba(0,188,212,0.8);
            box-shadow: 0 0 0 3px rgba(0,188,212,0.25);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 16px;
            transition: box-shadow .25s;
        }
        .nav-avatar:hover, .nav-avatar-default:hover {
            box-shadow: 0 0 0 4px rgba(0,188,212,0.55);
        }
        /* upload badge on avatar */
        .nav-avatar-wrap .upload-badge {
            position: absolute; bottom: -2px; right: -2px;
            width: 16px; height: 16px; border-radius: 50%;
            background: #00bcd4; border: 2px solid transparent;
            display: flex; align-items: center; justify-content: center;
            font-size: 8px; color: #fff; pointer-events: none;
        }
        /* dropdown panel */
        .avatar-dropdown {
            display: none; position: absolute; right: 0; top: calc(100% + 10px);
            width: 230px;
            background: rgba(20,25,40,0.97);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(0,188,212,0.25);
            border-radius: 14px;
            box-shadow: 0 12px 40px rgba(0,0,0,0.45);
            z-index: 9999; overflow: hidden;
            animation: dropIn .18s ease;
        }
        .avatar-dropdown.open { display: block; }
        @keyframes dropIn {
            from { opacity:0; transform:translateY(-8px); }
            to   { opacity:1; transform:translateY(0); }
        }
        .avatar-dropdown .dd-header {
            padding: 16px;
            background: linear-gradient(135deg,rgba(0,188,212,.15),rgba(33,150,243,.1));
            border-bottom: 1px solid rgba(255,255,255,.08);
            text-align: center;
        }
        .avatar-dropdown .dd-header img,
        .avatar-dropdown .dd-header .dd-avatar-default {
            width: 64px; height: 64px; border-radius: 50%;
            object-fit: cover;
            border: 2px solid #00bcd4;
            margin-bottom: 8px;
        }
        .avatar-dropdown .dd-header .dd-avatar-default {
            background: linear-gradient(135deg,#00bcd4,#2196f3);
            display: flex; align-items: center; justify-content: center;
            font-size: 26px; color: #fff; margin: 0 auto 8px;
        }
        .avatar-dropdown .dd-header p { margin:0; color:#fff; font-size:13px; font-weight:600; }
        .avatar-dropdown .dd-header small { color:rgba(255,255,255,.5); font-size:11px; }
        .avatar-dropdown .dd-actions { padding: 10px 12px; }
        .avatar-dropdown .dd-btn {
            display: flex; align-items: center; gap: 10px;
            width: 100%; padding: 9px 12px; border-radius: 8px;
            border: none; background: transparent;
            color: rgba(255,255,255,.8); font-size: 13px;
            cursor: pointer; transition: background .2s;
            text-align: left;
        }
        .avatar-dropdown .dd-btn:hover { background: rgba(255,255,255,.08); color:#fff; }
        .avatar-dropdown .dd-btn.danger:hover { background: rgba(244,67,54,.15); color:#f44336; }
        .avatar-dropdown .dd-btn i { width: 16px; text-align: center; }
        .avatar-dropdown .dd-divider { height:1px; background:rgba(255,255,255,.07); margin:4px 0; }

        /* ── Profile card avatar ── */
        .profile-photo-wrap { position: relative; display: inline-block; margin-bottom: 12px; }
        .profile-photo-wrap img,
        .profile-photo-wrap .profile-avatar-default {
            width: 80px; height: 80px; border-radius: 50%;
            object-fit: cover;
            border: 3px solid #00bcd4;
            box-shadow: 0 0 20px rgba(0,188,212,.35);
        }
        .profile-photo-wrap .profile-avatar-default {
            background: linear-gradient(135deg,#00bcd4,#2196f3);
            display: flex; align-items: center; justify-content: center;
            font-size: 32px; color: #fff;
        }
        .profile-photo-wrap .photo-edit-btn {
            position: absolute; bottom: 0; right: 0;
            width: 26px; height: 26px; border-radius: 50%;
            background: #00bcd4; border: 2px solid rgba(20,25,40,.9);
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; font-size: 11px; color: #fff;
            transition: background .2s;
        }
        .profile-photo-wrap .photo-edit-btn:hover { background: #2196f3; }
    </style>
</head>
<body>
    <!-- Hidden file input (shared by header + profile card) -->
    <input type="file" id="photoFileInput" name="admin_photo" accept="image/jpeg,image/png,image/gif,image/webp" style="display:none;">

    <!-- Fixed Header -->
    <nav class="navbar navbar-expand-lg navbar-dark header fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-hospital-alt"></i> MediCare Plus Admin
            </a>
            <div class="navbar-nav ms-auto align-items-center gap-3">

                <!-- Admin name -->
                <span class="navbar-text text-white d-none d-md-inline">
                    <?php echo htmlspecialchars($admin_data['full_name'] ?? 'Admin'); ?>
                </span>

                <!-- Avatar + dropdown -->
                <div class="nav-avatar-wrap position-relative" id="avatarWrap">
                    <?php if (!empty($admin_data['image'])): ?>
                        <img src="uploads/admins/<?php echo htmlspecialchars($admin_data['image']); ?>"
                             class="nav-avatar" id="navAvatarImg" alt="Profile">
                    <?php else: ?>
                        <div class="nav-avatar-default" id="navAvatarDefault">
                            <i class="fas fa-user-shield"></i>
                        </div>
                    <?php endif; ?>
                    <span class="upload-badge"><i class="fas fa-camera"></i></span>

                    <!-- Dropdown panel -->
                    <div class="avatar-dropdown" id="avatarDropdown">
                        <div class="dd-header">
                            <?php if (!empty($admin_data['image'])): ?>
                                <img src="uploads/admins/<?php echo htmlspecialchars($admin_data['image']); ?>" alt="Profile" id="ddAvatarImg">
                            <?php else: ?>
                                <div class="dd-avatar-default" id="ddAvatarDefault"><i class="fas fa-user-shield"></i></div>
                            <?php endif; ?>
                            <p><?php echo htmlspecialchars($admin_data['full_name'] ?? 'Admin'); ?></p>
                            <small><?php echo htmlspecialchars($admin_data['email'] ?? ''); ?></small>
                        </div>
                        <div class="dd-actions">
                            <button class="dd-btn" onclick="triggerPhotoUpload()">
                                <i class="fas fa-camera text-info"></i>
                                <?php echo !empty($admin_data['image']) ? 'Change Photo' : 'Upload Photo'; ?>
                            </button>
                            <?php if (!empty($admin_data['image'])): ?>
                            <div class="dd-divider"></div>
                            <form method="POST" style="margin:0;">
                                <input type="hidden" name="remove_photo" value="1">
                                <button type="submit" class="dd-btn danger">
                                    <i class="fas fa-trash-alt text-danger"></i> Remove Photo
                                </button>
                            </form>
                            <?php endif; ?>
                            <div class="dd-divider"></div>
                            <a href="logout.php" class="dd-btn">
                                <i class="fas fa-sign-out-alt text-warning"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Logout button (visible on larger screens) -->
                <a href="logout.php" class="btn btn-outline-light btn-sm d-none d-lg-inline-flex align-items-center gap-1">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid" style="margin-top: 80px; padding: 20px;">
        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="glass p-4 text-center">
                    <h1 class="text-white mb-2">
                        <i class="fas fa-crown text-warning"></i> Admin Control Center
                    </h1>
                    <p class="text-white-50">Complete Hospital Management & Administration</p>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card blue admin-card">
                    <i class="fas fa-user-md"></i>
                    <h3 class="stat-number"><?php echo $stats['doctors']; ?></h3>
                    <p>Active Doctors</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card green admin-card">
                    <i class="fas fa-users"></i>
                    <h3 class="stat-number"><?php echo $stats['patients']; ?></h3>
                    <p>Registered Patients</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card orange admin-card">
                    <i class="fas fa-calendar-check"></i>
                    <h3 class="stat-number"><?php echo $stats['appointments']; ?></h3>
                    <p>Today's Appointments</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card red admin-card">
                    <i class="fas fa-bed"></i>
                    <h3 class="stat-number"><?php echo $stats['rooms']; ?></h3>
                    <p>Available Rooms</p>
                </div>
            </div>
        </div>

        <!-- Admin Profile & New Patients Row -->
        <div class="row mb-4">
            <!-- Admin Profile -->
            <div class="col-lg-4 mb-4">
                <div class="glass p-4">
                    <h4 class="text-white mb-3"><i class="fas fa-user-circle text-info"></i> Admin Profile</h4>

                    <?php if ($profile_success): ?>
                        <div class="alert alert-success py-2" style="font-size:13px;">
                            <i class="fas fa-check-circle me-1"></i><?php echo $profile_success; ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($profile_error): ?>
                        <div class="alert alert-danger py-2" style="font-size:13px;">
                            <i class="fas fa-exclamation-circle me-1"></i><?php echo htmlspecialchars($profile_error); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Profile Display -->
                    <div id="profileView">
                        <div class="text-center mb-3">
                            <div class="profile-photo-wrap">
                                <?php if (!empty($admin_data['image'])): ?>
                                    <img src="uploads/admins/<?php echo htmlspecialchars($admin_data['image']); ?>"
                                         alt="Profile" id="profileCardImg">
                                <?php else: ?>
                                    <div class="profile-avatar-default" id="profileCardDefault">
                                        <i class="fas fa-user-shield"></i>
                                    </div>
                                <?php endif; ?>
                                <span class="photo-edit-btn" onclick="triggerPhotoUpload()" title="Change photo">
                                    <i class="fas fa-camera"></i>
                                </span>
                            </div>
                        </div>
                        <table class="table table-borderless mb-3">
                            <tr>
                                <td class="text-info" style="width:40%;font-size:13px;"><strong><i class="fas fa-user me-1"></i>Name</strong></td>
                                <td class="text-warning" style="font-size:13px;"><?php echo htmlspecialchars($admin_data['full_name'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <td class="text-info" style="font-size:13px;"><strong><i class="fas fa-envelope me-1"></i>Email</strong></td>
                                <td class="text-success" style="font-size:13px;word-break:break-all;"><?php echo htmlspecialchars($admin_data['email'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <td class="text-info" style="font-size:13px;"><strong><i class="fas fa-phone me-1"></i>Phone</strong></td>
                                <td class="text-primary" style="font-size:13px;">
                                    <?php echo !empty($admin_data['phone']) ? htmlspecialchars($admin_data['phone']) : '<span style="color:rgba(255,255,255,0.35);font-style:italic;">Not set</span>'; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-info" style="font-size:13px;"><strong><i class="fas fa-id-badge me-1"></i>Admin ID</strong></td>
                                <td class="text-danger" style="font-size:13px;"><?php echo htmlspecialchars($admin_data['username'] ?? 'N/A'); ?></td>
                            </tr>
                        </table>
                        <button class="btn btn-outline-info btn-sm w-100" onclick="toggleEdit(true)">
                            <i class="fas fa-edit me-1"></i> Edit Profile
                        </button>
                    </div>

                    <!-- Edit Form -->
                    <div id="profileEdit" style="display:none;">
                        <form method="POST" id="profileForm" novalidate>
                            <input type="hidden" name="update_profile" value="1">

                            <div class="mb-2">
                                <label class="form-label" style="font-size:12px;color:rgba(255,255,255,0.8);"><i class="fas fa-user me-1"></i>Full Name</label>
                                <input type="text" class="form-control form-control-sm" name="full_name" id="pf_name"
                                       value="<?php echo htmlspecialchars($admin_data['full_name'] ?? ''); ?>">
                                <div id="pf_err_name" style="color:#f44336;font-size:11px;display:none;"></div>
                            </div>

                            <div class="mb-2">
                                <label class="form-label" style="font-size:12px;color:rgba(255,255,255,0.8);"><i class="fas fa-envelope me-1"></i>Email Address</label>
                                <input type="email" class="form-control form-control-sm" name="email" id="pf_email"
                                       value="<?php echo htmlspecialchars($admin_data['email'] ?? ''); ?>">
                                <div id="pf_err_email" style="color:#f44336;font-size:11px;display:none;"></div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" style="font-size:12px;color:rgba(255,255,255,0.8);"><i class="fas fa-phone me-1"></i>Phone Number</label>
                                <input type="tel" class="form-control form-control-sm" name="phone" id="pf_phone"
                                       placeholder="10-digit number" maxlength="10" inputmode="numeric"
                                       value="<?php echo htmlspecialchars($admin_data['phone'] ?? ''); ?>">
                                <div id="pf_err_phone" style="color:#f44336;font-size:11px;display:none;"></div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success btn-sm flex-fill" id="pfSaveBtn">
                                    <i class="fas fa-save me-1"></i>Save
                                </button>
                                <button type="button" class="btn btn-outline-light btn-sm flex-fill" onclick="toggleEdit(false)">
                                    <i class="fas fa-times me-1"></i>Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- New Patients Alert -->
            <div class="col-lg-4 mb-4">
                <div class="glass p-4">
                    <h4 class="text-white mb-3">
                        <i class="fas fa-bell text-warning"></i> New Patients Today 
                        <?php if (count($new_patients) > 0): ?>
                            <span class="badge bg-danger"><?php echo count($new_patients); ?></span>
                        <?php endif; ?>
                    </h4>
                    <div style="max-height: 200px; overflow-y: auto;">
                        <?php if (count($new_patients) > 0): ?>
                            <?php foreach ($new_patients as $patient): ?>
                            <div class="alert alert-info mb-2 p-2">
                                <strong><?php echo htmlspecialchars($patient['name']); ?></strong><br>
                                <small>ID: <?php echo htmlspecialchars($patient['pat_id']); ?> | <?php echo date('H:i', strtotime($patient['created_at'])); ?></small>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-white-50">No new patients registered today.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Patient Complaints -->
            <div class="col-lg-4 mb-4">
                <div class="glass p-4">
                    <h4 class="text-white mb-3">
                        <i class="fas fa-exclamation-circle text-danger"></i> Recent Complaints 
                        <?php if ($recent_complaints && count($recent_complaints) > 0): ?>
                            <span class="badge bg-warning"><?php echo count($recent_complaints); ?></span>
                        <?php endif; ?>
                    </h4>
                    <div style="max-height: 200px; overflow-y: auto;">
                        <?php if (count($recent_complaints) > 0): ?>
                            <?php foreach ($recent_complaints as $complaint): ?>
                            <div class="alert alert-warning mb-2 p-2">
                                <strong><?php echo htmlspecialchars($complaint['patient_name']); ?></strong><br>
                                <small class="text-muted"><?php echo htmlspecialchars($complaint['complaint_type']); ?></small><br>
                                <small>ID: <?php echo htmlspecialchars($complaint['complaint_id']); ?> | <?php echo date('M d, H:i', strtotime($complaint['created_at'])); ?></small>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-white-50">No complaints submitted.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Departments List -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="glass p-4">
                    <h4 class="text-white mb-3"><i class="fas fa-building text-success"></i> Hospital Departments</h4>
                    <div class="row">
                        <?php if (count($departments) > 0): ?>
                            <?php foreach ($departments as $dept): ?>
                            <div class="col-lg-4 col-md-6 mb-3">
                                <div class="card bg-dark bg-opacity-50 border-light">
                                    <div class="card-body">
                                        <h6 class="card-title text-white">
                                            <i class="fas fa-hospital text-primary"></i> <?php echo htmlspecialchars($dept['name']); ?>
                                        </h6>
                                        <p class="card-text text-white-50 small"><?php echo htmlspecialchars($dept['description'] ?? 'No description'); ?></p>
                                        <span class="badge <?php echo $dept['status'] === 'active' ? 'bg-success' : 'bg-secondary'; ?>">
                                            <?php echo ucfirst($dept['status']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12">
                                <p class="text-white-50 text-center">No departments found. <a href="add-department.php" class="text-info">Add Department</a></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Management Sections -->
        <div class="row">
            <!-- Staff Management -->
            <div class="col-lg-4 mb-4">
                <div class="glass p-4">
                    <h4 class="text-white mb-3"><i class="fas fa-users-cog text-primary"></i> Staff Management</h4>
                    <div class="d-grid gap-2">
                        <a href="add-doctor.php" class="btn btn-primary">
                            <i class="fas fa-user-md"></i> Add Doctor
                        </a>
                        <a href="doctor-management.php" class="btn btn-outline-primary">
                            <i class="fas fa-list"></i> Manage Doctors
                        </a>
                        <a href="view-patients.php" class="btn btn-info">
                            <i class="fas fa-users"></i> View Patients
                        </a>
                    </div>
                </div>
            </div>

            <!-- Hospital Operations -->
            <div class="col-lg-4 mb-4">
                <div class="glass p-4">
                    <h4 class="text-white mb-3"><i class="fas fa-hospital text-success"></i> Hospital Operations</h4>
                    <div class="d-grid gap-2">
                        <a href="departments.php" class="btn btn-success">
                            <i class="fas fa-building"></i> Departments
                        </a>
                        <a href="rooms.php" class="btn btn-warning">
                            <i class="fas fa-bed"></i> Room Management
                        </a>
                        <a href="room-booking.php" class="btn btn-success">
                            <i class="fas fa-calendar-plus"></i> Book Room
                        </a>
                        <a href="emergency-services.php" class="btn btn-danger">
                            <i class="fas fa-ambulance"></i> Emergency Services
                        </a>
                    </div>
                </div>
            </div>

            <!-- Appointments & Services -->
            <div class="col-lg-4 mb-4">
                <div class="glass p-4">
                    <h4 class="text-white mb-3"><i class="fas fa-calendar-alt text-warning"></i> Appointments & Services</h4>
                    <div class="d-grid gap-2">
                        <a href="upcoming-appointments.php" class="btn btn-warning">
                            <i class="fas fa-calendar-check"></i> Appointments
                        </a>
                        <a href="diagnostic-services.php" class="btn btn-info">
                            <i class="fas fa-x-ray"></i> Diagnostic Services
                        </a>
                        <a href="services.php" class="btn btn-outline-info">
                            <i class="fas fa-stethoscope"></i> Medical Services
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-12">
                <div class="glass p-4">
                    <h4 class="text-white mb-3"><i class="fas fa-bolt text-warning"></i> Quick Actions</h4>
                    <div class="row">
                        <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                            <a href="add-department.php" class="btn btn-outline-light w-100">
                                <i class="fas fa-plus"></i> Add Department
                            </a>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                            <a href="add-room.php" class="btn btn-outline-light w-100">
                                <i class="fas fa-plus"></i> Add Room
                            </a>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                            <a href="setup-services.php" class="btn btn-outline-light w-100">
                                <i class="fas fa-cog"></i> Setup Services
                            </a>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                            <a href="total-patients.php" class="btn btn-outline-light w-100">
                                <i class="fas fa-chart-bar"></i> Reports
                            </a>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                            <a href="emergency-management.php" class="btn btn-outline-light w-100">
                                <i class="fas fa-exclamation-triangle"></i> Emergency
                            </a>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                            <a href="patient-complaints.php" class="btn btn-outline-light w-100">
                                <i class="fas fa-comment-medical"></i> Complaints
                            </a>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                            <a href="room-booking.php" class="btn btn-outline-light w-100">
                                <i class="fas fa-calendar-plus"></i> Book Room
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden upload form (submitted programmatically) -->
    <form method="POST" enctype="multipart/form-data" id="photoUploadForm" style="display:none;">
        <input type="hidden" name="upload_photo" value="1">
        <input type="file" name="admin_photo" id="photoFormFile" accept="image/jpeg,image/png,image/gif,image/webp">
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // ── Avatar dropdown toggle ────────────────────────────────────────────────
    const avatarWrap     = document.getElementById('avatarWrap');
    const avatarDropdown = document.getElementById('avatarDropdown');
    if (avatarWrap) {
        avatarWrap.addEventListener('click', function(e) {
            e.stopPropagation();
            avatarDropdown.classList.toggle('open');
        });
        document.addEventListener('click', () => avatarDropdown.classList.remove('open'));
        avatarDropdown.addEventListener('click', e => e.stopPropagation());
    }

    // ── Photo upload ─────────────────────────────────────────────────────────
    function triggerPhotoUpload() {
        avatarDropdown && avatarDropdown.classList.remove('open');
        document.getElementById('photoFormFile').click();
    }

    const photoFormFile = document.getElementById('photoFormFile');
    if (photoFormFile) {
        photoFormFile.addEventListener('change', function() {
            if (!this.files || !this.files[0]) return;
            const file = this.files[0];
            const allowed = ['image/jpeg','image/png','image/gif','image/webp'];
            if (!allowed.includes(file.type)) {
                alert('Only JPG, PNG, GIF, or WEBP images are allowed.');
                this.value = ''; return;
            }
            if (file.size > 2 * 1024 * 1024) {
                alert('Image must be smaller than 2 MB.');
                this.value = ''; return;
            }
            // Live preview before upload
            const reader = new FileReader();
            reader.onload = function(e) {
                const src = e.target.result;
                // Update nav avatar
                const navImg = document.getElementById('navAvatarImg');
                const navDef = document.getElementById('navAvatarDefault');
                if (navImg)  { navImg.src = src; }
                else if (navDef) {
                    const img = document.createElement('img');
                    img.src = src; img.className = 'nav-avatar'; img.id = 'navAvatarImg';
                    navDef.replaceWith(img);
                }
                // Update dropdown avatar
                const ddImg = document.getElementById('ddAvatarImg');
                const ddDef = document.getElementById('ddAvatarDefault');
                if (ddImg)  { ddImg.src = src; }
                else if (ddDef) {
                    const img = document.createElement('img');
                    img.src = src; img.id = 'ddAvatarImg';
                    img.style.cssText = 'width:64px;height:64px;border-radius:50%;object-fit:cover;border:2px solid #00bcd4;margin-bottom:8px;';
                    ddDef.replaceWith(img);
                }
                // Update profile card avatar
                const cardImg = document.getElementById('profileCardImg');
                const cardDef = document.getElementById('profileCardDefault');
                if (cardImg)  { cardImg.src = src; }
                else if (cardDef) {
                    const img = document.createElement('img');
                    img.src = src; img.id = 'profileCardImg';
                    img.style.cssText = 'width:80px;height:80px;border-radius:50%;object-fit:cover;border:3px solid #00bcd4;box-shadow:0 0 20px rgba(0,188,212,.35);';
                    cardDef.replaceWith(img);
                }
            };
            reader.readAsDataURL(file);
            // Submit the hidden form
            document.getElementById('photoUploadForm').submit();
        });
    }

    function toggleEdit(show) {
        document.getElementById('profileView').style.display = show ? 'none'  : 'block';
        document.getElementById('profileEdit').style.display = show ? 'block' : 'none';
    }

    // Auto-open edit form if there was a server-side profile error
    <?php if ($profile_error): ?>toggleEdit(true);<?php endif; ?>

    // Numeric-only phone
    const pfPhone = document.getElementById('pf_phone');
    if (pfPhone) {
        pfPhone.addEventListener('input', () => {
            pfPhone.value = pfPhone.value.replace(/[^0-9]/g, '').slice(0, 10);
        });
    }

    // Profile form client-side validation
    const profileForm = document.getElementById('profileForm');
    const pfSaveBtn   = document.getElementById('pfSaveBtn');
    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            let ok = true;
            const rules = [
                { id: 'pf_name',  err: 'pf_err_name',  check: v => v.trim().length >= 2 ? '' : 'Name must be at least 2 characters.' },
                { id: 'pf_email', err: 'pf_err_email', check: v => /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v.trim()) ? '' : 'Enter a valid email address.' },
                { id: 'pf_phone', err: 'pf_err_phone', check: v => /^[0-9]{10}$/.test(v.trim()) ? '' : 'Phone must be exactly 10 digits.' },
            ];
            rules.forEach(r => {
                const el  = document.getElementById(r.id);
                const err = document.getElementById(r.err);
                if (!el || !err) return;
                const msg = r.check(el.value);
                if (msg) {
                    err.textContent = msg; err.style.display = 'block';
                    el.style.borderColor = '#f44336';
                    ok = false;
                } else {
                    err.style.display = 'none';
                    el.style.borderColor = '';
                }
            });
            if (!ok) { e.preventDefault(); return; }
            if (pfSaveBtn) {
                pfSaveBtn.disabled = true;
                pfSaveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving…';
            }
        });

        // Clear errors on input
        ['pf_name','pf_email','pf_phone'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('input', () => {
                const err = document.getElementById(id.replace('pf_','pf_err_'));
                if (err) err.style.display = 'none';
                el.style.borderColor = '';
            });
        });
    }
    </script>
</body>
</html>