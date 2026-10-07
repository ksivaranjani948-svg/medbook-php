<?php
require "config.php";
require "auth.php";

$id = $_GET["id"] ?? "";

if ($id !== "") {
    if ($_SESSION["role"] === "admin") {
        $stmt = $conn->prepare(
            "UPDATE appointments SET status = 'cancelled', appt_date = NULL, appt_time = NULL
             WHERE appointment_id = ?"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("
            UPDATE appointments a
            JOIN patients p ON a.patient_id = p.patient_id
            SET a.status = 'cancelled', a.appt_date = NULL, a.appt_time = NULL
            WHERE a.appointment_id = ? AND p.user_id = ?
        ");
        $stmt->bind_param("ii", $id, $_SESSION["user_id"]);
        $stmt->execute();
    }
}

header("Location: " . ($_SESSION["role"] === "admin" ? "admin.php" : "index.php"));
exit;
?>
