<?php
// Always start the session at the very top
session_start();

// This line is essential. It defines BASE_URL.
require_once __DIR__ . '/../includes/config.php';

// Now you can include the database
include("../includes/db.php");

$error = "";

// If user is already logged in, redirect them away
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if ($_SESSION['role'] === 'admin') { header("Location: " . BASE_URL . "admin/index.php"); }
    elseif ($_SESSION['role'] === 'doctor') { header("Location: " . BASE_URL . "doctors/dashboard.php"); }
    else { header("Location: " . BASE_URL . "index.php"); }
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $error = "Both email and password are required!";
    } else {
        $stmt = $conn->prepare("SELECT user_id, password, role, name FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $hashedPass, $role, $name);
            $stmt->fetch();

            if (password_verify($password, $hashedPass)) {
                session_regenerate_id(true);
                $_SESSION['loggedin'] = true;
                $_SESSION['user_id'] = $id;
                $_SESSION['user_name'] = $name;
                $_SESSION['role'] = $role;

                if ($role === 'admin') { header("Location: " . BASE_URL . "admin/index.php"); } 
                elseif ($role === 'doctor') { header("Location: " . BASE_URL . "doctors/dashboard.php"); }
                else { header("Location: " . BASE_URL . "index.php"); }
                exit();
            } else { $error = "Invalid email or password!"; }
        } else { $error = "Invalid email or password!"; }
        $stmt->close();
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Hospital Management System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f0f4f8; background-image: linear-gradient(135deg, #eef4fe 0%, #dbe8fd 100%); }
        .login-container { display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .login-card { display: flex; width: 100%; max-width: 900px; background: #ffffff; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); overflow: hidden; animation: fadeIn 0.6s ease-in-out; }
        .login-image-side { flex: 1; background: url('https://images.unsplash.com/photo-1576091160550-2173dba999ef?auto=format&fit=crop&w=800&q=80') center/cover no-repeat; }
        .login-form-side { flex: 1; padding: 2.5rem 3rem; }
        .brand-title { font-weight: 700; color: #0d6efd; }
        .btn-primary { border-radius: 25px; font-weight: bold; padding: 12px; border: none; transition: background-color 0.3s ease; }
        .btn-outline-secondary { border-radius: 25px; font-weight: bold; padding: 12px; }
        .input-group-text { background-color: #e9ecef; border: 1px solid #ced4da; }
        .form-control { border-left: none; padding: 12px; }
        .form-control:focus { box-shadow: none; border-color: #0d6efd; }
        .input-group:focus-within { box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25); border-radius: .375rem; }
        .footer-text a { text-decoration: none; font-weight: 500; }
        @keyframes fadeIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
        @media (max-width: 768px) { .login-image-side { display: none; } .login-form-side { padding: 2rem; } }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-image-side"></div>
            <div class="login-form-side">
                <h3 class="text-center mb-2 brand-title"><i class="bi bi-hospital"></i> HospitalCare</h3>
                <p class="text-center mb-4 text-secondary">Sign in to your account</p>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <form method="POST" action="login.php">
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" class="form-control" placeholder="Email Address" required>
                    </div>
                    <div class="input-group mb-4">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>
                    <div class="d-grid mt-2">
                        <a href="<?php echo BASE_URL; ?>index.php" class="btn btn-outline-secondary">
                            <i class="bi bi-house-door"></i> Back to Home
                        </a>
                    </div>
                    <p class="text-center mt-4 footer-text">
                        Don’t have an account? <a href="register.php">Register Now</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</body>
</html>