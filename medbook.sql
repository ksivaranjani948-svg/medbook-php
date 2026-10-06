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
    doctor_id   INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    specialty   VARCHAR(100) NOT NULL
);

CREATE TABLE patients (
    patient_id  INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNIQUE NULL,
    name        VARCHAR(100) NOT NULL,
    phone       VARCHAR(15),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

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
    UNIQUE KEY unique_slot (doctor_id, appt_date, appt_time)
);

INSERT INTO doctors (name, specialty) VALUES
('Dr. Aisha Khan',   'Cardiologist'),
('Dr. Rohan Mehta',  'Dermatologist'),
('Dr. Priya Nair',   'Pediatrician'),
('Dr. Arjun Singh',  'Orthopedic'),
('Dr. Kavya Reddy',  'General Physician'),
('Dr. Sanjay Rao',   'Dentist');

INSERT INTO users (username, password, full_name, role) VALUES
('admin', '$2y$10$naJasFat0CC1N8bTSvSvxurQdPwzWi8YiCB9XUdpeIYHfEJe3vLkC', 'Admin', 'admin');

INSERT INTO users (username, password, full_name, role) VALUES
('patient', '$2y$10$K7tlf28AVJRJzVdRYcyItuUUOSRqtQ9qgcprlitoBPzUWr83gFH22', 'Test Patient', 'patient');

INSERT INTO patients (user_id, name) VALUES
(2, 'Test Patient');
