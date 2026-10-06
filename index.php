<?php
require "config.php";
require "auth.php"; // redirects to login.php if not logged in

// ---- Fetch doctors (used for the dropdown AND the doctor list) ----
$doctorsResult = $conn->query("SELECT * FROM doctors ORDER BY name");
$doctors = [];
while ($row = $doctorsResult->fetch_assoc()) {
    $doctors[] = $row;
}

// ---- Fetch all appointments, joined across 3 tables ----
$appointments = $conn->query("
    SELECT a.appointment_id, a.appt_date, a.appt_time, a.reason,
           p.name AS patient_name, d.name AS doctor_name
    FROM appointments a
    JOIN patients p ON a.patient_id = p.patient_id
    JOIN doctors  d ON a.doctor_id  = d.doctor_id
    ORDER BY a.appt_date, a.appt_time
");

// ---- Stats (aggregate SQL queries) ----
$totalCount  = $conn->query("SELECT COUNT(*) AS c FROM appointments")->fetch_assoc()["c"];
$todayCount  = $conn->query("SELECT COUNT(*) AS c FROM appointments WHERE appt_date = CURDATE()")->fetch_assoc()["c"];
$doctorCount = count($doctors);

$today = date("Y-m-d");
?>
<!DOCTYPE html>
<html>
<head>
    <title>MedBook - Appointment Booking</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="topbar">
        <h1>MedBook</h1>
        <div>
            <span style="margin-right:14px;">Hi, <?= htmlspecialchars($_SESSION["username"]) ?></span>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <?php if (isset($_GET["success"])): ?>
            <div class="alert alert-success">Appointment booked successfully.</div>
        <?php elseif (isset($_GET["error"])): ?>
            <div class="alert alert-error"><?= htmlspecialchars($_GET["error"]) ?></div>
        <?php endif; ?>

        <div class="stats">
            <div class="stat-card">
                <div class="num"><?= $totalCount ?></div>
                <div class="label">Total Appointments</div>
            </div>
            <div class="stat-card">
                <div class="num"><?= $todayCount ?></div>
                <div class="label">Today</div>
            </div>
            <div class="stat-card">
                <div class="num"><?= $doctorCount ?></div>
                <div class="label">Doctors Available</div>
            </div>
        </div>

        <div class="grid">
            <div class="card">
                <h2>Book an Appointment</h2>
                <form action="book.php" method="POST">
                    <div class="field">
                        <label>Patient Name</label>
                        <input type="text" name="patient_name" required>
                    </div>
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
                        <label>Date</label>
                        <input type="date" name="appt_date" required>
                    </div>
                    <div class="field">
                        <label>Time</label>
                        <input type="time" name="appt_time" required>
                    </div>
                    <div class="field">
                        <label>Reason</label>
                        <textarea name="reason" rows="2"></textarea>
                    </div>
                    <button type="submit" class="btn btn-full">Book Appointment</button>
                </form>

                <h2 style="margin-top:24px;">Our Doctors</h2>
                <ul class="doctor-list">
                    <?php foreach ($doctors as $doc): ?>
                        <li class="doctor-item">
                            <?= htmlspecialchars($doc["name"]) ?>
                            <div class="spec"><?= htmlspecialchars($doc["specialty"]) ?></div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="card">
                <h2>All Appointments</h2>
                <table>
                    <tr>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                    <?php while ($a = $appointments->fetch_assoc()): ?>
                        <?php $isUpcoming = $a["appt_date"] >= $today; ?>
                        <tr>
                            <td><?= htmlspecialchars($a["patient_name"]) ?></td>
                            <td><?= htmlspecialchars($a["doctor_name"]) ?></td>
                            <td><?= $a["appt_date"] ?></td>
                            <td><?= $a["appt_time"] ?></td>
                            <td>
                                <span class="badge <?= $isUpcoming ? "badge-upcoming" : "badge-past" ?>">
                                    <?= $isUpcoming ? "Upcoming" : "Past" ?>
                                </span>
                            </td>
                            <td>
                                <a class="cancel-link" href="cancel.php?id=<?= $a["appointment_id"] ?>"
                                   onclick="return confirm('Cancel this appointment?');">Cancel</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
