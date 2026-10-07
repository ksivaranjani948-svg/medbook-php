<?php
require "config.php";
require "auth.php";
requirePatient();

$doctorId = $_POST["doctor_id"] ?? "";
$reason   = trim($_POST["reason"] ?? "");

if ($doctorId === "" || $reason === "") {
    header("Location: index.php?error=" . urlencode("Please choose a doctor and enter a reason."));
    exit;
}

$stmt = $conn->prepare("SELECT patient_id FROM patients WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();
$patientId = $stmt->get_result()->fetch_assoc()["patient_id"];

$stmt = $conn->prepare(
    "INSERT INTO appointments (doctor_id, patient_id, reason, status) VALUES (?, ?, ?, 'pending')"
);
$stmt->bind_param("iis", $doctorId, $patientId, $reason);
$stmt->execute();

header("Location: index.php?success=1");
exit;
?>
