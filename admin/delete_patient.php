<?php
session_start();

// Admin access guard
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error_message'] = "You do not have permission to perform this action.";
    header("Location: ../auth/login.php");
    exit();
}

// Check if the form was submitted via POST with a patient_id
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['patient_id'])) {
    
    require_once '../includes/db.php';
    
    $patientId = $_POST['patient_id'];

    // Note: Because of ON DELETE CASCADE, deleting a patient from the 'patients' table
    // will NOT delete their user account or appointments. This is usually desired behavior.
    // If you want to delete the user account too, you would need to perform a second query.
    
    $sql = "DELETE FROM patients WHERE patient_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $patientId);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Patient profile deleted successfully.";
    } else {
        $_SESSION['error_message'] = "Failed to delete patient profile.";
    }

    $stmt->close();
    $conn->close();

} else {
    $_SESSION['error_message'] = "Invalid request.";
}

// Redirect back to the management page
header("Location: index.php");
exit();
?>