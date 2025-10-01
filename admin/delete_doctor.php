<?php
session_start();

// Admin access guard
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error_message'] = "You do not have permission to perform this action.";
    header("Location: ../auth/login.php");
    exit();
}

// Check if the form was submitted via POST with a doctor_id
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['doctor_id'])) {
    
    require_once '../includes/db.php';
    
    $doctorId = $_POST['doctor_id'];

    // Use a prepared statement to prevent SQL injection
    $sql = "DELETE FROM doctors WHERE doctor_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $doctorId);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Doctor deleted successfully.";
    } else {
        $_SESSION['error_message'] = "Failed to delete doctor. Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

} else {
    // If the form was not submitted correctly
    $_SESSION['error_message'] = "Invalid request.";
}

// Redirect back to the management page
header("Location: manage_doctors.php");
exit();
?>