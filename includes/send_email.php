<?php
// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require_once __DIR__ . '/../vendor/autoload.php';

/**
 * A reusable function to send emails.
 *
 * @param string $to The recipient's email address.
 * @param string $subject The subject of the email.
 * @param string $body The HTML body of the email.
 * @return bool True on success, false on failure.
 */
function sendEmail($to, $subject, $body) {
    // Create an instance; passing `true` enables exceptions
    $mail = new PHPMailer(true);

    try {
        // --- SERVER SETTINGS ---
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Enable verbose debug output for testing
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        
        // --- YOUR GMAIL CREDENTIALS ---
        // IMPORTANT: Replace with your Gmail address and the 16-digit App Password
        $mail->Username   = 'raketyler439@gmail.com';
        $mail->Password   = 'ggpoodqafyvttyza';
        
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        // --- RECIPIENTS ---
        $mail->setFrom('raketyler439@gmail.com', 'HospitalCare'); // This is the sender's email and name
        $mail->addAddress($to); // This is the recipient

        // --- CONTENT ---
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body; // The HTML version of the email
        $mail->AltBody = strip_tags($body); // A plain-text version for non-HTML email clients

        $mail->send();
        return true; // Return true if email is sent
    } catch (Exception $e) {
        // In a real application, you would log this error.
        // error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false; // Return false if there was an error
    }
}
?>