<?php
session_start();
$pageTitle = "Create New Appointment";

// Admin Access Guard
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error_message'] = "You do not have permission to access this page.";
    header("Location: ../auth/login.php");
    exit();
}

require_once '../includes/db.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patientId = $_POST['patient_id'];
    $doctorId = $_POST['doctor_id'];
    $appointmentDate = $_POST['appointment_date'];
    $appointmentTime = $_POST['appointment_time'];
    $reason = $_POST['reason'];

    if (empty($patientId) || empty($doctorId) || empty($appointmentDate) || empty($appointmentTime)) {
        $error = "Patient, Doctor, Date, and Time are required fields.";
    } else {
        $fullDateTime = $appointmentDate . ' ' . $appointmentTime;

        if (strtotime($fullDateTime) < time()) {
        $error = "The selected appointment date and time cannot be in the past.";
        }
        // --- NEW: Check Doctor's Availability for the selected day and time ---
        $dayOfWeek = date('N', strtotime($appointmentDate));
        
        $avail_sql = "SELECT is_available, start_time, end_time FROM doctor_availability WHERE doctor_id = ? AND day_of_week = ?";
        $stmt_avail = $conn->prepare($avail_sql);
        $stmt_avail->bind_param("ii", $doctorId, $dayOfWeek);
        $stmt_avail->execute();
        $availability = $stmt_avail->get_result()->fetch_assoc();
        $stmt_avail->close();

        if (!$availability || $availability['is_available'] == 0) {
            $error = "The selected doctor is not available on this day of the week.";
        } elseif ($appointmentTime < $availability['start_time'] || $appointmentTime > $availability['end_time']) {
            $error = "The selected time is outside the doctor's working hours for that day (" . date('g:i A', strtotime($availability['start_time'])) . " to " . date('g:i A', strtotime($availability['end_time'])) . ").";
        } else {
            // --- If available, proceed with conflict check and booking ---
            $checkSql = "SELECT appointment_id FROM appointments 
                         WHERE appointment_date = ? AND status IN ('Pending', 'Confirmed') AND (patient_id = ? OR doctor_id = ?)";
            $stmt_check = $conn->prepare($checkSql);
            $stmt_check->bind_param("sii", $fullDateTime, $patientId, $doctorId);
            $stmt_check->execute();
            $stmt_check->store_result();

            if ($stmt_check->num_rows > 0) {
                $error = "A scheduling conflict exists. Either the patient or the doctor is already booked at this exact time.";
            } else {
                $status = 'Confirmed';
                $sql = "INSERT INTO appointments (patient_id, doctor_id, appointment_date, reason, status) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iisss", $patientId, $doctorId, $fullDateTime, $reason, $status);

                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Appointment created and confirmed successfully!";
                    header("Location: manage_appointments.php");
                    exit();
                } else {
                    $error = "Failed to create the appointment. Please try again.";
                }
                $stmt->close();
            }
            $stmt_check->close();
        }
    }
}

// 4. Fetch Data for Dropdowns
$patientsResult = $conn->query("SELECT user_id, name FROM users WHERE role = 'patient' ORDER BY name ASC");
$doctorsResult = $conn->query("SELECT doctor_id, full_name FROM doctors ORDER BY full_name ASC");


include '../includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header bg-primary text-white text-center rounded-top-4">
                    <h3 class="mb-0">Create Appointment (Admin)</h3>
                </div>
                <div class="card-body p-4">

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="create_appointment.php">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="patient_id" class="form-label">Select Patient</label>
                                <select name="patient_id" id="patient_id" class="form-select" required>
                                    <option value="" disabled selected>-- Choose a patient --</option>
                                    <?php mysqli_data_seek($patientsResult, 0); // Reset pointer for display ?>
                                    <?php while($patient = $patientsResult->fetch_assoc()): ?>
                                        <option value="<?php echo $patient['user_id']; ?>"><?php echo htmlspecialchars($patient['name']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="doctor_id" class="form-label">Select Doctor</label>
                                <select name="doctor_id" id="doctor_id" class="form-select" required>
                                    <option value="" disabled selected>-- Choose a doctor --</option>
                                    <?php mysqli_data_seek($doctorsResult, 0); // Reset pointer for display ?>
                                    <?php while($doctor = $doctorsResult->fetch_assoc()): ?>
                                        <option value="<?php echo $doctor['doctor_id']; ?>"><?php echo htmlspecialchars($doctor['full_name']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="appointment_date" class="form-label">Appointment Date</label>
                                <input type="date" class="form-control" name="appointment_date" id="appointment_date" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="appointment_time" class="form-label">Appointment Time</label>
                                <input type="time" class="form-control" name="appointment_time" id="appointment_time" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="reason" class="form-label">Reason for Visit</label>
                            <textarea name="reason" id="reason" class="form-control" rows="3" placeholder="Enter reason (optional)"></textarea>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                             <a href="manage_appointments.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create and Confirm Appointment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var today = new Date().toISOString().split('T')[0];
        document.getElementById("appointment_date").min = today;
    });
</script>

<?php 
$conn->close();
include '../includes/footer.php'; 
?>