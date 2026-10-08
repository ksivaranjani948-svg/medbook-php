<?php
require "config.php";
require "auth.php";
requireAdmin();

// Clinic operating hours - change these two lines to whatever hours the
// clinic is actually open. Example: for an 11:00 AM - 1:00 PM only window,
// set CLINIC_OPEN = "11:00" and CLINIC_CLOSE = "13:00".
define("CLINIC_OPEN",  "09:00");
define("CLINIC_CLOSE", "17:00");

$appointmentId = $_POST["appointment_id"] ?? "";
$date          = $_POST["appt_date"] ?? "";
$time          = $_POST["appt_time"] ?? "";

if ($appointmentId === "" || $date === "" || $time === "") {
    header("Location: admin.php?error=" . urlencode("Please pick both a date and a time."));
    exit;
}

// Can't assign a slot in the past
if ($date < date("Y-m-d")) {
    header("Location: admin.php?error=" . urlencode("You can't assign a date that's already in the past."));
    exit;
}

// Must fall inside clinic hours
if ($time < CLINIC_OPEN || $time > CLINIC_CLOSE) {
    header("Location: admin.php?error=" . urlencode(
        "The clinic is only open from " . CLINIC_OPEN . " to " . CLINIC_CLOSE . ". Please pick a time in that range."
    ));
    exit;
}

// Find which doctor this request is for, and that doctor's daily limit
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

// How many CONFIRMED appointments does this doctor already have on that date?
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

// Try to confirm - the UNIQUE KEY on (doctor_id, appt_date, appt_time)
// stops two confirmed appointments landing on the exact same slot
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
