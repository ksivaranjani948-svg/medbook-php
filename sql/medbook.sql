-- ============================================
-- MedBook - Appointment Booking System
-- Database schema (3NF, 4 related tables)
-- ============================================

CREATE DATABASE IF NOT EXISTS medbook;
USE medbook;

-- Table 1: Users (login/authentication)
CREATE TABLE users (
    user_id     INT AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(50) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table 2: Doctors
CREATE TABLE doctors (
    doctor_id   INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    specialty   VARCHAR(100) NOT NULL
);

-- Table 3: Patients
CREATE TABLE patients (
    patient_id  INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    phone       VARCHAR(15)
);

-- Table 4: Appointments (links doctors + patients)
CREATE TABLE appointments (
    appointment_id  INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id       INT NOT NULL,
    patient_id      INT NOT NULL,
    appt_date       DATE NOT NULL,
    appt_time       TIME NOT NULL,
    reason          VARCHAR(255),
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (doctor_id)  REFERENCES doctors(doctor_id),
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id),

    -- Enforces "no double-booking" as a real database constraint,
    -- not just an application-level check
    UNIQUE KEY unique_slot (doctor_id, appt_date, appt_time)
);

-- Sample data
INSERT INTO doctors (name, specialty) VALUES
('Dr. Aisha Khan',   'Cardiologist'),
('Dr. Rohan Mehta',  'Dermatologist'),
('Dr. Priya Nair',   'Pediatrician'),
('Dr. Arjun Singh',  'Orthopedic'),
('Dr. Kavya Reddy',  'General Physician'),
('Dr. Sanjay Rao',   'Dentist');

-- Sample login user -> username: admin, password: admin123
-- (password hash generated with PHP's password_hash() using the default algorithm)
INSERT INTO users (username, password) VALUES
('admin', '$2y$10$naJasFat0CC1N8bTSvSvxurQdPwzWi8YiCB9XUdpeIYHfEJe3vLkC');
