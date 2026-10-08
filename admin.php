<?php
require "config.php";
require "auth.php";
requireAdmin();

$doctorsResult = $conn->query("SELECT * FROM doctors ORDER BY name");
$doctors = [];
while ($row = $doctorsResult->fetch_assoc()) {
    $doctors[] = $row;
}

$pending = $conn->query("
    SELECT a.appointment_id, a.reason, a.requested_at, a.doctor_id,
           p.name AS patient_name, p.phone, p.email,
           d.name AS doctor_name, d.specialty, d.daily_limit
    FROM appointments a
    JOIN patients p ON a.patient_id = p.patient_id
    JOIN doctors  d ON a.doctor_id  = d.doctor_id
    WHERE a.status = 'pending'
    ORDER BY a.requested_at ASC
");

$confirmed = $conn->query("
    SELECT a.appointment_id, a.appt_date, a.appt_time, a.reason,
           p.name AS patient_name, p.phone, p.email,
           d.name AS doctor_name
    FROM appointments a
    JOIN patients p ON a.patient_id = p.patient_id
    JOIN doctors  d ON a.doctor_id  = d.doctor_id
    WHERE a.status = 'confirmed'
    ORDER BY a.appt_date, a.appt_time
");

$pendingCount   = $conn->query("SELECT COUNT(*) AS c FROM appointments WHERE status = 'pending'")->fetch_assoc()["c"];
$confirmedCount = $conn->query("SELECT COUNT(*) AS c FROM appointments WHERE status = 'confirmed'")->fetch_assoc()["c"];
$todayCount     = $conn->query("SELECT COUNT(*) AS c FROM appointments WHERE status = 'confirmed' AND appt_date = CURDATE()")->fetch_assoc()["c"];
$patientCount   = $conn->query("SELECT COUNT(*) AS c FROM patients")->fetch_assoc()["c"];
?>
<!DOCTYPE html>
<html>
<head>
    <title>MedBook - Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="topbar">
        <div class="brand">
            <div class="cross">+</div>
            <h1>MedBook</h1>
            <span class="tag">Admin</span>
        </div>
        <div class="right">
            <span><?= htmlspecialchars($_SESSION["full_name"]) ?></span>
            <a class="logout" href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <h2 class="page-title">Clinic Overview</h2>

        <?php if (isset($_GET["success"])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_GET["success"]) ?></div>
        <?php elseif (isset($_GET["error"])): ?>
            <div class="alert alert-error"><?= htmlspecialchars($_GET["error"]) ?></div>
        <?php endif; ?>

        <div class="stats">
            <div class="stat-card">
                <div class="num"><?= $pendingCount ?></div>
                <div class="label">Awaiting Assignment</div>
            </div>
            <div class="stat-card">
                <div class="num"><?= $confirmedCount ?></div>
                <div class="label">Confirmed</div>
            </div>
            <div class="stat-card">
                <div class="num"><?= $todayCount ?></div>
                <div class="label">Today</div>
            </div>
            <div class="stat-card">
                <div class="num"><?= $patientCount ?></div>
                <div class="label">Patients</div>
            </div>
        </div>

        <div class="card" style="margin-bottom:20px;">
            <h2>Pending Requests - Assign a Date &amp; Time</h2>
            <?php if ($pending->num_rows === 0): ?>
                <div class="empty-row">No pending requests right now.</div>
            <?php else: ?>
            <table>
                <tr>
                    <th>Appt ID</th>
                    <th>Patient</th>
                    <th>Doctor</th>
                    <th>Reason</th>
                    <th>Assign Slot</th>
                    <th></th>
                </tr>
                <?php while ($p = $pending->fetch_assoc()): ?>
                    <tr>
                        <td class="appt-id">APT-<?= 1000 + $p["appointment_id"] ?></td>
                        <td><?= htmlspecialchars($p["patient_name"]) ?></td>
                        <td>
                            <?= htmlspecialchars($p["doctor_name"]) ?>
                            <div class="spec"><?= htmlspecialchars($p["specialty"]) ?> &middot; limit <?= $p["daily_limit"] ?>/day</div>
                        </td>
                        <td><?= htmlspecialchars($p["reason"]) ?></td>
                        <td>
                            <form action="confirm.php" method="POST" class="inline-form">
                                <input type="hidden" name="appointment_id" value="<?= $p["appointment_id"] ?>">
                                <input type="date" name="appt_date" min="<?= date("Y-m-d") ?>" required>
                                <input type="time" name="appt_time" min="09:00" max="17:00" required>
                                <button type="submit" class="btn btn-small">Confirm</button>
                            </form>
                            <div class="spec">Clinic hours: 09:00 - 17:00</div>
                        </td>
                        <td>
                            <a class="cancel-link" href="cancel.php?id=<?= $p["appointment_id"] ?>"
                               onclick="return confirm('Reject this request?');">Reject</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2>Confirmed Appointments</h2>
            <?php if ($confirmed->num_rows === 0): ?>
                <div class="empty-row">No confirmed appointments yet.</div>
            <?php else: ?>
            <table>
                <tr>
                    <th>Appt ID</th>
                    <th>Patient</th>
                    <th>Doctor</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Notify</th>
                    <th></th>
                </tr>
                <?php while ($a = $confirmed->fetch_assoc()):
                    $message = "Hi " . $a["patient_name"] . ", your appointment with " . $a["doctor_name"]
                             . " at MedBook Clinic is confirmed for " . $a["appt_date"] . " at " . $a["appt_time"] . ".";
                    $waLink = "https://wa.me/" . preg_replace('/[^0-9]/', '', $a["phone"]) . "?text=" . urlencode($message);
                    $mailLink = "mailto:" . urlencode($a["email"]) . "?subject=" . urlencode("Your MedBook Appointment")
                              . "&body=" . urlencode($message);
                ?>
                    <tr>
                        <td class="appt-id">APT-<?= 1000 + $a["appointment_id"] ?></td>
                        <td><?= htmlspecialchars($a["patient_name"]) ?></td>
                        <td><?= htmlspecialchars($a["doctor_name"]) ?></td>
                        <td><?= $a["appt_date"] ?></td>
                        <td><?= $a["appt_time"] ?></td>
                        <td>
                            <a class="notify-btn notify-wa" href="<?= $waLink ?>" target="_blank">WhatsApp</a>
                            <?php if ($a["email"]): ?>
                                <a class="notify-btn notify-mail" href="<?= $mailLink ?>">Email</a>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a class="cancel-link" href="cancel.php?id=<?= $a["appointment_id"] ?>"
                               onclick="return confirm('Cancel this appointment?');">Cancel</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
