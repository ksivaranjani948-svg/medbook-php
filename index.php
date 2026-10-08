<?php
require "config.php";
require "auth.php";
requirePatient();

$stmt = $conn->prepare("SELECT patient_id FROM patients WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();
$myPatientId = $stmt->get_result()->fetch_assoc()["patient_id"];

$doctorsResult = $conn->query("SELECT * FROM doctors ORDER BY name");
$doctors = [];
while ($row = $doctorsResult->fetch_assoc()) {
    $doctors[] = $row;
}

$stmt = $conn->prepare("
    SELECT a.appointment_id, a.status, a.appt_date, a.appt_time, a.reason,
           d.name AS doctor_name, d.specialty
    FROM appointments a
    JOIN doctors d ON a.doctor_id = d.doctor_id
    WHERE a.patient_id = ?
    ORDER BY a.requested_at DESC
");
$stmt->bind_param("i", $myPatientId);
$stmt->execute();
$myAppointments = $stmt->get_result();

$stmt = $conn->prepare("SELECT COUNT(*) AS c FROM appointments WHERE patient_id = ? AND status = 'pending'");
$stmt->bind_param("i", $myPatientId);
$stmt->execute();
$pendingCount = $stmt->get_result()->fetch_assoc()["c"];

$stmt = $conn->prepare("SELECT COUNT(*) AS c FROM appointments WHERE patient_id = ? AND status = 'confirmed'");
$stmt->bind_param("i", $myPatientId);
$stmt->execute();
$confirmedCount = $stmt->get_result()->fetch_assoc()["c"];
?>
<!DOCTYPE html>
<html>
<head>
    <title>MedBook - My Appointments</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="topbar">
        <div class="brand">
            <div class="cross">+</div>
            <h1>MedBook</h1>
            <span class="tag">Patient</span>
        </div>
        <div class="right">
            <span><?= htmlspecialchars($_SESSION["full_name"]) ?></span>
            <a class="logout" href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <h2 class="page-title">My Appointments</h2>

        <?php if (isset($_GET["success"])): ?>
            <div class="alert alert-success">Request sent. The clinic will confirm a date &amp; time soon.</div>
        <?php elseif (isset($_GET["error"])): ?>
            <div class="alert alert-error"><?= htmlspecialchars($_GET["error"]) ?></div>
        <?php endif; ?>

        <div class="stats">
            <div class="stat-card">
                <div class="num"><?= $pendingCount ?></div>
                <div class="label">Awaiting Confirmation</div>
            </div>
            <div class="stat-card">
                <div class="num"><?= $confirmedCount ?></div>
                <div class="label">Confirmed</div>
            </div>
            <div class="stat-card">
                <div class="num"><?= count($doctors) ?></div>
                <div class="label">Doctors Available</div>
            </div>
        </div>

        <div class="grid">
            <div class="card">
                <h2>Request an Appointment</h2>
                <p class="hint-text">Pick a doctor and tell us why you need a visit. The clinic will assign an exact date &amp; time and message you.</p>
                <form action="book.php" method="POST">
                    <div class="field">
                        <label>Doctor</label>
                        <select name="doctor_id" required>
                            <option value="">-- Select Doctor --</option>
                            <?php foreach ($doctors as $doc): ?>
                                <option value="<?= $doc["doctor_id"] ?>">
                                    <?= htmlspecialchars($doc["name"]) ?> (<?= htmlspecialchars($doc["specialty"]) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label>Reason for visit</label>
                        <textarea name="reason" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-full">Send Request</button>
                </form>
            </div>

            <div class="card">
                <h2>Status of My Requests</h2>
                <?php if ($myAppointments->num_rows === 0): ?>
                    <div class="empty-row">You haven't requested any appointments yet.</div>
                <?php else: ?>
                <?php while ($a = $myAppointments->fetch_assoc()): ?>
                    <div class="appt-card">
                        <div class="appt-card-head">
                            <span class="appt-id">APT-<?= 1000 + $a["appointment_id"] ?></span>
                            <span class="badge badge-<?= $a["status"] ?>"><?= ucfirst($a["status"]) ?></span>
                        </div>
                        <table class="appt-detail">
                            <tr><th>Doctor</th><td><?= htmlspecialchars($a["doctor_name"]) ?></td></tr>
                            <tr><th>Specialization</th><td><?= htmlspecialchars($a["specialty"]) ?></td></tr>
                            <tr><th>Reason</th><td><?= htmlspecialchars($a["reason"]) ?></td></tr>
                            <tr>
                                <th>Date</th>
                                <td><?= $a["status"] === "confirmed" ? date("d M Y", strtotime($a["appt_date"])) : '<span class="text-muted">Not yet assigned</span>' ?></td>
                            </tr>
                            <?php if ($a["status"] === "confirmed"): ?>
                            <tr><th>Time</th><td><?= date("h:i A", strtotime($a["appt_time"])) ?></td></tr>
                            <?php endif; ?>
                        </table>
                        <?php if ($a["status"] !== "cancelled"): ?>
                        <a class="cancel-link" href="cancel.php?id=<?= $a["appointment_id"] ?>"
                           onclick="return confirm('Cancel this appointment request?');">Cancel this appointment</a>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
