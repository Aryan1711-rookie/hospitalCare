<?php
session_start();
$pageTitle = "My Appointments";

// 1. Security Guard: Ensure the user is a logged-in patient
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'patient') {
    $_SESSION['error_message'] = "You must be logged in as a patient to view this page.";
    header("Location: ../auth/login.php");
    exit();
}

require_once '../includes/db.php';
$patientId = $_SESSION['user_id'];

// --- HANDLE CANCEL ACTION ---
if (isset($_GET['action']) && $_GET['action'] === 'cancel' && isset($_GET['id'])) {
    $appointmentId = $_GET['id'];

    // Security Check: Verify this appointment belongs to the logged-in user before canceling
    $verifySql = "SELECT appointment_id FROM appointments WHERE appointment_id = ? AND patient_id = ?";
    $stmt_verify = $conn->prepare($verifySql);
    $stmt_verify->bind_param("ii", $appointmentId, $patientId);
    $stmt_verify->execute();
    $stmt_verify->store_result();

    if ($stmt_verify->num_rows > 0) {
        // Ownership verified, proceed with cancellation
        $updateSql = "UPDATE appointments SET status = 'Canceled' WHERE appointment_id = ?";
        $stmt_update = $conn->prepare($updateSql);
        $stmt_update->bind_param("i", $appointmentId);
        if ($stmt_update->execute()) {
            $_SESSION['success_message'] = "Your appointment has been successfully canceled.";
        } else {
            $_SESSION['error_message'] = "Failed to cancel the appointment.";
        }
        $stmt_update->close();
    } else {
        // User is trying to cancel an appointment that isn't theirs
        $_SESSION['error_message'] = "Unauthorized action.";
    }
    $stmt_verify->close();
    header("Location: my_appointments.php");
    exit();
}
// --- END OF CANCEL LOGIC ---


// --- FETCH APPOINTMENT HISTORY ---
$sql = "SELECT 
            a.appointment_id, 
            d.full_name as doctor_name, 
            d.specialization,
            a.appointment_date, 
            a.reason,
            a.status
        FROM appointments a
        JOIN doctors d ON a.doctor_id = d.doctor_id
        WHERE a.patient_id = ?
        ORDER BY a.appointment_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $patientId);
$stmt->execute();
$result = $stmt->get_result();

include '../includes/header.php';
?>

<div class="container my-5">
    <h1 class="mb-4">My Appointment History</h1>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Doctor</th>
                            <th>Specialization</th>
                            <th>Date & Time</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($appt = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($appt['doctor_name']); ?></td>
                                    <td><?php echo htmlspecialchars($appt['specialization']); ?></td>
                                    <td>
                                        <?php if ($appt['status'] === 'Pending'): ?>
                                            <?php echo date('F j, Y', strtotime($appt['appointment_date'])); ?> (Time TBD)
                                        <?php else: ?>
                                            <?php echo date('F j, Y, g:i A', strtotime($appt['appointment_date'])); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($appt['reason']); ?></td>
                                    <td>
                                        <span class="badge <?php if ($appt['status'] == 'Confirmed') echo 'bg-success'; elseif ($appt['status'] == 'Pending') echo 'bg-warning text-dark'; else echo 'bg-danger'; ?>">
                                            <?php echo htmlspecialchars($appt['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        // Show Cancel button only for future appointments that are not already canceled
                                        $isFuture = strtotime($appt['appointment_date']) > time();
                                        if (($appt['status'] === 'Pending' || $appt['status'] === 'Confirmed') && $isFuture): 
                                        ?>
                                            <a href="my_appointments.php?action=cancel&id=<?php echo $appt['appointment_id']; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Are you sure you want to cancel this appointment?');">
                                               Cancel
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">You have no appointment history.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php 
$stmt->close();
$conn->close();
include '../includes/footer.php'; 
?>