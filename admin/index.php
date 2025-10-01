<?php
// 1. Start the session and implement the Admin Guard
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error_message'] = "You do not have permission to access this page.";
    header("Location: ../auth/login.php");
    exit();
}

// 2. Include the database connection
require_once '../includes/db.php';

// 3. Fetch statistics for the dashboard
// Total Doctors
$resultDoctors = $conn->query("SELECT COUNT(*) as total_doctors FROM doctors");
$totalDoctors = $resultDoctors->fetch_assoc()['total_doctors'];

// Total Patients (based on 'patient' role in users table)
$resultPatients = $conn->query("SELECT COUNT(*) as total_patients FROM users WHERE role = 'patient'");
$totalPatients = $resultPatients->fetch_assoc()['total_patients'];

// Pending Appointments
$resultAppointments = $conn->query("SELECT COUNT(*) as pending_appointments FROM appointments WHERE status = 'Pending'");
$pendingAppointments = $resultAppointments->fetch_assoc()['pending_appointments'];

// Set the page title and include the header
$pageTitle = "Admin Dashboard";
include '../includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-12 mb-4">
            <h1 class="display-5">Admin Dashboard</h1>
            <p class="lead">Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card text-white bg-primary shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="card-title"><?php echo $totalDoctors; ?></h3>
                            <p class="card-text mb-0">Total Doctors</p>
                        </div>
                        <i class="bi bi-person-badge" style="font-size: 3rem;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card text-white bg-success shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="card-title"><?php echo $totalPatients; ?></h3>
                            <p class="card-text mb-0">Total Patients</p>
                        </div>
                        <i class="bi bi-people-fill" style="font-size: 3rem;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card text-dark bg-warning shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="card-title"><?php echo $pendingAppointments; ?></h3>
                            <p class="card-text mb-0">Pending Appointments</p>
                        </div>
                        <i class="bi bi-clock-history" style="font-size: 3rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <h2 class="mb-3">Management Sections</h2>
        </div>
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center d-flex flex-column">
                    <i class="bi bi-person-lines-fill" style="font-size: 3rem; color: #0d6efd;"></i>
                    <h5 class="card-title mt-3">Manage Doctors</h5>
                    <p class="card-text">View, add, edit, and remove doctor profiles.</p>
                    <div class="mt-auto">
                        <a href="manage_doctors.php" class="btn btn-primary">Manage Doctors</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center d-flex flex-column">
                    <i class="bi bi-person-check-fill" style="font-size: 3rem; color: #198754;"></i>
                    <h5 class="card-title mt-3">Manage Patients</h5>
                    <p class="card-text">View and manage registered patient records.</p>
                    <div class="mt-auto">
                        <a href="manage_patients.php" class="btn btn-success">Manage Patients</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center d-flex flex-column">
                    <i class="bi bi-calendar2-check" style="font-size: 3rem; color: #ffc107;"></i>
                    <h5 class="card-title mt-3">Manage Appointments</h5>
                    <p class="card-text">Confirm or cancel patient appointment requests.</p>
                    <div class="mt-auto">
                        <a href="manage_appointments.php" class="btn btn-warning">Manage Appointments</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
// Close the database connection and include the footer
$conn->close();
include '../includes/footer.php'; 
?>