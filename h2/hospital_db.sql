-- Complete Hospital Management System Database
DROP DATABASE IF EXISTS h2;
CREATE DATABASE h2;
USE h2;

-- Admin Table
CREATE TABLE admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Departments Table
CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dept_id VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    head_of_department VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Doctors Table
CREATE TABLE doctors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doc_id VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    department_id INT NOT NULL,
    specialization VARCHAR(100) NOT NULL,
    qualification VARCHAR(200) NOT NULL,
    experience INT NOT NULL,
    consultation_fee DECIMAL(10,2) NOT NULL,
    rating DECIMAL(2,1) DEFAULT 0.0,
    address TEXT,
    image VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE
);

-- Patients Table
CREATE TABLE patients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pat_id VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    dob DATE NOT NULL,
    age INT NOT NULL,
    gender ENUM('male', 'female', 'other') NOT NULL,
    blood_group VARCHAR(5),
    address TEXT,
    emergency_contact VARCHAR(15),
    image VARCHAR(255),
    medical_history TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Rooms Table
CREATE TABLE rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_number VARCHAR(10) UNIQUE NOT NULL,
    room_type ENUM('general', 'private', 'icu', 'emergency') NOT NULL,
    bed_count INT DEFAULT 1,
    price_per_day DECIMAL(10,2) NOT NULL,
    status ENUM('available', 'occupied', 'maintenance') DEFAULT 'available',
    amenities TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Services Table
CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    duration VARCHAR(50),
    image VARCHAR(255),
    category ENUM('lab', 'scan', 'procedure', 'therapy') NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Appointments Table
CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    appt_id VARCHAR(20) UNIQUE NOT NULL,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    department_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    reason TEXT,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled', 'no_show') DEFAULT 'pending',
    notes TEXT,
    consultation_fee DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE
);

-- Room Bookings Table
CREATE TABLE room_bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id VARCHAR(20) UNIQUE NOT NULL,
    patient_id INT NOT NULL,
    room_id INT NOT NULL,
    check_in_date DATE NOT NULL,
    check_out_date DATE,
    total_days INT DEFAULT 1,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('booked', 'checked_in', 'checked_out', 'cancelled') DEFAULT 'booked',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
);

-- Billing Table
CREATE TABLE billing (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bill_id VARCHAR(20) UNIQUE NOT NULL,
    patient_id INT NOT NULL,
    appointment_id INT,
    room_booking_id INT,
    consultation_fee DECIMAL(10,2) DEFAULT 0,
    service_charges DECIMAL(10,2) DEFAULT 0,
    room_charges DECIMAL(10,2) DEFAULT 0,
    medicine_charges DECIMAL(10,2) DEFAULT 0,
    other_charges DECIMAL(10,2) DEFAULT 0,
    subtotal DECIMAL(10,2) NOT NULL,
    tax_amount DECIMAL(10,2) DEFAULT 0,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_status ENUM('pending', 'paid', 'partial', 'refunded') DEFAULT 'pending',
    payment_method VARCHAR(50),
    payment_date DATETIME,
    bill_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE SET NULL,
    FOREIGN KEY (room_booking_id) REFERENCES room_bookings(id) ON DELETE SET NULL
);

-- Insert Default Admin
INSERT INTO admin (username, password, email, full_name) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@hospital.com', 'Hospital Administrator');

-- Insert Sample Departments
INSERT INTO departments (dept_id, name, description, head_of_department) VALUES
('DEPT001', 'Cardiology', 'Heart and cardiovascular system treatment', 'Dr. John Smith'),
('DEPT002', 'Neurology', 'Brain and nervous system disorders', 'Dr. Sarah Johnson'),
('DEPT003', 'Orthopedics', 'Bone, joint and muscle treatment', 'Dr. Michael Brown');

-- Insert Sample Doctors
INSERT INTO doctors (doc_id, name, email, password, phone, department_id, specialization, qualification, experience, consultation_fee, rating) VALUES
('DOC001', 'Dr. John Smith', 'john@hospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9876543210', 1, 'Cardiology', 'MBBS, MD Cardiology', 10, 800.00, 4.5),
('DOC002', 'Dr. Sarah Johnson', 'sarah@hospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9876543211', 2, 'Neurology', 'MBBS, MD Neurology', 8, 900.00, 4.3);

-- Insert Sample Rooms
INSERT INTO rooms (room_number, room_type, bed_count, price_per_day, amenities) VALUES
('G101', 'general', 4, 500.00, 'Basic bed, shared bathroom, fan'),
('P201', 'private', 1, 1500.00, 'Private bed, attached bathroom, AC, TV'),
('I301', 'icu', 1, 3000.00, 'ICU bed, ventilator, monitoring equipment');

-- Insert Sample Services
INSERT INTO services (service_name, description, price, duration, category) VALUES
('Blood Test', 'Complete blood count', 350.00, '30 minutes', 'lab'),
('X-Ray Chest', 'Chest X-ray examination', 450.00, '15 minutes', 'scan');

-- Insert Sample Patients
INSERT INTO patients (pat_id, name, email, password, phone, dob, age, gender, blood_group, address) VALUES
('PAT001', 'Alice Wilson', 'alice@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9876543220', '1995-05-15', 28, 'female', 'A+', '123 Main St, City');