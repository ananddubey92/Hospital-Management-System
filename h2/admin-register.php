<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$error   = '';
$success = false;
$old     = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name        = sanitize($_POST['full_name']        ?? '');
    $email            = sanitize($_POST['email']            ?? '');
    $phone            = sanitize($_POST['phone']            ?? '');
    $password         = $_POST['password']                  ?? '';
    $confirm_password = $_POST['confirm_password']          ?? '';

    $old = ['full_name' => $full_name, 'email' => $email, 'phone' => $phone];

    if (empty($full_name) || empty($email) || empty($phone) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all fields.';
    } elseif (strlen(trim($full_name)) < 2) {
        $error = 'Full name must be at least 2 characters.';
    } elseif (!validateEmail($email)) {
        $error = 'Please enter a valid email address.';
    } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
        $error = 'Phone number must be exactly 10 digits.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        $pdo = getConnection();

        // Ensure optional columns exist (non-fatal)
        $optional_cols = [
            'reset_code VARCHAR(8)',
            'reset_expires DATETIME',
            'phone VARCHAR(15)'
        ];
        foreach ($optional_cols as $col) {
            try { $pdo->exec("ALTER TABLE admin ADD COLUMN $col"); } catch (PDOException $e) {}
        }

        // Derive a unique username from the email local-part
        $base_username = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', explode('@', $email)[0]));
        if (empty($base_username)) {
            $base_username = 'admin';
        }
        // Make it unique: append a number if already taken
        $username   = $base_username;
        $suffix     = 1;
        $stmt_check = $pdo->prepare("SELECT id FROM admin WHERE username = ?");
        while (true) {
            $stmt_check->execute([$username]);
            if (!$stmt_check->fetch()) break;
            $username = $base_username . $suffix++;
        }

        // Check duplicate email
        $stmt = $pdo->prepare("SELECT id FROM admin WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'An account with this email already exists.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt   = $pdo->prepare(
                "INSERT INTO admin (username, full_name, email, phone, password) VALUES (?, ?, ?, ?, ?)"
            );
            if ($stmt->execute([$username, $full_name, $email, $phone, $hashed])) {
                $success = true;
            } else {
                $error = 'Registration failed. Please try again.';
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
    <title>Admin Registration - Hospital Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <style>
        .strength-bar-wrap { height: 5px; border-radius: 3px; background: rgba(255,255,255,0.1); overflow: hidden; margin-top: 6px; }
        .strength-bar      { height: 100%; width: 0; border-radius: 3px; transition: width .3s, background .3s; }
        .field-icon        { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: rgba(255,255,255,0.5); z-index: 5; }
        .field-icon:hover  { color: #00bcd4; }
        .input-wrap        { position: relative; }
        .input-wrap .form-control { padding-right: 40px; }
    </style>
</head>
<body>
<div class="auth-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="auth-card glass">

                    <h2><i class="fas fa-user-shield"></i> Admin Registration</h2>
                    <p class="text-center mb-4" style="color:rgba(255,255,255,0.75);font-size:14px;">
                        Create your admin account to manage hospital operations.
                    </p>

                    <?php if ($success): ?>
                    <!-- ── Success State ── -->
                    <div class="text-center py-3">
                        <div style="font-size:4.5rem;margin-bottom:16px;">
                            <i class="fas fa-check-circle text-success" style="filter:drop-shadow(0 0 15px #4caf50);"></i>
                        </div>
                        <h4 class="text-white mb-2">Registration Successful!</h4>
                        <p style="color:rgba(255,255,255,0.7);font-size:14px;margin-bottom:28px;">
                            Your admin account has been created.<br>You can now log in with your credentials.
                        </p>
                        <a href="login.php?type=admin" class="btn btn-primary w-100 mb-3">
                            <i class="fas fa-sign-in-alt"></i> Go to Admin Login
                        </a>
                        <a href="index.php" class="text-info" style="font-size:13px;">
                            <i class="fas fa-home me-1"></i> Back to Home
                        </a>
                    </div>

                    <?php else: ?>
                    <!-- ── Registration Form ── -->
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" id="regForm" novalidate>

                        <!-- Full Name -->
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-user me-1"></i> Full Name
                            </label>
                            <input type="text" class="form-control" name="full_name" id="full_name"
                                   placeholder="Enter your full name" autofocus autocomplete="name"
                                   value="<?php echo htmlspecialchars($old['full_name'] ?? ''); ?>">
                            <div class="field-err" id="err_name" style="color:#f44336;font-size:12px;margin-top:4px;display:none;"></div>
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-envelope me-1"></i> Email Address
                            </label>
                            <input type="email" class="form-control" name="email" id="email"
                                   placeholder="Enter your email address" autocomplete="email"
                                   value="<?php echo htmlspecialchars($old['email'] ?? ''); ?>">
                            <div class="field-err" id="err_email" style="color:#f44336;font-size:12px;margin-top:4px;display:none;"></div>
                        </div>

                        <!-- Phone Number -->
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-phone me-1"></i> Phone Number
                            </label>
                            <input type="tel" class="form-control" name="phone" id="phone"
                                   placeholder="Enter 10-digit phone number" autocomplete="tel"
                                   maxlength="10" inputmode="numeric"
                                   value="<?php echo htmlspecialchars($old['phone'] ?? ''); ?>">
                            <div class="field-err" id="err_phone" style="color:#f44336;font-size:12px;margin-top:4px;display:none;"></div>
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-lock me-1"></i> Password
                            </label>
                            <div class="input-wrap">
                                <input type="password" class="form-control" name="password" id="password"
                                       placeholder="Minimum 6 characters" autocomplete="new-password">
                                <span class="field-icon" onclick="toggleVis('password','eyeP')">
                                    <i class="fas fa-eye" id="eyeP"></i>
                                </span>
                            </div>
                            <div class="strength-bar-wrap">
                                <div class="strength-bar" id="strengthBar"></div>
                            </div>
                            <small id="strengthText" style="color:rgba(255,255,255,0.5);font-size:11px;"></small>
                            <div class="field-err" id="err_pass" style="color:#f44336;font-size:12px;margin-top:4px;display:none;"></div>
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-lock me-1"></i> Confirm Password
                            </label>
                            <div class="input-wrap">
                                <input type="password" class="form-control" name="confirm_password" id="confirm_password"
                                       placeholder="Re-enter your password" autocomplete="new-password">
                                <span class="field-icon" onclick="toggleVis('confirm_password','eyeC')">
                                    <i class="fas fa-eye" id="eyeC"></i>
                                </span>
                            </div>
                            <div class="field-err" id="err_confirm" style="color:#f44336;font-size:12px;margin-top:4px;display:none;"></div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-3" id="regBtn">
                            <i class="fas fa-user-shield"></i>
                            <span id="regBtnText"> Create Admin Account</span>
                        </button>
                    </form>

                    <div class="text-center mt-2">
                        <p class="text-white mb-2" style="font-size:14px;">
                            Already have an account?
                            <a href="login.php?type=admin" class="text-info">Login here</a>
                        </p>
                        <a href="index.php" class="text-info" style="font-size:13px;">
                            <i class="fas fa-arrow-left me-1"></i> Back to Home
                        </a>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Show / hide password
function toggleVis(fieldId, iconId) {
    const f = document.getElementById(fieldId);
    const i = document.getElementById(iconId);
    if (!f) return;
    if (f.type === 'password') { f.type = 'text';     i.classList.replace('fa-eye', 'fa-eye-slash'); }
    else                       { f.type = 'password'; i.classList.replace('fa-eye-slash', 'fa-eye'); }
}

// Inline error helpers
function showErr(id, msg) {
    const el = document.getElementById(id);
    if (!el) return;
    el.textContent = msg;
    el.style.display = 'block';
}
function clearErr(id) {
    const el = document.getElementById(id);
    if (el) el.style.display = 'none';
}
function setFieldState(inputId, valid) {
    const el = document.getElementById(inputId);
    if (!el) return;
    el.style.borderColor = valid ? '' : '#f44336';
    el.style.boxShadow   = valid ? '' : '0 0 0 3px rgba(244,67,54,0.2)';
}

// Email regex
function isEmail(v) { return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v); }

// Password strength
const strengthLevels = [
    { w: '0%',   c: '',        t: '' },
    { w: '25%',  c: '#f44336', t: 'Weak' },
    { w: '50%',  c: '#ff9800', t: 'Fair' },
    { w: '75%',  c: '#2196f3', t: 'Good' },
    { w: '100%', c: '#4caf50', t: 'Strong' },
];
const passInput = document.getElementById('password');
if (passInput) {
    passInput.addEventListener('input', function () {
        const v = this.value;
        let s = 0;
        if (v.length >= 6)  s++;
        if (v.length >= 10) s++;
        if (/[A-Z]/.test(v) && /[a-z]/.test(v)) s++;
        if (/[0-9]/.test(v)) s++;
        if (/[^A-Za-z0-9]/.test(v)) s++;
        const lvl = strengthLevels[Math.min(s, 4)];
        const bar = document.getElementById('strengthBar');
        const txt = document.getElementById('strengthText');
        if (bar) { bar.style.width = lvl.w; bar.style.background = lvl.c; }
        if (txt)   txt.textContent = lvl.t;
        if (v) clearErr('err_pass');
    });
}

// Live blur validation
const fields = {
    full_name:        { err: 'err_name',    check: v => v.trim().length >= 2      ? '' : 'Full name must be at least 2 characters.' },
    email:            { err: 'err_email',   check: v => isEmail(v.trim())         ? '' : 'Please enter a valid email address.' },
    phone:            { err: 'err_phone',   check: v => /^[0-9]{10}$/.test(v.trim()) ? '' : 'Phone number must be exactly 10 digits.' },
    password:         { err: 'err_pass',    check: v => v.length >= 6             ? '' : 'Password must be at least 6 characters.' },
    confirm_password: { err: 'err_confirm', check: v => {
        const p = document.getElementById('password');
        return (p && v === p.value) ? '' : 'Passwords do not match.';
    }},
};

// Allow only numeric input on phone field
const phoneInput = document.getElementById('phone');
if (phoneInput) {
    phoneInput.addEventListener('input', () => {
        phoneInput.value = phoneInput.value.replace(/[^0-9]/g, '').slice(0, 10);
    });
}
Object.entries(fields).forEach(([id, cfg]) => {
    const el = document.getElementById(id);
    if (!el) return;
    el.addEventListener('blur', () => {
        const msg = cfg.check(el.value);
        if (msg) { showErr(cfg.err, msg); setFieldState(id, false); }
        else      { clearErr(cfg.err);    setFieldState(id, true);  }
    });
    el.addEventListener('input', () => {
        if (el.value) { clearErr(cfg.err); setFieldState(id, true); }
    });
});

// Submit guard
const regForm = document.getElementById('regForm');
const regBtn  = document.getElementById('regBtn');
if (regForm) {
    regForm.addEventListener('submit', function (e) {
        let valid = true;
        Object.entries(fields).forEach(([id, cfg]) => {
            const el = document.getElementById(id);
            if (!el) return;
            const msg = cfg.check(el.value);
            if (msg) { showErr(cfg.err, msg); setFieldState(id, false); valid = false; }
        });
        if (!valid) { e.preventDefault(); return; }
        if (regBtn) {
            regBtn.disabled = true;
            regBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span> Creating Account…</span>';
        }
    });
}
</script>
</body>
</html>
