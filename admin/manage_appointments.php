<?php
session_start();
$pageTitle = "Manage Appointments";
require_once '../includes/db.php';
// --- NEW: Include our email function at the top ---
require_once '../includes/send_email.php';

// --- HANDLE POST REQUEST (from Confirm Modal) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status']) && $_POST['status'] === 'Confirmed') {
    $appointmentId = $_POST['appointment_id'];
    $date = $_POST['appointment_date'];
    $time = $_POST['appointment_time'];
    $fullDateTime = $date . ' ' . $time;

    $sql = "UPDATE appointments SET status = 'Confirmed', appointment_date = ? WHERE appointment_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $fullDateTime, $appointmentId);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Appointment has been confirmed successfully.";

        // --- NEW: SEND CONFIRMATION EMAIL TO THE PATIENT ---
        // First, we need to get the patient's email and other details for the message
        $query = "SELECT u.name as patient_name, u.email as patient_email, d.full_name as doctor_name 
                  FROM appointments a 
                  JOIN users u ON a.patient_id = u.user_id 
                  JOIN doctors d ON a.doctor_id = d.doctor_id
                  WHERE a.appointment_id = ?";
        
        $stmt_email = $conn->prepare($query);
        $stmt_email->bind_param("i", $appointmentId);
        $stmt_email->execute();
        $email_details = $stmt_email->get_result()->fetch_assoc();

        if ($email_details) {
            $patientEmail = $email_details['patient_email'];
            
            // Compose the email subject and body
            $subject = "Your Appointment is Confirmed - HospitalCare";
            $body = "<h3>Appointment Confirmation</h3>" .
                    "<p>Dear " . htmlspecialchars($email_details['patient_name']) . ",</p>" .
                    "<p>This is a confirmation that your appointment has been scheduled by our administration. Please see the details below:</p>" .
                    "<ul>" .
                    "<li><b>Hospital Name:</b> HospitalCare</li>" .
                    "<li><b>Doctor:</b> " . htmlspecialchars($email_details['doctor_name']) . "</li>" .
                    "<li><b>Date & Time:</b> " . date('F j, Y, g:i A', strtotime($fullDateTime)) . "</li>" .
                    "</ul>" .
                    "<p>We look forward to seeing you. If you need to cancel, please log in to your portal or contact us directly.</p>" .
                    "<p>Sincerely,<br>The HospitalCare Team</p>";
            
            // Call our reusable email function
            sendEmail($patientEmail, $subject, $body);
        }
        $stmt_email->close();
        // --- END: SEND CONFIRMATION EMAIL ---

    } else { 
        $_SESSION['error_message'] = "Failed to confirm the appointment."; 
    }
    $stmt->close();
    header("Location: manage_appointments.php");
    exit();
}

// --- HANDLE GET REQUEST (from Cancel Link) ---
if (isset($_GET['action']) && $_GET['action'] === 'cancel' && isset($_GET['id'])) {
    $appointmentId = $_GET['id'];

    // First, get the details needed for the email BEFORE updating the database
    $query = "SELECT u.name as patient_name, u.email as patient_email, d.full_name as doctor_name, a.appointment_date
              FROM appointments a 
              JOIN users u ON a.patient_id = u.user_id 
              JOIN doctors d ON a.doctor_id = d.doctor_id
              WHERE a.appointment_id = ?";
    $stmt_email = $conn->prepare($query);
    $stmt_email->bind_param("i", $appointmentId);
    $stmt_email->execute();
    $email_details = $stmt_email->get_result()->fetch_assoc();
    $stmt_email->close();

    // Now, update the status in the database
    $sql = "UPDATE appointments SET status = 'Canceled' WHERE appointment_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $appointmentId);
    
    if ($stmt->execute()) { 
        $_SESSION['success_message'] = "Appointment has been canceled."; 

        // Send the cancellation email
        if ($email_details) {
            $patientEmail = $email_details['patient_email'];
            $subject = "Appointment Cancellation Notice - HospitalCare";
            $body = "<h3>Appointment Cancellation Notice</h3>" .
                    "<p>Dear " . htmlspecialchars($email_details['patient_name']) . ",</p>" .
                    "<p>We are writing to inform you that your appointment scheduled with <b>" . htmlspecialchars($email_details['doctor_name']) . "</b> on <b>" . date('F j, Y', strtotime($email_details['appointment_date'])) . "</b> has been canceled by our administration.</p>" .
                    "<p>We apologize for any inconvenience this may cause. Please feel free to book a new appointment through our portal or contact our office for assistance.</p>" .
                    "<p>Sincerely,<br>The HospitalCare Team</p>";
            
            sendEmail($patientEmail, $subject, $body);
        }
    } else { 
        $_SESSION['error_message'] = "Failed to cancel the appointment."; 
    }
    $stmt->close();
    header("Location: manage_appointments.php");
    exit();
}
// --- Admin access guard and the rest of the file remains the same ---
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error_message'] = "You do not have permission to access this page.";
    header("Location: ../auth/login.php");
    exit();
}
$sql = "SELECT a.appointment_id, p.name as patient_name, d.full_name as doctor_name, a.appointment_date, a.status
        FROM appointments a
        JOIN users p ON a.patient_id = p.user_id
        JOIN doctors d ON a.doctor_id = d.doctor_id
        ORDER BY a.status = 'Pending' DESC, a.appointment_date ASC";
$appointments = $conn->query($sql);
include '../includes/header.php';
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Manage Appointments</h1>
        <a href="create_appointment.php" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Create New Appointment</a>
    </div>
    
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>
     <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>

    <table class="table table-hover align-middle mt-4">
        <thead class="table-light">
            <tr>
                <th>Patient</th>
                <th>Doctor</th>
                <th>Date & Time</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($appt = $appointments->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($appt['patient_name']); ?></td>
                    <td><?php echo htmlspecialchars($appt['doctor_name']); ?></td>
                    <td>
                        <?php 
                            if ($appt['status'] === 'Confirmed') {
                                echo date('F j, Y, g:i A', strtotime($appt['appointment_date']));
                            } else {
                                echo date('F j, Y', strtotime($appt['appointment_date'])) . " (Time TBD)";
                            }
                        ?>
                    </td>
                    <td>
                        <span class="badge <?php if ($appt['status'] == 'Confirmed') echo 'bg-success'; elseif ($appt['status'] == 'Pending') echo 'bg-warning text-dark'; else echo 'bg-danger'; ?>">
                            <?php echo htmlspecialchars($appt['status']); ?>
                        </span>
                    </td>
                    <td>
                        <?php // The "Confirm" button should ONLY appear for Pending appointments ?>
                        <?php if ($appt['status'] === 'Pending'): ?>
                            <button class="btn btn-sm btn-success" 
                                data-bs-toggle="modal" 
                                data-bs-target="#confirmAppointmentModal"
                                data-id="<?php echo $appt['appointment_id']; ?>"
                                data-date="<?php echo date('Y-m-d', strtotime($appt['appointment_date'])); ?>">
                                Confirm
                            </button>
                        <?php endif; ?>

                        <?php // The "Cancel" button should appear for BOTH Pending and Confirmed appointments ?>
                        <?php if ($appt['status'] === 'Pending' || $appt['status'] === 'Confirmed'): ?>
                            <a href="manage_appointments.php?action=cancel&id=<?php echo $appt['appointment_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to cancel this appointment?');">
                                Cancel
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="confirmAppointmentModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="manage_appointments.php" method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmModalLabel">Confirm Appointment and Set Time</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="appointment_id" id="confirm-id">
        <input type="hidden" name="status" value="Confirmed">
        <div class="mb-3">
            <label for="confirm-date" class="form-label">Appointment Date</label>
            <input type="date" class="form-control" name="appointment_date" id="confirm-date" readonly>
        </div>
        <div class="mb-3">
            <label for="appointment_time" class="form-label">Set Appointment Time</label>
            <input type="time" class="form-control" name="appointment_time" id="appointment_time" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-success">Confirm Appointment</button>
      </div>
    </form>
  </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const confirmModal = document.getElementById('confirmAppointmentModal');
        confirmModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            confirmModal.querySelector('#confirm-id').value = button.getAttribute('data-id');
            confirmModal.querySelector('#confirm-date').value = button.getAttribute('data-date');
        });
    });
</script>

<?php
$conn->close();
include '../includes/footer.php'; 
?>