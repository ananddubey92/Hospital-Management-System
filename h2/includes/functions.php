<?php
require_once __DIR__ . '/../config/database.php';

// Sanitize input data
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Authentication functions
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

function isDoctorLoggedIn() {
    return isset($_SESSION['doctor_id']);
}

function isPatientLoggedIn() {
    return isset($_SESSION['patient_id']);
}

function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: ../login.php?type=admin');
        exit();
    }
}

function requireDoctorLogin() {
    if (!isDoctorLoggedIn()) {
        header('Location: ../login.php?type=doctor');
        exit();
    }
}

function requirePatientLogin() {
    if (!isPatientLoggedIn()) {
        header('Location: ../login.php?type=patient');
        exit();
    }
}

// ID Generation functions
function generatePatientId() {
    $pdo = getConnection();
    $stmt = $pdo->query("SELECT COUNT(*) FROM patients");
    $count = $stmt->fetchColumn() + 1;
    return 'PAT' . str_pad($count, 4, '0', STR_PAD_LEFT);
}

function generateDoctorId() {
    $pdo = getConnection();
    $stmt = $pdo->query("SELECT COUNT(*) FROM doctors");
    $count = $stmt->fetchColumn() + 1;
    return 'DOC' . str_pad($count, 4, '0', STR_PAD_LEFT);
}

function generateAppointmentId() {
    $pdo = getConnection();
    $stmt = $pdo->query("SELECT COUNT(*) FROM appointments");
    $count = $stmt->fetchColumn() + 1;
    return 'APPT' . str_pad($count, 4, '0', STR_PAD_LEFT);
}

function generateBillId() {
    $pdo = getConnection();
    $stmt = $pdo->query("SELECT COUNT(*) FROM billing");
    $count = $stmt->fetchColumn() + 1;
    return 'BILL' . str_pad($count, 4, '0', STR_PAD_LEFT);
}

function generateDepartmentId() {
    $pdo = getConnection();
    $stmt = $pdo->query("SELECT COUNT(*) FROM departments");
    $count = $stmt->fetchColumn() + 1;
    return 'DEPT' . str_pad($count, 3, '0', STR_PAD_LEFT);
}

function generateRoomBookingId() {
    $pdo = getConnection();
    $stmt = $pdo->query("SELECT COUNT(*) FROM room_bookings");
    $count = $stmt->fetchColumn() + 1;
    return 'ROOM' . str_pad($count, 4, '0', STR_PAD_LEFT);
}

// Image upload function
function uploadImage($file, $folder) {
    $target_dir = "uploads/" . $folder . "/";
    $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    // Check if image file is actual image
    $check = getimagesize($file["tmp_name"]);
    if($check === false) {
        return false;
    }
    
    // Check file size (5MB max)
    if ($file["size"] > 5000000) {
        return false;
    }
    
    // Allow certain file formats
    if($file_extension != "jpg" && $file_extension != "png" && $file_extension != "jpeg") {
        return false;
    }
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $new_filename;
    }
    return false;
}

// Validation functions
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePhone($phone) {
    return preg_match('/^[0-9]{10}$/', $phone);
}

function validateDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

// Format currency
function formatCurrency($amount) {
    return '₹' . number_format($amount, 2);
}

// Calculate age from date of birth
function calculateAge($dob) {
    $today = new DateTime();
    $birthDate = new DateTime($dob);
    return $today->diff($birthDate)->y;
}

// Get user details
function getPatientDetails($patient_id) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM patients WHERE id = ?");
    $stmt->execute([$patient_id]);
    return $stmt->fetch();
}

function getDoctorDetails($doctor_id) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT d.*, dept.name as department_name FROM doctors d LEFT JOIN departments dept ON d.department_id = dept.id WHERE d.id = ?");
    $stmt->execute([$doctor_id]);
    return $stmt->fetch();
}

function getAdminDetails($admin_id) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM admin WHERE id = ?");
    $stmt->execute([$admin_id]);
    return $stmt->fetch();
}

// Get all departments
function getAllDepartments() {
    $pdo = getConnection();
    $stmt = $pdo->query("SELECT * FROM departments ORDER BY name");
    return $stmt->fetchAll();
}

// Get doctors by department
function getDoctorsByDepartment($department_id) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM doctors WHERE department_id = ? AND status = 'active' ORDER BY name");
    $stmt->execute([$department_id]);
    return $stmt->fetchAll();
}

// Check room availability
function isRoomAvailable($room_id, $check_in_date, $check_out_date = null) {
    $pdo = getConnection();
    
    if ($check_out_date) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM room_bookings 
            WHERE room_id = ? 
            AND status IN ('booked', 'checked_in') 
            AND (
                (check_in_date <= ? AND (check_out_date IS NULL OR check_out_date >= ?))
                OR (check_in_date <= ? AND (check_out_date IS NULL OR check_out_date >= ?))
            )
        ");
        $stmt->execute([$room_id, $check_in_date, $check_in_date, $check_out_date, $check_out_date]);
    } else {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM room_bookings 
            WHERE room_id = ? 
            AND status IN ('booked', 'checked_in') 
            AND check_in_date = ?
        ");
        $stmt->execute([$room_id, $check_in_date]);
    }
    
    return $stmt->fetchColumn() == 0;
}

// Check doctor availability
function isDoctorAvailable($doctor_id, $appointment_date, $appointment_time) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM appointments 
        WHERE doctor_id = ? 
        AND appointment_date = ? 
        AND appointment_time = ? 
        AND status IN ('pending', 'confirmed')
    ");
    $stmt->execute([$doctor_id, $appointment_date, $appointment_time]);
    
    return $stmt->fetchColumn() == 0;
}
?>