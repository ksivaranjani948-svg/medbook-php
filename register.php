<?php
require "config.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullName = trim($_POST["full_name"] ?? "");
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";
    $phone    = trim($_POST["phone"] ?? "");
    $email    = trim($_POST["email"] ?? "");

    if ($fullName === "" || $username === "" || $password === "" || $phone === "") {
        $error = "Please fill in name, username, password and phone number.";
    } else {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "That username is already taken.";
        } else {
            $conn->begin_transaction();

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insertUser = $conn->prepare(
                "INSERT INTO users (username, password, full_name, role) VALUES (?, ?, ?, 'patient')"
            );
            $insertUser->bind_param("sss", $username, $hash, $fullName);
            $insertUser->execute();
            $newUserId = $insertUser->insert_id;

            $insertPatient = $conn->prepare(
                "INSERT INTO patients (user_id, name, phone, email) VALUES (?, ?, ?, ?)"
            );
            $insertPatient->bind_param("isss", $newUserId, $fullName, $phone, $email);
            $insertPatient->execute();

            $conn->commit();

            header("Location: login.php?registered=1");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>MedBook - Register</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
    <div class="auth-wrap">
        <div class="auth-icon">+</div>
        <h2>Create Patient Account</h2>
        <div class="sub">Request appointments and track their status</div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST" autocomplete="off">
            <div class="field">
                <label>Full Name</label>
                <input type="text" name="full_name" required autofocus>
            </div>
            <div class="field">
                <label>Phone (for WhatsApp updates)</label>
                <input type="text" name="phone" placeholder="e.g. 919876543210" required>
            </div>
            <div class="field">
                <label>Email (optional)</label>
                <input type="email" name="email">
            </div>
            <div class="field">
                <label>Username</label>
            <input type="text" name="username" autocomplete="off" required>   
            </div>
            <div class="field">
                <label>Password</label>
                <input type="password" name="password" autocomplete="new-password" required>
            </div>
            <button type="submit" class="btn btn-full">Register</button>
        </form>
        <div class="auth-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
</body>
</html>
