<?php
session_start();
require_once '../includes/db.php';
require_once __DIR__ . '/../includes/config.php';

// If the user hasn't been through the registration page, redirect them back
if (!isset($_SESSION['otp']) || !isset($_SESSION['registration_data'])) {
    header("Location: register.php");
    exit();
}

$error = "";
// Get the user's email from the session to display it
$user_email = $_SESSION['registration_data']['email'] ?? 'your email address';

// Function to partially mask the email for display
function mask_email($email) {
    list($user, $domain) = explode('@', $email);
    return substr($user, 0, 3) . str_repeat('*', 5) . '@' . $domain;
}

// Handle OTP form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // The 6 boxes will be submitted as an array named 'otp'. We combine them into a single string.
    $submitted_otp = is_array($_POST['otp']) ? implode('', $_POST['otp']) : '';

    if (empty($submitted_otp) || strlen($submitted_otp) !== 6) {
        $error = "Please enter the complete 6-digit OTP.";
    } elseif ($submitted_otp == $_SESSION['otp']) {
        // OTP is correct, proceed with registration
        $data = $_SESSION['registration_data'];
        
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $data['name'], $data['email'], $data['password'], $data['role']);

        if ($stmt->execute()) {
            unset($_SESSION['otp']);
            unset($_SESSION['registration_data']);
            $_SESSION['success_message'] = "Email verified successfully! You can now log in.";
            header("Location: login.php");
            exit();
        } else {
            $error = "Error: Could not complete registration. Please try again.";
        }
        $stmt->close();
    } else {
        $error = "The OTP you entered is incorrect. Please try again.";
    }
}

$pageTitle = "Verify Your Email";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify OTP - Hospital Management System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f0f4f8; background-image: linear-gradient(135deg, #eef4fe 0%, #dbe8fd 100%); }
        .verify-container { display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 1rem; }
        .verify-card { display: flex; width: 100%; max-width: 950px; background: #ffffff; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); overflow: hidden; animation: fadeIn 0.6s ease-in-out; }
        .verify-image-side { flex: 1; background: url('https://plus.unsplash.com/premium_photo-1723651465341-6f347e27dafa?q=80&w=1170&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D?auto=format&fit=crop&w=800&q=80') center/cover no-repeat; }
        .verify-form-side { flex: 1.2; padding: 2.5rem 3rem; text-align: center; }
        .brand-title { font-weight: 700; color: #0d6efd; }
        .otp-input-group { display: flex; justify-content: center; gap: 10px; margin: 2rem 0; }
        .otp-input { width: 45px; height: 50px; text-align: center; font-size: 1.25rem; border: 1px solid #ced4da; border-radius: 8px; transition: border-color 0.3s ease, box-shadow 0.3s ease; }
        .otp-input:focus { border-color: #0d6efd; box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25); outline: none; }
        .btn-primary { border-radius: 25px; font-weight: bold; padding: 12px; }
        .footer-text a { text-decoration: none; font-weight: 500; }
        @keyframes fadeIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
        @media (max-width: 768px) { .verify-image-side { display: none; } .verify-form-side { padding: 2rem; } }
    </style>
</head>
<body>
    <div class="verify-container">
        <div class="verify-card">
            <div class="verify-image-side"></div>
            <div class="verify-form-side">
                <h3 class="mb-2 brand-title"><i class="bi bi-shield-check"></i> Email Verification</h3>
                <p class="text-muted">A 6-digit code has been sent to <br><b><?php echo htmlspecialchars(mask_email($user_email)); ?></b></p>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" action="verify_otp.php">
                    <div class="otp-input-group">
                        <input type="text" class="form-control otp-input" name="otp[]" maxlength="1" required>
                        <input type="text" class="form-control otp-input" name="otp[]" maxlength="1" required>
                        <input type="text" class="form-control otp-input" name="otp[]" maxlength="1" required>
                        <input type="text" class="form-control otp-input" name="otp[]" maxlength="1" required>
                        <input type="text" class="form-control otp-input" name="otp[]" maxlength="1" required>
                        <input type="text" class="form-control otp-input" name="otp[]" maxlength="1" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Verify Account</button>
                    </div>
                </form>
                <p class="text-center mt-3 footer-text"><a href="register.php">Go back to registration</a></p>
            </div>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const inputs = document.querySelectorAll('.otp-input');
    inputs.forEach((input, index) => {
        input.addEventListener('input', () => {
            if (input.value.length === 1 && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && input.value.length === 0 && index > 0) {
                inputs[index - 1].focus();
            }
        });

        // Handle paste event
        if (index === 0) {
            input.addEventListener('paste', (e) => {
                e.preventDefault();
                const pastedData = e.clipboardData.getData('text');
                const digits = pastedData.replace(/\D/g, '').split('');
                inputs.forEach((input, i) => {
                    if (digits[i]) {
                        input.value = digits[i];
                    }
                });
                // Focus the last filled input or the last input if paste is long
                const lastFilledIndex = Math.min(digits.length - 1, inputs.length - 1);
                if(lastFilledIndex >= 0) {
                    inputs[lastFilledIndex].focus();
                }
            });
        }
    });
});
</script>

</body>
</html>