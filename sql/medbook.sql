CREATE DATABASE IF NOT EXISTS medbook;
USE medbook;

CREATE TABLE users (
    user_id     INT AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(50) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    full_name   VARCHAR(100) NOT NULL,
    role        ENUM('admin', 'patient') NOT NULL DEFAULT 'patient',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE doctors (
    doctor_id    INT AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(100) NOT NULL,
    specialty    VARCHAR(100) NOT NULL,
    daily_limit  INT NOT NULL DEFAULT 10
);

CREATE TABLE patients (
    patient_id  INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNIQUE NOT NULL,
    name        VARCHAR(100) NOT NULL,
    phone       VARCHAR(20),
    email       VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

CREATE TABLE appointments (
    appointment_id  INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id       INT NOT NULL,
    patient_id      INT NOT NULL,
    reason          VARCHAR(255),
    status          ENUM('pending', 'confirmed', 'cancelled') NOT NULL DEFAULT 'pending',
    appt_date       DATE NULL,
    appt_time       TIME NULL,
    requested_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_id)  REFERENCES doctors(doctor_id),
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id),
    UNIQUE KEY unique_slot (doctor_id, appt_date, appt_time)
);

INSERT INTO doctors (name, specialty, daily_limit) VALUES
('Dr. Aisha Khan',   'Cardiologist',       6),
('Dr. Rohan Mehta',  'Dermatologist',      8),
('Dr. Priya Nair',   'Pediatrician',       10),
('Dr. Arjun Singh',  'Orthopedic',         8),
('Dr. Kavya Reddy',  'General Physician',  12),
('Dr. Sanjay Rao',   'Dentist',            8);

INSERT INTO users (username, password, full_name, role) VALUES
('admin', '$2y$10$naJasFat0CC1N8bTSvSvxurQdPwzWi8YiCB9XUdpeIYHfEJe3vLkC', 'Admin', 'admin');
