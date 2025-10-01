<?php
// 1. Start session and check if the user is logged in
session_start();
if (!isset($_SESSION['loggedin'])) {
    $_SESSION['error_message'] = "You must be logged in to request an appointment.";
    header("Location: ../auth/login.php");
    exit();
}

// 2. Include database
require_once '../includes/db.php';
$userId = $_SESSION['user_id'];
$error = '';

// Check if the user has a patient profile... (this logic is unchanged)
$stmt_check = $conn->prepare("SELECT patient_id FROM patients WHERE user_id = ?");
$stmt_check->bind_param("i", $userId);
$stmt_check->execute();
$stmt_check->store_result();
if ($stmt_check->num_rows === 0) {
    $_SESSION['error_message'] = "Please complete your patient profile before booking an appointment.";
    header("Location: add.php");
    exit();
}
$stmt_check->close();

// 4. Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctorId = $_POST['doctor_id'];
    $appointmentDate = $_POST['appointment_date'];
    $reason = $_POST['reason'];

    if (empty($doctorId) || empty($appointmentDate)) {
        $error = "Please select a doctor and a date.";
    } else {
        // --- NEW: Check Doctor's Availability for the selected day ---
        $dayOfWeek = date('N', strtotime($appointmentDate)); // 'N' gets day of week: 1 (Mon) to 7 (Sun)
        
        $avail_sql = "SELECT is_available FROM doctor_availability WHERE doctor_id = ? AND day_of_week = ?";
        $stmt_avail = $conn->prepare($avail_sql);
        $stmt_avail->bind_param("ii", $doctorId, $dayOfWeek);
        $stmt_avail->execute();
        $avail_result = $stmt_avail->get_result()->fetch_assoc();
        $stmt_avail->close();

        if (!$avail_result || $avail_result['is_available'] == 0) {
            $error = "Sorry, the selected doctor is not available on that day. Please choose a different date.";
        } else {
            // --- If available, proceed with existing logic ---
            $patientId = $_SESSION['user_id'];
            $status = 'Pending';
            $sql = "INSERT INTO appointments (patient_id, doctor_id, appointment_date, reason, status) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iisss", $patientId, $doctorId, $appointmentDate, $reason, $status);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Your appointment for ".date('F j, Y', strtotime($appointmentDate))." has been requested!";
                header("Location: ../index.php");
                exit();
            } else {
                $error = "Failed to request appointment. Please try again.";
            }
            $stmt->close();
        }
    }
}

$specializationsResult = $conn->query("SELECT DISTINCT specialization FROM doctors ORDER BY specialization ASC");
$pageTitle = "Request an Appointment";
include '../includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-md-9">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header bg-primary text-white text-center rounded-top-4">
                    <h3 class="mb-0">Request an Appointment Date</h3>
                </div>
                <div class="card-body p-4">

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="request_appointment.php">
                        <div class="mb-3">
                            <label for="specialization" class="form-label">Select Department / Specialization</label>
                            <select name="specialization" id="specialization" class="form-select" required>
                                <option value="" disabled selected>-- First, choose a department --</option>
                                <?php if ($specializationsResult->num_rows > 0): ?>
                                    <?php while($spec = $specializationsResult->fetch_assoc()): ?>
                                        <option value="<?php echo htmlspecialchars($spec['specialization']); ?>">
                                            <?php echo htmlspecialchars($spec['specialization']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="doctor_id" class="form-label">Select Doctor</label>
                            <select name="doctor_id" id="doctor_id" class="form-select" required disabled>
                                <option value="" disabled selected>-- Select a department first --</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="appointment_date" class="form-label">Requested Appointment Date</label>
                            <input type="date" class="form-control" name="appointment_date" id="appointment_date" required>
                        </div>

                        <div class="mb-3">
                            <label for="reason" class="form-label">Reason for Visit / Condition</label>
                            <textarea name="reason" id="reason" class="form-control" rows="4" placeholder="Briefly describe your symptoms or reason for visit." required></textarea>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">Request Appointment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // This script for the dynamic doctor dropdown remains the same
    const specializationSelect = document.getElementById("specialization");
    const doctorSelect = document.getElementById("doctor_id");
    specializationSelect.addEventListener("change", function() {
        const selectedSpecialization = this.value;
        doctorSelect.innerHTML = '<option value="" disabled selected>Loading doctors...</option>';
        doctorSelect.disabled = true;
        if (selectedSpecialization) {
            fetch(`get_doctors.php?specialization=${encodeURIComponent(selectedSpecialization)}`)
                .then(response => response.json())
                .then(doctors => {
                    doctorSelect.innerHTML = '<option value="" disabled selected>-- Select a Doctor --</option>';
                    if (doctors.length > 0) {
                        doctors.forEach(doctor => {
                            const option = document.createElement("option");
                            option.value = doctor.doctor_id;
                            option.textContent = doctor.full_name;
                            doctorSelect.appendChild(option);
                        });
                        doctorSelect.disabled = false;
                    } else {
                        doctorSelect.innerHTML = '<option value="" disabled selected>No doctors found</option>';
                    }
                });
        }
    });

    // UPDATED: JavaScript for the date picker
    const datePicker = document.getElementById("appointment_date");
    if (datePicker) {
        // Set the minimum value to today's date
        var today = new Date().toISOString().split('T')[0];
        datePicker.min = today;
    }
});
</script>

<?php 
$conn->close();
include '../includes/footer.php'; 
?>