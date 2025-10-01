<?php
require_once __DIR__ . '/config.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- LOGOUT LOGIC ---
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $_SESSION = [];
    session_destroy();
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}
// --- END LOGOUT LOGIC ---
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style> /* Navbar styling */ .navbar { background: #fff !important; box-shadow: 0 2px 10px rgba(0,0,0,0.1); } .navbar-brand { font-weight: 700; color: #0d6efd !important; display: flex; align-items: center; } .navbar-brand i { margin-right: 8px; color: #0d6efd; } .navbar-nav .nav-link { position: relative; font-weight: 500; color: #333 !important; margin-left: 15px; transition: color 0.3s ease; } .navbar-nav .nav-link::after { content: ''; position: absolute; width: 0; height: 2px; display: block; margin-top: 5px; left: 0; background: #0d6efd; transition: width 0.3s ease; } .navbar-nav .nav-link:hover { color: #0d6efd !important; } .navbar-nav .nav-link:hover::after { width: 100%; } .navbar-text { margin-left: 15px; color: #6c757d !important; } </style>

<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="<?php echo BASE_URL; ?>index.php">
            <i class="fa-solid fa-hospital"></i> HospitalCare
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>about.php">About</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>contact.php">Contact</a></li>

                <?php if (isset($_SESSION['role'])): ?>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>admin/index.php">Admin Dashboard</a></li>
                    <?php elseif ($_SESSION['role'] === 'patient'): ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>patients/request_appointment.php">Book Appointment</a></li>
                    
                    <?php elseif ($_SESSION['role'] === 'doctor'): ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>doctors/dashboard.php">Dashboard</a></li>
                    <?php endif; ?>

                    <li class="nav-item"><a class="nav-link" href="?action=logout">Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>auth/login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>auth/register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
