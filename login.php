<?php
require "config.php";

if (isset($_SESSION["user_id"])) {
    header("Location: " . ($_SESSION["role"] === "admin" ? "admin.php" : "index.php"));
    exit;
}

$error = "";
$registered = isset($_GET["registered"]);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    $stmt = $conn->prepare("SELECT user_id, password, role, full_name FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user["password"])) {
        $_SESSION["user_id"]   = $user["user_id"];
        $_SESSION["username"]  = $username;
        $_SESSION["full_name"] = $user["full_name"];
        $_SESSION["role"]      = $user["role"];
        header("Location: " . ($user["role"] === "admin" ? "admin.php" : "index.php"));
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>MedBook - Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
    <div class="auth-wrap">
        <div class="auth-icon">+</div>
        <h2>MedBook Clinic</h2>
        <div class="sub">Sign in to manage appointments</div>

        <?php if ($registered): ?>
            <div class="alert alert-success">Account created. Please log in.</div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="field">
                <label>Username</label>
                <input type="text" name="username" required autofocus>
            </div>
            <div class="field">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-full">Login</button>
        </form>
        <div class="auth-link">
            No account? <a href="register.php">Register as a patient</a>
        </div>
        <div class="sample-hint">
            Sample admin: admin / admin123 &nbsp;&middot;&nbsp; Sample patient: patient / patient123
        </div>
    </div>
</body>
</html>
