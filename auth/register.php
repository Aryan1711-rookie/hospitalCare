<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/send_email.php';

// If user is already logged in, redirect them away from the register page
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$error = "";
$allowed_roles = ['patient', 'doctor', 'admin'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $role = trim($_POST['role'] ?? 'patient');

    if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error = "All fields are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please provide a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match!";
    } elseif (!in_array($role, $allowed_roles, true)) {
        $error = "Invalid role selected.";
    } else {
        $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "An account with this email already exists!";
        } else {
            $otp = rand(100000, 999999);
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $_SESSION['otp'] = $otp;
            $_SESSION['registration_data'] = ['name' => $name, 'email' => $email, 'password' => $hashedPassword, 'role' => $role];
            $subject = "Your Verification Code for HospitalCare";
            $body = "<h3>Welcome to HospitalCare!</h3><p>Thank you for registering. Please use the following One-Time Password (OTP) to verify your email address:</p><h2 style='text-align:center; color:#0d6efd;'><b>" . $otp . "</b></h2><p>This code is valid for 10 minutes. If you did not request this, please ignore this email.</p>";
            
            if (sendEmail($email, $subject, $body)) {
                header("Location: verify_otp.php");
                exit();
            } else {
                $error = "Could not send verification email. Please try again later.";
            }
        }
        $check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - HospitalCare</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f0f4f8; background-image: linear-gradient(135deg, #eef4fe 0%, #dbe8fd 100%); }
        .register-container { display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 1rem; }
        .register-card { display: flex; width: 100%; max-width: 950px; background: #ffffff; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); overflow: hidden; animation: fadeIn 0.6s ease-in-out; }
        .register-image-side { flex: 1; background: url('https://images.unsplash.com/photo-1538108149393-fbbd81895907?auto=format&fit=crop&w=800&q=80') center/cover no-repeat; }
        .register-form-side { flex: 1.2; padding: 2.5rem 3rem; }
        .brand-title { font-weight: 700; color: #0d6efd; }
        .btn-primary { border-radius: 25px; font-weight: bold; padding: 12px; border: none; transition: background-color 0.3s ease; }
        .btn-outline-secondary { border-radius: 25px; font-weight: bold; padding: 12px; }
        .input-group-text { background-color: #e9ecef; border: 1px solid #ced4da; }
        .form-control, .form-select { border-left: none; padding: 12px; }
        .form-control:focus, .form-select:focus { box-shadow: none; border-color: #0d6efd; }
        .input-group:focus-within { box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25); border-radius: .375rem; }
        .footer-text a { text-decoration: none; font-weight: 500; }
        @keyframes fadeIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
        @media (max-width: 768px) { .register-image-side { display: none; } .register-form-side { padding: 2rem; } }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <!-- Image Side -->
            <div class="register-image-side"></div>

            <!-- Form Side -->
            <div class="register-form-side">
                <h3 class="text-center mb-1 brand-title"><i class="bi bi-hospital"></i> HospitalCare</h3>
                <p class="text-center mb-3 text-secondary">Create Your Account to Get Started</p>

                <?php if ($error): ?>
                    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="POST" action="register.php">
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" name="name" class="form-control" placeholder="Full Name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" class="form-control" placeholder="Email Address" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" class="form-control" placeholder="Password (min. 6 characters)" required>
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" required>
                    </div>
                    <div class="input-group mb-3">
                         <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                        <select name="role" class="form-select" required>
                            <option value="patient" <?= (($_POST['role'] ?? 'patient') === 'patient') ? 'selected' : '' ?>>Register as a Patient</option>
                            <option value="doctor"  <?= (($_POST['role'] ?? '') === 'doctor') ? 'selected' : '' ?>>Register as a Doctor</option>
                            <option value="admin"  <?= (($_POST['role'] ?? '') === 'admin') ? 'selected' : '' ?>>Register as an Admin</option>
                        </select>
                    </div>
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary">Create Account</button>
                    </div>
                    <div class="d-grid mt-2">
                        <a href="<?php echo BASE_URL; ?>index.php" class="btn btn-outline-secondary">
                            <i class="bi bi-house-door"></i> Back to Home
                        </a>
                    </div>
                    <p class="text-center mt-3 footer-text">
                        Already have an account? <a href="login.php">Sign In</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</body>
</html>