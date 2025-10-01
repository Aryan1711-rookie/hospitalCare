<?php
// This script will not render any HTML. It only returns data.
header('Content-Type: application/json');

require_once '../includes/db.php';

// Default to an empty array
$doctors = [];

// Check if a specialization was provided in the URL (e.g., ?specialization=Cardiology)
if (isset($_GET['specialization'])) {
    $specialization = $_GET['specialization'];

    $sql = "SELECT doctor_id, full_name FROM doctors WHERE specialization = ? ORDER BY full_name";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $specialization);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $doctors[] = $row;
    }
    $stmt->close();
}

$conn->close();

// Echo the data as a JSON string
echo json_encode($doctors);
?>