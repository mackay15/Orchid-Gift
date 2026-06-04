<?php
// login.php - Unified Portal Login
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    $role = $_SESSION['role'];
    if ($role === 'admin') header("Location: admin/index.php");
    elseif ($role === 'cashier') header("Location: cashier/index.php");
    else header("Location: customer/index.php");
    exit();
}

$error = '';
$redirect = $_GET['redirect'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $loginRes = loginUser($username, $password);
        if ($loginRes === true) {
            // Success - Redirect based on role
            if (!empty($redirect)) {
                header("Location: " . $redirect);
            } else {
                $role = $_SESSION['role'];
                if ($role === 'admin') header("Location: admin/index.php");
                elseif ($role === 'cashier') header("Location: cashier/index.php");
                else header("Location: customer/index.php");
            }
            exit();
        } else {
            $error = $loginRes;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ORCHID GIFT AND MORE</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        body {
            background-color: var(--dark-bg);
            background-image: radial-gradient(circle at 10% 20%, rgba(90, 24, 154, 0.2) 0%, rgba(12, 8, 23, 0.9) 90%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-bubble-1 {
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: radial-gradient(var(--primary-color), transparent 60%);
            top: 10%;
            left: 15%;
            filter: blur(50px);
            opacity: 0.3;
            z-index: 1;
        }
        .login-bubble-2 {
            position: absolute;
            width: 250px;
            height: 250px;
            border-radius: 50%;
            background: radial-gradient(var(--secondary-color), transparent 60%);
            bottom: 10%;
            right: 15%;
            filter: blur(40px);
            opacity: 0.35;
            z-index: 1;
        }
        .login-container {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 450px;
        }
        .login-card {
            background: rgba(21, 15, 40, 0.7);
            border: 1px solid rgba(224, 170, 255, 0.1);
            backdrop-filter: blur(15px);
            color: #fff;
        }
        .login-card .form-control {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(224, 170, 255, 0.2);
            color: #fff;
        }
        .login-card .form-control:focus {
            background: rgba(255, 255, 255, 0.12);
            border-color: var(--secondary-color);
            box-shadow: 0 0 10px rgba(255, 133, 161, 0.3);
        }
        .login-card label {
            color: var(--text-light);
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

<div class="login-bubble-1"></div>
<div class="login-bubble-2"></div>

<div class="login-container px-3">
    <div class="text-center mb-4">
        <a href="index.php" class="text-decoration-none d-inline-flex align-items-center gap-2">
            <i class="bi bi-flower1 fs-1 text-secondary"></i>
            <span class="brand-font fw-bold fs-2 text-white" style="letter-spacing: 1px;">ORCHID</span>
        </a>
        <p class="text-muted small mt-1">Management Portal</p>
    </div>

    <div class="card login-card p-4 rounded-4 shadow-lg">
        <h4 class="text-center mb-4 font-monospace" style="letter-spacing: 0.5px;">Sign In</h4>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger border-0 bg-danger bg-opacity-20 text-danger-emphasis small py-2" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form action="login.php?redirect=<?php echo urlencode($redirect); ?>" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Username or Email</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 text-muted" style="border: 1px solid rgba(224,170,255,0.2);"><i class="bi bi-envelope"></i></span>
                    <input type="text" class="form-control border-start-0" id="username" name="username" required placeholder="e.g. customer or admin">
                </div>
            </div>
            
            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 text-muted" style="border: 1px solid rgba(224,170,255,0.2);"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control border-start-0 border-end-0" id="password" name="password" required placeholder="••••••••">
                    <span class="input-group-text bg-transparent border-start-0 text-muted" style="border: 1px solid rgba(224,170,255,0.2); cursor: pointer;" id="togglePassword">
                        <i class="bi bi-eye" id="passwordIcon"></i>
                    </span>
                </div>
            </div>
            
            <button type="submit" class="btn btn-orchid w-100 py-2.5 rounded-3 mb-3">Login to Dashboard</button>
        </form>

        <div class="text-center mt-3">
            <span class="text-muted small">New to ORCHID?</span>
            <a href="register.php" class="text-secondary small fw-bold ms-1 text-decoration-none">Create Account</a>
        </div>
        
        <div class="mt-4 pt-3 border-top border-secondary border-opacity-20 text-center text-muted small">
            <div class="d-flex justify-content-center gap-3">
                <span>Admin: <code class="text-light">admin123</code></span>
                <span>Cashier: <code class="text-light">cashier123</code></span>
            </div>
        </div>
    </div>
</div>

<script>
    // Password visibility toggle helper
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');
    const passwordIcon = document.querySelector('#passwordIcon');

    togglePassword.addEventListener('click', function (e) {
        // Toggle input type
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        
        // Toggle classes
        passwordIcon.classList.toggle('bi-eye');
        passwordIcon.classList.toggle('bi-eye-slash');
    });
</script>

</body>
</html>
