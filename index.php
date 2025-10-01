<?php
// We need the session to check the user's role
// The header.php file already starts the session for us.
$pageTitle = "Hospital Management System";
include './includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style> 
        /* Card design */
        .card { border: 1px solid #e0e0e0; border-radius: 12px; background-color: #fff; transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease; }
        .card:hover { transform: translateY(-8px); box-shadow: 0 12px 28px rgba(0,0,0,0.15); border-color: #0d6efd; }
        /* Card icons */
        .card i { font-size: 2.5rem; color: #0d6efd; margin-bottom: 15px; }
        /* Hero button hover */
        .hero .btn { transition: all 0.3s ease; }
        .hero .btn:hover { transform: scale(1.05); }
        .overlay { z-index: 0; }
        .container.position-relative { z-index: 1; }

    </style>
</head>
<body>
<main class="container my-5">
    <section class="hero position-relative text-white text-center d-flex align-items-center mb-5" style="background: url('https://plus.unsplash.com/premium_photo-1729286323509-940c922ada56?q=80&w=1170&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D?auto=format&fit=crop&w=1600&q=80') center/cover no-repeat; height: 70vh; border-radius: 15px;">
        <div class="overlay position-absolute top-0 start-0 w-100 h-100" style="background: rgba(0, 0, 0, 0.6); border-radius: 15px;"></div>
        <div class="container position-relative z-1">
            <h1 class="display-5 fw-bold">Hospital Management System</h1>
            <p class="lead mb-4">Efficient. Reliable. Patient-Centered Healthcare at Your Fingertips.</p>
            
            <?php if (isset($_SESSION['loggedin']) && $_SESSION['role'] === 'patient'): ?>
                <a href="<?php echo BASE_URL; ?>patients/request_appointment.php" class="btn btn-outline-light btn-lg display-6">Book an Appointment</a>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>auth/login.php" class="btn btn-outline-light btn-lg">Book your appointment</a>
            <?php endif; ?>
        </div>
    </section>

     <?php
    // --- THIS IS THE CORRECT LOGIC ---
    // First, check ONLY for the 'admin' role
    if (isset($_SESSION['loggedin']) && $_SESSION['role'] === 'admin'):
    ?>
        <h2 class="text-center fw-bold mb-4">
            <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                        <span class="display-6">
                            Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?> ! ✨
                        </span>
            <?php endif; ?>
        </h2>
        <div class="row text-center">
            <div class="col-md-4 mb-4">
                <div class="card h-100"><div class="card-body d-flex flex-column"><i class="fa-solid fa-user-doctor"></i><h5 class="card-title fw-bold">Doctors</h5><p class="card-text">View, add, and manage doctors.</p><a href="<?php echo BASE_URL; ?>admin/manage_doctors.php" class="btn btn-primary mt-auto">Manage Doctors</a></div></div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100"><div class="card-body d-flex flex-column"><i class="fa-solid fa-bed-pulse"></i><h5 class="card-title fw-bold">Patients</h5><p class="card-text">Track patient records and details.</p><a href="<?php echo BASE_URL; ?>admin/manage_patients.php" class="btn btn-primary mt-auto">Manage Patients</a></div></div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100"><div class="card-body d-flex flex-column"><i class="fa-solid fa-calendar-check"></i><h5 class="card-title fw-bold">Appointments</h5><p class="card-text">Schedule and monitor appointments.</p><a href="<?php echo BASE_URL; ?>admin/manage_appointments.php" class="btn btn-primary mt-auto">Manage Appointments</a></div></div>
            </div>
        </div>

    <?php
    // THEN, if the user is not an admin, check if they are a 'patient'
    elseif (isset($_SESSION['loggedin']) && $_SESSION['role'] === 'patient'):
    ?>
        <h2 class="text-center fw-bold mb-4">
            <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                        <span class="display-6">
                            Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?> ! ✨
                        </span>
            <?php endif; ?>
        </h2>
        <div class="row text-center justify-content-center">
             <div class="col-md-4 mb-4">
                <div class="card h-100"><div class="card-body d-flex flex-column"><i class="fa-solid fa-calendar-plus"></i><h5 class="card-title fw-bold">Book an Appointment</h5><p class="card-text">Find a doctor and schedule your next visit with us.</p><a href="<?php echo BASE_URL; ?>patients/request_appointment.php" class="btn btn-primary mt-auto">Book Now</a></div></div>
            </div>
             <div class="col-md-4 mb-4">
                <div class="card h-100"><div class="card-body d-flex flex-column"><i class="fa-solid fa-file-medical"></i><h5 class="card-title fw-bold">My Appointments</h5><p class="card-text">View your upcoming and past appointment history.</p><a href="<?php echo BASE_URL; ?>patients/my_appointments.php" class="btn btn-primary mt-auto">View History</a></div></div>
            </div>
        </div>
    
    <?php
    endif; // End the if/elseif condition. Guests and Doctors will see no cards.
    ?>
    </main>
</body>
</html>

<?php
// Include footer
include './includes/footer.php';
?>