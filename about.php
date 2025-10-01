<?php 
$pageTitle = "About Us";
include './includes/header.php'; 
?>

<style>
    /* Section-specific background colors for visual separation */
    .section-light {
        background-color: #f8f9fa; /* Bootstrap's light grey */
    }
    .section-white {
        background-color: #ffffff;
    }

    /* Styling for the new "Our Values" icon boxes */
    .value-box {
        background: #fff;
        border-radius: 12px;
        padding: 2.5rem 2rem;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        box-shadow: 0 6px 20px rgba(0,0,0,0.08);
        border: 1px solid #e0e0e0;
    }
    .value-box:hover {
        transform: translateY(-10px);
        box-shadow: 0 12px 28px rgba(0,0,0,0.12);
        border-color: var(--bs-primary);
    }
    .value-box .icon {
        font-size: 3rem;
        color: var(--bs-primary);
        margin-bottom: 1rem;
        display: inline-block;
    }
    .value-box h5 {
        color: var(--bs-primary);
    }

    /* Call to Action section styling */
    .cta-section {
        background: linear-gradient(45deg, var(--bs-primary), var(--bs-info));
        color: #fff;
        border-radius: 15px;
    }
</style>

<div class="container-fluid bg-primary text-white text-center py-5">
    <div class="container">
        <h1 class="display-4 fw-bold">About HospitalCare</h1>
        <p class="lead col-lg-8 mx-auto">Learn about our commitment to providing compassionate care through advanced technology and trusted healthcare solutions.</p>
    </div>
</div>

<section class="section-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <img src="https://images.unsplash.com/photo-1586773860418-d37222d8fce3?auto=format&fit=crop&w=1600&q=80" 
                     alt="Our modern hospital facility" class="img-fluid rounded-3 shadow-lg">
            </div>
            <div class="col-lg-6 ps-lg-5">
                <h2 class="fw-bold text-primary mb-3">Welcome to the Future of Healthcare</h2>
                <p class="text-muted">
                    Our Hospital Management System is designed to provide seamless healthcare services, 
                    helping doctors, patients, and staff stay connected. We believe in combining modern 
                    medical practices with advanced technology to create a better experience for everyone.
                </p>
                <p class="text-muted">
                    From patient record management to doctor scheduling and appointment booking, 
                    our system ensures efficiency and reliability every step of the way.
                </p>
            </div>
        </div>
    </div>
</section>

<section class="section-light py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="p-4 text-center">
                    <i class="fas fa-bullseye fa-3x text-primary mb-3"></i>
                    <h3 class="fw-bold">Our Mission</h3>
                    <p class="text-muted">To deliver quality healthcare through innovation, compassion, and commitment. We aim to empower hospitals with a robust digital ecosystem that ensures patients receive the best care possible.</p>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="p-4 text-center">
                    <i class="fas fa-eye fa-3x text-primary mb-3"></i>
                    <h3 class="fw-bold">Our Vision</h3>
                    <p class="text-muted">To become the leading healthcare solution provider by integrating technology, trust, and accessibility into every aspect of hospital management, ultimately improving patient outcomes.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-white py-5">
    <div class="container text-center">
        <h2 class="fw-bold text-primary mb-5">Our Core Values</h2>
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="value-box h-100">
                    <div class="icon"><i class="fas fa-heart-pulse"></i></div>
                    <h5 class="fw-bold">Compassion</h5>
                    <p class="text-muted small">We treat patients with kindness and empathy in every step of their journey.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="value-box h-100">
                    <div class="icon"><i class="fas fa-lightbulb"></i></div>
                    <h5 class="fw-bold">Innovation</h5>
                    <p class="text-muted small">We embrace technology to improve efficiency and medical outcomes.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="value-box h-100">
                    <div class="icon"><i class="fas fa-shield-alt"></i></div>
                    <h5 class="fw-bold">Integrity</h5>
                    <p class="text-muted small">We uphold transparency, trust, and ethics in healthcare delivery.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="value-box h-100">
                    <div class="icon"><i class="fas fa-star"></i></div>
                    <h5 class="fw-bold">Excellence</h5>
                    <p class="text-muted small">We strive for continuous improvement and the highest quality of care.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="cta-section p-5 text-center">
            <h2 class="fw-bold mb-3">Ready to Experience Better Healthcare?</h2>
            <p class="lead mb-4">Our team is ready to assist you. Book an appointment or get in touch with us today.</p>
            <a href="<?php echo BASE_URL; ?>patients/request_appointment.php" class="btn btn-light btn-lg me-2">Book an Appointment</a>
            <a href="<?php echo BASE_URL; ?>contact.php" class="btn btn-outline-light btn-lg">Contact Us</a>
        </div>
    </div>
</section>

<?php include './includes/footer.php'; ?>