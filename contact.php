<?php
// We will handle the form submission on this page itself.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- NEW: Include our new email function ---
require_once './includes/send_email.php';

// Handle the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email   = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);

    if (!$email) {
        $_SESSION['error_message'] = "Please enter a valid email address.";
    } else {
        // --- NEW: Email Sending Logic ---
        
        // This is the email address that will receive the contact form submissions
        $adminEmail = "raketyler439@gmail.com";

        $emailSubject = "New Contact Form Query: " . $subject;
        
        $emailBody = "<h3>New message from the HospitalCare contact form:</h3>" .
                     "<ul>" .
                     "<li><b>Name:</b> " . htmlspecialchars($name) . "</li>" .
                     "<li><b>Email:</b> " . htmlspecialchars($email) . "</li>" .
                     "<li><b>Subject:</b> " . htmlspecialchars($subject) . "</li>" .
                     "</ul>" .
                     "<p><b>Message:</b><br>" . nl2br(htmlspecialchars($message)) . "</p>";

        // Call the reusable function to send the email
        if (sendEmail($adminEmail, $emailSubject, $emailBody)) {
             $_SESSION['success_message'] = "Thank you, {$name}! Your message has been sent successfully.";
        } else {
             $_SESSION['error_message'] = "Sorry, your message could not be sent at this time. Please try again later.";
        }
        // --- END: Email Sending Logic ---
    }
    
    header("Location: contact.php");
    exit();
}

$page_title = "Contact Us";
include './includes/header.php';
?>
<style>
    .contact-section {
        background-color: #f8f9fa; /* Light grey background */
        padding: 4rem 0;
    }
    .contact-icon-box {
        background: #fff;
        border-radius: 12px;
        padding: 2rem;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        box-shadow: 0 6px 20px rgba(0,0,0,0.08);
    }
    .contact-icon-box:hover {
        transform: translateY(-10px);
        box-shadow: 0 12px 28px rgba(0,0,0,0.12);
    }
    .contact-icon-box i {
        font-size: 2.5rem;
        color: #0d6efd; /* Bootstrap primary blue */
        margin-bottom: 1rem;
        display: block;
    }
    .map-container iframe {
        border-radius: 12px;
    }
</style>

<div class="contact-section">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="display-5 fw-bold text-primary">Contact Us</h1>
            <p class="text-muted fs-5">We’re here to help. Reach out to us anytime.</p>
        </div>

        <div class="row mb-5 text-center">
            <div class="col-lg-4 mb-4">
                <div class="contact-icon-box h-100">
                    <i class="fas fa-map-marker-alt"></i>
                    <h5 class="fw-bold">Our Address</h5>
                    <p class="text-muted">123 Healthcare Street<br>Sagar, Madhya Pradesh, India</p>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="contact-icon-box h-100">
                    <i class="fas fa-phone-alt"></i>
                    <h5 class="fw-bold">Call Us</h5>
                    <a href="tel:+911234567890" class="text-decoration-none text-muted d-block">+91 12345 67890</a>
                    <span class="d-block mt-2">Mon - Sat : 9 AM - 7 PM</span>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="contact-icon-box h-100">
                    <i class="fas fa-envelope"></i>
                    <h5 class="fw-bold">Email Us</h5>
                    <a href="mailto:info@hospitalcare.com" class="text-decoration-none text-muted">info@hospitalcare.com</a>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-lg border-0 rounded-4">
                    <div class="card-body p-4 p-md-5">
                        <h3 class="fw-bold text-primary mb-4 text-center">📩 Send Us a Message</h3>

                        <?php if (isset($_SESSION['success_message'])): ?>
                            <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
                        <?php elseif (isset($_SESSION['error_message'])): ?>
                            <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
                        <?php endif; ?>

                        <form action="contact.php" method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Your Name</label>
                                    <input type="text" id="name" name="name" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" id="email" name="email" class="form-control" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="subject" class="form-label">Subject</label>
                                <input type="text" id="subject" name="subject" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="message" class="form-label">Message</label>
                                <textarea id="message" name="message" rows="5" class="form-control" required></textarea>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary btn-lg px-5">Send Message</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid p-0 mt-5">
    <div class="map-container">
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d117488.100344464!2d78.65994064372558!3d23.83236359051483!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3978d14a5cf59aad%3A0x26c0a0c6606a1b2b!2sSagar%2C%20Madhya%20Pradesh!5e0!3m2!1sen!2sin!4v1727800000000!5m2!1sen!2sin" 
            width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
        </iframe>
    </div>
</div>

<?php include './includes/footer.php'; ?>