<?php
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

function requireAdmin() {
    if ($_SESSION["role"] !== "admin") {
        header("Location: index.php");
        exit;
    }
}

function requirePatient() {
    if ($_SESSION["role"] !== "patient") {
        header("Location: admin.php");
        exit;
    }
}
?>
