<?php
// 1. Start session and check if the user is logged in
session_start();
if (!isset($_SESSION['loggedin'])) {
    $_SESSION['error_message'] = "You must be logged in to add your patient details.";
    header("Location: ../auth/login.php");
    exit();
}

// 2. Include database and header
require_once '../includes/db.php';
$pageTitle = "Add Your Patient Details";
include '../includes/header.php';

// 3. Get the logged-in user's ID
$userId = $_SESSION['user_id'];

// 4. Check if a patient profile already exists for this user
$stmt_check = $conn->prepare("SELECT patient_id FROM patients WHERE user_id = ?");
$stmt_check->bind_param("i", $userId);
$stmt_check->execute();
$stmt_check->store_result();
$profileExists = ($stmt_check->num_rows > 0);
$stmt_check->close();

// 5. Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$profileExists) {
    // Retrieve data from the form
    $dateOfBirth = $_POST['date_of_birth'];
    $gender = $_POST['gender'];
    $contactNumber = $_POST['contact_number'];
    $address = $_POST['address'];

    // Prepare the INSERT statement
    $sql = "INSERT INTO patients (user_id, date_of_birth, gender, contact_number, address) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issss", $userId, $dateOfBirth, $gender, $contactNumber, $address);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Your patient profile has been created successfully!";
        header("Location: ../index.php"); // Redirect to homepage or a dashboard
        exit();
    } else {
        $error = "Failed to create profile. Please try again.";
    }
    $stmt->close();
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-md-9">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header bg-success text-white text-center rounded-top-4">
                    <h3 class="mb-0">Create Your Patient Profile</h3>
                </div>
                <div class="card-body p-4">

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <?php if ($profileExists): ?>
                        <div class="alert alert-info text-center">
                            <h4 class="alert-heading">Profile Already Exists!</h4>
                            <p>Your patient profile has already been created. You can manage your appointments or contact us for any changes.</p>
                            <a href="../index.php" class="btn btn-primary">Go to Homepage</a>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>! Please complete your profile below.</p>
                        <form method="POST" action="add.php">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="date_of_birth" class="form-label">Date of Birth</label>
                                    <input type="date" class="form-control" name="date_of_birth" id="date_of_birth" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="gender" class="form-label">Gender</label>
                                    <select name="gender" id="gender" class="form-select" required>
                                        <option value="" disabled selected>-- Select Gender --</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="contact_number" class="form-label">Contact Number</label>
                                <input type="tel" class="form-control" name="contact_number" id="contact_number" placeholder="Enter your phone number" required>
                            </div>

                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <textarea name="address" id="address" class="form-control" rows="3" placeholder="Enter your full address" required></textarea>
                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-success btn-lg">Save Profile</button>
                            </div>
                        </form>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$conn->close();
include '../includes/footer.php'; 
?>