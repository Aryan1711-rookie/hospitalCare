<?php
session_start();
$pageTitle = "My Availability";

// 1. Security Guard: Ensure the user is a logged-in doctor
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'doctor') {
    $_SESSION['error_message'] = "You do not have permission to access this page.";
    header("Location: ../auth/login.php");
    exit();
}

require_once '../includes/db.php';
$loggedInUserId = $_SESSION['user_id'];

// First, get the doctor_id from the user_id
$stmt_doc = $conn->prepare("SELECT doctor_id FROM doctors WHERE user_id = ?");
$stmt_doc->bind_param("i", $loggedInUserId);
$stmt_doc->execute();
$result_doc = $stmt_doc->get_result();
if ($result_doc->num_rows === 0) {
    die("Error: Doctor profile not found for this user."); // Or handle more gracefully
}
$doctorData = $result_doc->fetch_assoc();
$doctorId = $doctorData['doctor_id'];
$stmt_doc->close();


// 2. Handle Form Submission to SAVE the schedule
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // We will update all 7 days in a loop
    $sql = "INSERT INTO doctor_availability (doctor_id, day_of_week, start_time, end_time, is_available)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            start_time = VALUES(start_time), end_time = VALUES(end_time), is_available = VALUES(is_available)";
    
    $stmt_save = $conn->prepare($sql);

    for ($i = 1; $i <= 7; $i++) {
        $isAvailable = isset($_POST['availability'][$i]['is_available']) ? 1 : 0;
        $startTime = $_POST['availability'][$i]['start_time'] ?: NULL;
        $endTime = $_POST['availability'][$i]['end_time'] ?: NULL;
        
        $stmt_save->bind_param("iissi", $doctorId, $i, $startTime, $endTime, $isAvailable);
        $stmt_save->execute();
    }
    $stmt_save->close();
    $_SESSION['success_message'] = "Your weekly schedule has been updated successfully!";
    header("Location: availability.php");
    exit();
}


// 3. Fetch current availability to display in the form
$currentSchedule = [];
$sql_fetch = "SELECT day_of_week, start_time, end_time, is_available FROM doctor_availability WHERE doctor_id = ?";
$stmt_fetch = $conn->prepare($sql_fetch);
$stmt_fetch->bind_param("i", $doctorId);
$stmt_fetch->execute();
$result_fetch = $stmt_fetch->get_result();
while($row = $result_fetch->fetch_assoc()) {
    $currentSchedule[$row['day_of_week']] = $row;
}
$stmt_fetch->close();

$daysOfWeek = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'];

include '../includes/header.php';
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4 ">
        <h3>Set Your Weekly Availability</h3>
        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="availability.php">
                <?php foreach ($daysOfWeek as $dayIndex => $dayName): 
                    // Get the saved data for this day, or set defaults
                    $isAvailable = $currentSchedule[$dayIndex]['is_available'] ?? false;
                    $startTime = $currentSchedule[$dayIndex]['start_time'] ?? '09:00';
                    $endTime = $currentSchedule[$dayIndex]['end_time'] ?? '17:00';
                ?>
                    <div class="row align-items-center border-bottom py-3">
                        <div class="col-md-2">
                            <strong class="fs-5"><?php echo $dayName; ?></strong>
                        </div>
                        <div class="col-md-2">
                            <div class="form-check form-switch fs-5">
                                <input class="form-check-input" type="checkbox" name="availability[<?php echo $dayIndex; ?>][is_available]" id="available-<?php echo $dayIndex; ?>" value="1" <?php echo $isAvailable ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="available-<?php echo $dayIndex; ?>">Available</label>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="row time-inputs" id="time-inputs-<?php echo $dayIndex; ?>" style="<?php echo !$isAvailable ? 'display: none;' : ''; ?>">
                                <div class="col-auto"><label class="col-form-label">From</label></div>
                                <div class="col-auto"><input type="time" name="availability[<?php echo $dayIndex; ?>][start_time]" class="form-control" value="<?php echo date('H:i', strtotime($startTime)); ?>"></div>
                                <div class="col-auto"><label class="col-form-label">To</label></div>
                                <div class="col-auto"><input type="time" name="availability[<?php echo $dayIndex; ?>][end_time]" class="form-control" value="<?php echo date('H:i', strtotime($endTime)); ?>"></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">Save Schedule</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // JavaScript to show/hide time inputs based on the checkbox
    document.addEventListener('DOMContentLoaded', function() {
        <?php foreach ($daysOfWeek as $dayIndex => $dayName): ?>
            const checkbox<?php echo $dayIndex; ?> = document.getElementById('available-<?php echo $dayIndex; ?>');
            const timeInputs<?php echo $dayIndex; ?> = document.getElementById('time-inputs-<?php echo $dayIndex; ?>');
            
            checkbox<?php echo $dayIndex; ?>.addEventListener('change', function() {
                timeInputs<?php echo $dayIndex; ?>.style.display = this.checked ? 'flex' : 'none';
            });
        <?php endforeach; ?>
    });
</script>

<?php 
$conn->close();
include '../includes/footer.php'; 
?>