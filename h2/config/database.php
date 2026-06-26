<?php
// ── Hospital Management System — Database Configuration ────────────────────
//
// ROOT CAUSE DIAGNOSIS:
// Two MySQL instances exist on this machine:
//   1. MySQL97 (MySQL 9.7 standalone Windows Service) — ACTIVE on port 3306
//      Binary: C:\Program Files\MySQL\MySQL Server 9.7\bin\mysqld.exe
//      Password: Callme@123
//   2. XAMPP MariaDB — CANNOT start (port 3306 already taken by MySQL97)
//
// FIX: Connect to the running MySQL97 instance using the correct password.
// ─────────────────────────────────────────────────────────────────────────────
define('DB_HOST', '127.0.0.1');   // Use IP instead of 'localhost' to force TCP (avoids socket issues)
define('DB_PORT', '3306');
define('DB_USER', 'root');
define('DB_PASS', 'Callme@123');  // MySQL 9.7 root password
define('DB_NAME', 'h2');

// Start session early
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Returns a PDO connection, creating the database and all tables if needed.
 */
function getConnection(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    // ── Step 1: connect WITHOUT selecting a database ────────────────────────
    try {
        $dsn_no_db = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';charset=utf8mb4';
        $root = new PDO($dsn_no_db, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $e) {
        _dbError(
            'Cannot connect to MySQL server.',
            $e->getMessage(),
            _mysqlTips($e->getMessage())
        );
    }

    // ── Step 2: create the database if it does not exist ───────────────────
    try {
        $root->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "`
                     CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    } catch (PDOException $e) {
        _dbError('Failed to create database `' . DB_NAME . '`.', $e->getMessage());
    }

    // ── Step 3: connect to the database ────────────────────────────────────
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT
             . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $e) {
        _dbError('Connected to MySQL but could not select database `' . DB_NAME . '`.', $e->getMessage());
    }

    // ── Step 4: create all tables if they do not exist ─────────────────────
    _ensureTables($pdo);

    return $pdo;
}

// ── Table bootstrap ─────────────────────────────────────────────────────────
function _ensureTables(PDO $pdo): void {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    $tables = [

        "CREATE TABLE IF NOT EXISTS `admin` (
            `id`         INT AUTO_INCREMENT PRIMARY KEY,
            `username`   VARCHAR(50)  UNIQUE NOT NULL,
            `password`   VARCHAR(255) NOT NULL,
            `email`      VARCHAR(100) NOT NULL,
            `full_name`  VARCHAR(100) NOT NULL,
            `phone`      VARCHAR(15)  DEFAULT NULL,
            `image`      VARCHAR(255) DEFAULT NULL,
            `reset_code`    VARCHAR(8)   DEFAULT NULL,
            `reset_expires` DATETIME     DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `departments` (
            `id`                 INT AUTO_INCREMENT PRIMARY KEY,
            `dept_id`            VARCHAR(20) UNIQUE NOT NULL,
            `name`               VARCHAR(100) NOT NULL,
            `description`        TEXT,
            `head_of_department` VARCHAR(100),
            `status`             ENUM('active','inactive') DEFAULT 'active',
            `created_at`         TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `doctors` (
            `id`               INT AUTO_INCREMENT PRIMARY KEY,
            `doc_id`           VARCHAR(20)  UNIQUE NOT NULL,
            `name`             VARCHAR(100) NOT NULL,
            `email`            VARCHAR(100) UNIQUE NOT NULL,
            `password`         VARCHAR(255) NOT NULL,
            `phone`            VARCHAR(15)  NOT NULL,
            `department_id`    INT          NOT NULL,
            `specialization`   VARCHAR(100) NOT NULL,
            `qualification`    VARCHAR(200) NOT NULL,
            `experience`       INT          NOT NULL,
            `consultation_fee` DECIMAL(10,2) NOT NULL,
            `rating`           DECIMAL(2,1)  DEFAULT 0.0,
            `address`          TEXT,
            `image`            VARCHAR(255),
            `status`           ENUM('active','inactive') DEFAULT 'active',
            `reset_code`       VARCHAR(8)   DEFAULT NULL,
            `reset_expires`    DATETIME     DEFAULT NULL,
            `created_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `patients` (
            `id`                INT AUTO_INCREMENT PRIMARY KEY,
            `pat_id`            VARCHAR(20)  UNIQUE NOT NULL,
            `name`              VARCHAR(100) NOT NULL,
            `email`             VARCHAR(100) UNIQUE NOT NULL,
            `password`          VARCHAR(255) NOT NULL,
            `phone`             VARCHAR(15)  NOT NULL,
            `dob`               DATE         NOT NULL,
            `age`               INT          NOT NULL,
            `gender`            ENUM('male','female','other') NOT NULL,
            `blood_group`       VARCHAR(5),
            `address`           TEXT,
            `emergency_contact` VARCHAR(15),
            `image`             VARCHAR(255),
            `medical_history`   TEXT,
            `date_of_birth`     DATE         DEFAULT NULL,
            `reset_code`        VARCHAR(8)   DEFAULT NULL,
            `reset_expires`     DATETIME     DEFAULT NULL,
            `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `rooms` (
            `id`           INT AUTO_INCREMENT PRIMARY KEY,
            `room_number`  VARCHAR(10) UNIQUE NOT NULL,
            `room_type`    ENUM('general','private','icu','emergency') NOT NULL,
            `bed_count`    INT DEFAULT 1,
            `price_per_day` DECIMAL(10,2) NOT NULL,
            `status`       ENUM('available','occupied','maintenance') DEFAULT 'available',
            `amenities`    TEXT,
            `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `services` (
            `id`           INT AUTO_INCREMENT PRIMARY KEY,
            `service_name` VARCHAR(100) NOT NULL,
            `description`  TEXT,
            `price`        DECIMAL(10,2) NOT NULL,
            `duration`     VARCHAR(50),
            `image`        VARCHAR(255),
            `category`     ENUM('lab','scan','procedure','therapy') NOT NULL,
            `status`       ENUM('active','inactive') DEFAULT 'active',
            `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `appointments` (
            `id`               INT AUTO_INCREMENT PRIMARY KEY,
            `appt_id`          VARCHAR(20) UNIQUE NOT NULL,
            `patient_id`       INT NOT NULL,
            `doctor_id`        INT NOT NULL,
            `department_id`    INT NOT NULL,
            `appointment_date` DATE NOT NULL,
            `appointment_time` TIME NOT NULL,
            `reason`           TEXT,
            `status`           ENUM('pending','confirmed','completed','cancelled','no_show') DEFAULT 'pending',
            `notes`            TEXT,
            `consultation_fee` DECIMAL(10,2) NOT NULL,
            `created_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`patient_id`)    REFERENCES `patients`(`id`)    ON DELETE CASCADE,
            FOREIGN KEY (`doctor_id`)     REFERENCES `doctors`(`id`)     ON DELETE CASCADE,
            FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `room_bookings` (
            `id`             INT AUTO_INCREMENT PRIMARY KEY,
            `booking_id`     VARCHAR(20) UNIQUE NOT NULL,
            `patient_id`     INT NOT NULL,
            `room_id`        INT NOT NULL,
            `check_in_date`  DATE NOT NULL,
            `check_out_date` DATE DEFAULT NULL,
            `total_days`     INT DEFAULT 1,
            `total_amount`   DECIMAL(10,2) NOT NULL,
            `status`         ENUM('booked','checked_in','checked_out','cancelled') DEFAULT 'booked',
            `created_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`patient_id`) REFERENCES `patients`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`room_id`)    REFERENCES `rooms`(`id`)    ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `billing` (
            `id`               INT AUTO_INCREMENT PRIMARY KEY,
            `bill_id`          VARCHAR(20) UNIQUE NOT NULL,
            `patient_id`       INT NOT NULL,
            `appointment_id`   INT DEFAULT NULL,
            `room_booking_id`  INT DEFAULT NULL,
            `consultation_fee` DECIMAL(10,2) DEFAULT 0,
            `service_charges`  DECIMAL(10,2) DEFAULT 0,
            `room_charges`     DECIMAL(10,2) DEFAULT 0,
            `medicine_charges` DECIMAL(10,2) DEFAULT 0,
            `other_charges`    DECIMAL(10,2) DEFAULT 0,
            `subtotal`         DECIMAL(10,2) NOT NULL,
            `tax_amount`       DECIMAL(10,2) DEFAULT 0,
            `total_amount`     DECIMAL(10,2) NOT NULL,
            `payment_status`   ENUM('pending','paid','partial','refunded') DEFAULT 'pending',
            `payment_method`   VARCHAR(50),
            `payment_date`     DATETIME DEFAULT NULL,
            `bill_date`        DATE NOT NULL,
            `created_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`patient_id`)      REFERENCES `patients`(`id`)      ON DELETE CASCADE,
            FOREIGN KEY (`appointment_id`)  REFERENCES `appointments`(`id`)  ON DELETE SET NULL,
            FOREIGN KEY (`room_booking_id`) REFERENCES `room_bookings`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `complaints` (
            `id`             INT AUTO_INCREMENT PRIMARY KEY,
            `complaint_id`   VARCHAR(20),
            `patient_name`   VARCHAR(100),
            `patient_id`     VARCHAR(20),
            `phone`          VARCHAR(15),
            `complaint_type` VARCHAR(50),
            `complaint_text` TEXT,
            `status`         ENUM('Pending','In Review','Resolved') DEFAULT 'Pending',
            `created_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `emergencies` (
            `id`          INT AUTO_INCREMENT PRIMARY KEY,
            `patient_name` VARCHAR(100) NOT NULL,
            `contact`      VARCHAR(15),
            `description`  TEXT,
            `severity`     ENUM('low','medium','high','critical') DEFAULT 'medium',
            `status`       ENUM('active','resolved') DEFAULT 'active',
            `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    ];

    foreach ($tables as $sql) {
        try {
            $pdo->exec($sql);
        } catch (PDOException $e) {
            // Log non-fatal table errors but don't crash
            error_log('[HMS DB] Table creation error: ' . $e->getMessage());
        }
    }

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    // Seed default admin if table is empty
    _seedAdmin($pdo);
}

// ── Seed a default admin account ────────────────────────────────────────────
function _seedAdmin(PDO $pdo): void {
    $count = (int) $pdo->query("SELECT COUNT(*) FROM `admin`")->fetchColumn();
    if ($count === 0) {
        // Default login: admin@hospital.com / password
        $hash = password_hash('password', PASSWORD_DEFAULT);
        $pdo->prepare(
            "INSERT IGNORE INTO `admin` (username, password, email, full_name)
             VALUES ('admin', ?, 'admin@hospital.com', 'Hospital Administrator')"
        )->execute([$hash]);
    }
}

// ── Friendly error page ──────────────────────────────────────────────────────
function _dbError(string $title, string $detail, string $tips = ''): never {
    http_response_code(503);
    // Strip sensitive info from detail shown in browser
    $safe = htmlspecialchars(preg_replace('/password[=:\s]+\S+/i', 'password=[hidden]', $detail));
    echo <<<HTML
    <!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width,initial-scale=1">
      <title>Database Error — Hospital Management System</title>
      <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Segoe UI',sans-serif;background:linear-gradient(135deg,#667eea,#764ba2);
             min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
        .box{background:rgba(255,255,255,.08);backdrop-filter:blur(16px);border:1px solid
             rgba(255,255,255,.2);border-radius:18px;padding:36px 32px;max-width:560px;width:100%;
             box-shadow:0 12px 40px rgba(0,0,0,.35)}
        .icon{font-size:3rem;margin-bottom:16px}
        h2{color:#fff;font-size:1.4rem;margin-bottom:10px}
        .detail{background:rgba(0,0,0,.25);border-radius:8px;padding:12px 16px;
                font-family:monospace;font-size:12px;color:#ffa;word-break:break-all;margin:14px 0}
        .tips{color:rgba(255,255,255,.75);font-size:13px;line-height:1.8}
        .tips li{margin-left:18px}
        .btn{display:inline-block;margin-top:20px;padding:10px 24px;background:#00bcd4;
             color:#fff;border-radius:25px;text-decoration:none;font-size:14px;font-weight:600}
      </style>
    </head>
    <body>
      <div class="box">
        <div class="icon">⚠️</div>
        <h2>$title</h2>
        <div class="detail">$safe</div>
        {$tips}
        <a class="btn" href="javascript:location.reload()">Retry</a>
      </div>
    </body>
    </html>
    HTML;
    exit;
}

function _mysqlTips(string $msg): string {
    $tips = '<ul class="tips">';

    if (stripos($msg, '1045') !== false || stripos($msg, 'Access denied') !== false) {
        $tips .= '<li>Open <b>XAMPP Control Panel</b> → click <b>Shell</b> and run:<br>
                  <code>mysqladmin -u root password ""</code> to reset to blank password,<br>
                  or update <code>DB_PASS</code> in <code>config/database.php</code>.</li>';
    }
    if (stripos($msg, 'caching_sha2') !== false) {
        $tips .= '<li><b>Missing plugin</b>: add <code>default_authentication_plugin=mysql_native_password</code>
                  to <code>c:/xampp/mysql/data/my.ini</code> under <code>[mysqld]</code>, then restart MySQL.</li>';
    }
    if (stripos($msg, '2002') !== false || stripos($msg, "Can't connect") !== false) {
        $tips .= '<li>MySQL is not running. Open <b>XAMPP Control Panel</b> and click <b>Start</b> next to MySQL.</li>';
    }
    if (stripos($msg, '1049') !== false) {
        $tips .= '<li>Database does not exist. This page will create it automatically — just reload after starting MySQL.</li>';
    }

    $tips .= '<li>Check XAMPP MySQL port (default <b>3306</b>) is not blocked by another service.</li>';
    $tips .= '</ul>';
    return $tips;
}
