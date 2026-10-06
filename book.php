<?php
require "config.php";
require "auth.php";

$patientName = trim($_POST["patient_name"] ?? "");
$doctorId    = $_POST["doctor_id"] ?? "";
$date        = $_POST["appt_date"] ?? "";
$time        = $_POST["appt_time"] ?? "";
$reason      = trim($_POST["reason"] ?? "");

if ($patientName === "" || $doctorId === "" || $date === "" || $time === "") {
    header("Location: index.php?error=" . urlencode("Please fill in all required fields."));
    exit;
}

// Find existing patient by name, or create a new one
$stmt = $conn->prepare("SELECT patient_id FROM patients WHERE name = ?");
$stmt->bind_param("s", $patientName);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $patientId = $row["patient_id"];
} else {
    $insert = $conn->prepare("INSERT INTO patients (name) VALUES (?)");
    $insert->bind_param("s", $patientName);
    $insert->execute();
    $patientId = $insert->insert_id;
}

// Insert the appointment (UNIQUE KEY on doctor_id+date+time blocks double-booking)
$stmt = $conn->prepare(
    "INSERT INTO appointments (doctor_id, patient_id, appt_date, appt_time, reason)
     VALUES (?, ?, ?, ?, ?)"
);
$stmt->bind_param("iisss", $doctorId, $patientId, $date, $time, $reason);

if ($stmt->execute()) {
    header("Location: index.php?success=1");
} else {
    header("Location: index.php?error=" . urlencode("That doctor already has an appointment at this date/time."));
}
exit;
?>
