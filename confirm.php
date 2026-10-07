<?php
require "config.php";
require "auth.php";
requireAdmin();

$appointmentId = $_POST["appointment_id"] ?? "";
$date          = $_POST["appt_date"] ?? "";
$time          = $_POST["appt_time"] ?? "";

if ($appointmentId === "" || $date === "" || $time === "") {
    header("Location: admin.php?error=" . urlencode("Please pick both a date and a time."));
    exit;
}

$stmt = $conn->prepare("
    SELECT a.doctor_id, d.daily_limit, d.name
    FROM appointments a
    JOIN doctors d ON a.doctor_id = d.doctor_id
    WHERE a.appointment_id = ?
");
$stmt->bind_param("i", $appointmentId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) {
    header("Location: admin.php?error=" . urlencode("Request not found."));
    exit;
}

$stmt = $conn->prepare("
    SELECT COUNT(*) AS c FROM appointments
    WHERE doctor_id = ? AND appt_date = ? AND status = 'confirmed'
");
$stmt->bind_param("is", $row["doctor_id"], $date);
$stmt->execute();
$countOnDate = $stmt->get_result()->fetch_assoc()["c"];

if ($countOnDate >= $row["daily_limit"]) {
    header("Location: admin.php?error=" . urlencode(
        $row["name"] . " already has " . $row["daily_limit"] . " appointments on " . $date . " (daily limit reached)."
    ));
    exit;
}

$stmt = $conn->prepare("
    UPDATE appointments
    SET status = 'confirmed', appt_date = ?, appt_time = ?
    WHERE appointment_id = ?
");
$stmt->bind_param("ssi", $date, $time, $appointmentId);

if ($stmt->execute()) {
    header("Location: admin.php?success=" . urlencode("Appointment confirmed for $date at $time."));
} else {
    header("Location: admin.php?error=" . urlencode("That exact date/time is already taken for this doctor."));
}
exit;
?>
