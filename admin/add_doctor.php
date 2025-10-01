<?php
// 1. Start the session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 2. Admin Access Guard
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error_message'] = "You do not have permission to access this page.";
    header("Location: ../auth/login.php");
    exit();
}

// 3. Include the database connection
require_once '../includes/db.php';

// 4. Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $fullName = $_POST['full_name'];
    $specialization = $_POST['specialization'];
    $contactNumber = $_POST['contact_number'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Basic validation
    if (empty($fullName) || empty($specialization) || empty($contactNumber) || empty($email) || empty($password)) {
        $_SESSION['error_message'] = "All fields are required.";
    } else {
        // Securely hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $role = 'doctor'; // Hardcode the role for new doctors

        // Start a database transaction
        $conn->begin_transaction();

        try {
            // Step 1: Insert into the 'users' table
            $sql1 = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
            $stmt1 = $conn->prepare($sql1);
            $stmt1->bind_param("ssss", $fullName, $email, $hashedPassword, $role);
            $stmt1->execute();

            // Step 2: Get the ID of the user we just created
            $newUserId = $conn->insert_id;

            // Step 3: Insert into the 'doctors' table
            $sql2 = "INSERT INTO doctors (user_id, full_name, specialization, contact_number, email) VALUES (?, ?, ?, ?, ?)";
            $stmt2 = $conn->prepare($sql2);
            $stmt2->bind_param("issss", $newUserId, $fullName, $specialization, $contactNumber, $email);
            $stmt2->execute();

            // If both queries were successful, commit the transaction
            $conn->commit();
            $_SESSION['success_message'] = "Doctor account and profile created successfully!";
            header('Location: manage_doctors.php');
            exit();

        } catch (mysqli_sql_exception $exception) {
            // If anything went wrong, roll back the transaction
            $conn->rollback();
            
            // Check for duplicate entry error (error code 1062)
            if ($conn->errno === 1062) {
                $_SESSION['error_message'] = "Failed to create doctor. An account with this email already exists.";
            } else {
                $_SESSION['error_message'] = "A database error occurred: " . $exception->getMessage();
            }
        }
    }
    // Redirect back to the form if there was a validation error
    header("Location: add_doctor.php");
    exit();
}

// Set the page title and include header
$pageTitle = "Add Doctor";
include '../includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header bg-primary text-white text-center rounded-top-4">
                    <h3 class="mb-0">Add New Doctor</h3>
                </div>
                <div class="card-body p-4">

                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger">
                            <?php echo $_SESSION['error_message']; ?>
                        </div>
                        <?php unset($_SESSION['error_message']); ?>
                    <?php endif; ?>

                    <form action="add_doctor.php" method="POST">
                        
                        <div class="mb-3">
                            <label for="fullName" class="form-label">Doctor's Full Name</label>
                            <input type="text" name="full_name" id="fullName" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address (for login)</label>
                            <input type="email" name="email" id="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Set Initial Password</label>
                            <input type="password" name="password" id="password" class="form-control" required>
                        </div>
                        <hr class="my-4">
                        <div class="mb-3">
                            <label for="specialization" class="form-label">Specialization</label>
                            <input type="text" name="specialization" id="specialization" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="contactNumber" class="form-label">Phone Number</label>
                            <input type="text" name="contact_number" id="contactNumber" class="form-control" required>
                        </div>
                        
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-success btn-lg rounded-3">
                                <i class="bi bi-person-plus"></i> Create Doctor Account
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$conn->close();
include '../includes/footer.php';
?>