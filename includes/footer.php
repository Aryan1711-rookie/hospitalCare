<?php
require_once __DIR__ . '/config.php';
if(session_status() == PHP_SESSION_NONE){
    session_start();
}
?>
<!-- Footer -->
<footer class="bg-dark text-white pt-5 pb-3 mt-5">
    <div class="container">
        <div class="row">
            <!-- About -->
            <div class="col-md-4 mb-3">
                <h5 class="fw-bold">HospitalCare</h5>
                <p class="text-muted">Providing reliable and efficient hospital management solutions to improve healthcare services.</p>
            </div>
            
            <!-- Quick Links -->
            <div class="col-md-4 mb-3">
                <h5 class="fw-bold">Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="<?php echo BASE_URL; ?>index.php" class="text-white text-decoration-none">Home</a></li>
                    <li><a href="<?php echo BASE_URL; ?>about.php" class="text-white text-decoration-none">About</a></li>
                    <li><a href="<?php echo BASE_URL; ?>contact.php" class="text-white text-decoration-none">Contact</a></li>
                    <li><a href="<?php echo BASE_URL; ?>login.php" class="text-white text-decoration-none">Login</a></li>
                </ul>
            </div>
            
            <!-- Contact -->
            <div class="col-md-4 mb-3">
                <h5 class="fw-bold">Contact Us</h5>
                <p class="mb-1"><i class="fa-solid fa-location-dot me-2"></i>DHSGSU UTD, Sagar, MP</p>
                <p class="mb-1"><i class="fa-solid fa-phone me-2"></i>+91 98765 43210</p>
                <p class="mb-3"><i class="fa-solid fa-envelope me-2"></i>support@hospitalcare.com</p>
                
                <!-- Social Icons -->
                <a href="#" class="text-white me-3"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                <a href="#" class="text-white me-3"><i class="fab fa-linkedin"></i></a>
            </div>
        </div>

        <hr class="border-light">
        <div class="text-center">
            <p class="mb-0">&copy; <?= date('Y') ?> HospitalCare. All Rights Reserved.</p>
        </div>
    </div>
</footer>

<!-- Bootstrap & Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
