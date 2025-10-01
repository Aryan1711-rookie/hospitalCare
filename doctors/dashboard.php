<?php
session_start();
$pageTitle = "Doctor Dashboard";

// 1. Security Guard: Ensure the user is a logged-in doctor
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'doctor') {
    $_SESSION['error_message'] = "You do not have permission to access this page.";
    header("Location: ../auth/login.php");
    exit();
}

require_once '../includes/db.php';
$loggedInUserId = $_SESSION['user_id'];
$doctorAppointments = [];
$doctorName = '';
$error = ''; // Variable to hold potential errors

// 2. Fetch the doctor's specific ID from the 'doctors' table using their user_id
$stmt_doc = $conn->prepare("SELECT doctor_id, full_name FROM doctors WHERE user_id = ?");
$stmt_doc->bind_param("i", $loggedInUserId);
$stmt_doc->execute();
$result_doc = $stmt_doc->get_result();

if ($result_doc->num_rows > 0) {
    $doctorData = $result_doc->fetch_assoc();
    $doctorId = $doctorData['doctor_id'];
    $doctorName = $doctorData['full_name'];

    // 3. Fetch all UPCOMING, CONFIRMED appointments for THIS doctor
    // UPDATED: Changed CURDATE() to NOW() to be more precise for today's appointments
    $sql = "SELECT a.appointment_date, a.reason, u.name as patient_name, u.email as patient_email
            FROM appointments a
            JOIN users u ON a.patient_id = u.user_id
            WHERE a.doctor_id = ? 
            AND a.status = 'Confirmed'
            AND a.appointment_date >= NOW() 
            ORDER BY a.appointment_date ASC";
    
    $stmt_appt = $conn->prepare($sql);
    $stmt_appt->bind_param("i", $doctorId);
    $stmt_appt->execute();
    $result_appt = $stmt_appt->get_result();
    while ($row = $result_appt->fetch_assoc()) {
        $doctorAppointments[] = $row;
    }
    $stmt_appt->close();
} else {
    // --- NEW: Handle the case where the doctor profile doesn't exist ---
    $error = "Your doctor profile has not been set up by an administrator. Please contact support to have your professional details configured.";
}
$stmt_doc->close();

include '../includes/header.php';
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="display-5">Doctor Dashboard</h1>
            <?php if ($doctorName): ?>
                <p class="lead">Welcome, <?php echo htmlspecialchars($doctorName); ?>.</p>
            <?php endif; ?>
        </div>
        <a href="availability.php" class="btn btn-outline-primary">
            <i class="bi bi-calendar-week"></i> Set My Availability
        </a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-warning mt-4"><?php echo $error; ?></div>
    <?php else: ?>
        <div class="card shadow-sm mt-4">
            <div class="card-header">
                <h4 class="mb-0">Your Upcoming Confirmed Appointments</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>Patient Name</th>
                                <th>Patient Email</th>
                                <th>Reason for Visit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($doctorAppointments)): ?>
                                <?php foreach ($doctorAppointments as $appt): ?>
                                    <tr>
                                        <td><?php echo date('F j, Y, g:i A', strtotime($appt['appointment_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($appt['patient_name']); ?></td>
                                        <td><a href="mailto:<?php echo htmlspecialchars($appt['patient_email']); ?>"><?php echo htmlspecialchars($appt['patient_email']); ?></a></td>
                                        <td><?php echo htmlspecialchars($appt['reason']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">You have no upcoming confirmed appointments.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php 
$conn->close();
include '../includes/footer.php'; 
?>