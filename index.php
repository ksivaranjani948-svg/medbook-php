<?php
require "config.php";
require "auth.php";
requirePatient();

// All doctors (for the dropdown + doctors list)
$doctors = $conn->query("SELECT * FROM doctors ORDER BY name");
$doctorRows = [];
while ($d = $doctors->fetch_assoc()) {
    $doctorRows[] = $d;
}

// This patient's appointments (3-table JOIN)
$stmt = $conn->prepare("
    SELECT a.appointment_id, a.reason, a.status, a.appt_date, a.appt_time,
           d.name AS doctor_name, d.specialty
    FROM appointments a
    JOIN doctors  d ON a.doctor_id  = d.doctor_id
    JOIN patients p ON a.patient_id = p.patient_id
    WHERE p.user_id = ?
    ORDER BY FIELD(a.status, 'confirmed', 'pending', 'cancelled'), a.requested_at DESC
");
$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();
$myAppointments = $stmt->get_result();

$statusLabels = [
    "confirmed" => "✅ Confirmed",
    "pending"   => "⏳ Pending",
    "cancelled" => "❌ Cancelled",
];
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
        <h2 class="page-title">Welcome, <?= htmlspecialchars($_SESSION["full_name"]) ?></h2>

        <?php if (isset($_GET["success"])): ?>
            <div class="alert alert-success">
                Request sent! The clinic will assign your date and time soon.
            </div>
        <?php elseif (isset($_GET["error"])): ?>
            <div class="alert alert-error"><?= htmlspecialchars($_GET["error"]) ?></div>
        <?php endif; ?>

        <!-- Book + Doctors -->
        <div class="grid" style="margin-bottom:24px;">
            <div class="card">
                <h2>Request an Appointment</h2>
                <form action="book.php" method="POST">
                    <div class="field">
                        <label>Choose Doctor</label>
                        <select name="doctor_id" required>
                            <option value="">-- Select a doctor --</option>
                            <?php foreach ($doctorRows as $d): ?>
                                <option value="<?= $d["doctor_id"] ?>">
                                    <?= htmlspecialchars($d["name"]) ?> (<?= htmlspecialchars($d["specialty"]) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label>Reason for Visit</label>
                        <input type="text" name="reason" placeholder="e.g. Tooth pain" required>
                    </div>
                    <button type="submit" class="btn btn-full">Send Request</button>
                </form>
            </div>

            <div class="card">
                <h2>Our Doctors</h2>
                <table>
                    <tr><th>Doctor</th><th>Specialty</th></tr>
                    <?php foreach ($doctorRows as $d): ?>
                        <tr>
                            <td><?= htmlspecialchars($d["name"]) ?></td>
                            <td><?= htmlspecialchars($d["specialty"]) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>

        <!-- My Appointments (receipt cards) -->
        <h2 class="page-title">My Appointments</h2>

        <?php if ($myAppointments->num_rows === 0): ?>
            <div class="card"><div class="empty-row">You have no appointments yet. Send a request above.</div></div>
        <?php else: ?>
        <div class="appt-grid">
            <?php while ($a = $myAppointments->fetch_assoc()): ?>
                <div class="appt-card <?= $a["status"] === "cancelled" ? "is-cancelled" : "" ?>">
                    <div class="appt-card-head">
                        <span class="appt-id">Appointment #APT-<?= 1000 + $a["appointment_id"] ?></span>
                    </div>

                    <table class="appt-detail">
                        <tr><th>Doctor</th><td><?= htmlspecialchars($a["doctor_name"]) ?></td></tr>
                        <tr><th>Specialty</th><td><?= htmlspecialchars($a["specialty"]) ?></td></tr>
                        <tr><th>Reason</th><td><?= htmlspecialchars($a["reason"]) ?></td></tr>
                        <tr>
                            <th>Date</th>
                            <td><?= $a["appt_date"] ? date("d M Y", strtotime($a["appt_date"])) : '<span class="text-muted">Awaiting assignment</span>' ?></td>
                        </tr>
                        <tr>
                            <th>Time</th>
                            <td><?= $a["appt_time"] ? date("h:i A", strtotime($a["appt_time"])) : '<span class="text-muted">Awaiting assignment</span>' ?></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td><span class="badge badge-<?= $a["status"] ?>"><?= $statusLabels[$a["status"]] ?></span></td>
                        </tr>
                    </table>

                    <?php if ($a["status"] !== "cancelled"): ?>
                        <a class="btn-cancel-appt"
                           href="cancel.php?id=<?= $a["appointment_id"] ?>"
                           onclick="return confirm('Cancel this appointment?');">Cancel Appointment</a>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
