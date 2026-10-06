# MedBook — Appointment Booking System

A simple mini-project: book and manage doctor appointments, with a login
system and a real relational database (MySQL, 3NF, foreign keys).

## Tech stack
- Frontend: HTML/CSS (plain, no framework)
- Backend: PHP
- Database: MySQL (via XAMPP)

## Database design
4 tables, 3rd Normal Form:
- `users` — login accounts
- `doctors` — doctor_id, name, specialty
- `patients` — patient_id, name, phone
- `appointments` — links doctors + patients, with a `UNIQUE KEY` on
  (doctor_id, appt_date, appt_time) so the database itself blocks double-booking

## Setup (XAMPP)

1. Install XAMPP from https://www.apachefriends.org (includes Apache, MySQL, PHP, phpMyAdmin).
2. Open the XAMPP Control Panel and click **Start** next to **Apache** and **MySQL**.
3. Copy this whole `medbook-php` folder into XAMPP's `htdocs` folder, so that
   `htdocs/medbook-php/index.php` exists.
   - Windows: `C:\xampp\htdocs\medbook-php`
   - Mac: `/Applications/XAMPP/htdocs/medbook-php`
4. Open `http://localhost/phpmyadmin` in your browser.
   - Click the **SQL** tab, open `sql/medbook.sql` from this project, paste
     its contents in, and click **Go**. This creates the `medbook` database
     and all 4 tables, with sample doctors and one sample login.
5. Visit `http://localhost/medbook-php/` in your browser.
   - You'll land on the login page.
   - Sample login: **username:** `admin` **password:** `admin123`
   - Or click "Register here" to create your own account.

## Pages
- `login.php` / `register.php` / `logout.php` — authentication
- `index.php` — dashboard: book an appointment, view doctors, view/cancel appointments
- `book.php` — handles the booking form submission
- `cancel.php` — deletes an appointment

## Notes for viva
- Passwords are hashed with PHP's `password_hash()` / `password_verify()` — never stored in plain text.
- All queries use prepared statements (`mysqli::prepare` + `bind_param`) to prevent SQL injection.
- The appointments list is built with a 3-table `JOIN` query.
- The dashboard stats use aggregate SQL (`COUNT(*)`).
- Double-booking is prevented at the database level with a `UNIQUE KEY`, not just in PHP code.
