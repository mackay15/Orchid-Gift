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

$error = $_GET['error'] ?? '';
$success_msg = '';
$redirect = $_GET['redirect'] ?? '';

// Flash success message on registration
if (isset($_GET['registered']) && $_GET['registered'] == 1) {
    $success_msg = "Account created successfully! Please sign in.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $loginRes = loginUser($username, $password);
        if ($loginRes === true) {
            logCashierAction('Login', 'Logged in to system portal.');
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
    <title>Sign In — Orchid Gifts & More</title>
    <meta name="description" content="Sign in to your Orchid Gifts & More account to shop, manage orders, and access your dashboard.">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-teal: #14b8a6;
            --primary-teal-hover: #0d9488;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --card-bg: #ffffff;
            --border-color: #f1f5f9;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Outfit', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }

        /* Full screen blurred background using the same image as left panel */
        .full-bg-blur {
            position: fixed;
            top: -20px;
            left: -20px;
            right: -20px;
            bottom: -20px;
            background-image: url('assets/loginback.jpg');
            background-size: cover;
            background-position: center;
            filter: blur(15px);
            z-index: -2;
            transform: scale(1.1);
        }

        /* Dark backdrop overlay to stand out the card */
        .full-bg-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.45);
            z-index: -1;
        }

        @keyframes cardEntrance {
            from {
                opacity: 0;
                transform: translateY(30px) scale(0.98);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Centered login card with subtle box shadow */
        .login-card {
            background-color: var(--card-bg);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            width: 100%;
            max-width: 960px;
            min-height: 600px;
            display: flex;
            overflow: hidden;
            position: relative;
            z-index: 10;
            animation: cardEntrance 0.8s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        /* Left side: Image and overlay (45% width) */
        .left-panel {
            width: 45%;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 50px 40px;
            color: #ffffff;
        }

        .left-panel-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('assets/loginback.jpg');
            background-size: cover;
            background-position: center;
            z-index: 1;
            transition: transform 10s ease;
        }

        .login-card:hover .left-panel-bg {
            transform: scale(1.08);
        }

        .left-panel-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(20, 184, 166, 0.85), rgba(13, 148, 136, 0.9));
            z-index: 2;
        }

        .left-panel-content {
            position: relative;
            z-index: 3;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
        }

        /* Smooth curved/wave divider between left panel and right form section */
        .wave-divider {
            position: absolute;
            top: 0;
            right: -1px;
            width: 80px;
            height: 100%;
            z-index: 4;
            fill: var(--card-bg);
            pointer-events: none;
        }

        .wave-divider svg {
            width: 100%;
            height: 100%;
            display: block;
        }

        /* Right side: White login form section (55% width) */
        .right-panel {
            width: 55%;
            background-color: var(--card-bg);
            padding: 50px 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            z-index: 3;
        }

        /* Responsive Styles */
        @media (max-width: 991.98px) {
            .right-panel {
                padding: 40px 45px;
            }
        }

        @media (max-width: 767.98px) {
            .login-card {
                flex-direction: column;
                max-width: 460px;
                min-height: auto;
                border-radius: 20px;
            }
            .left-panel {
                display: none !important;
            }
            .right-panel {
                width: 100%;
                padding: 40px 30px;
            }
        }

        /* Input Fields with icons inside */
        .input-icon-group {
            position: relative;
            margin-bottom: 20px;
        }

        .input-icon-group .input-icon-left {
            position: absolute;
            top: 50%;
            left: 20px;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 1.1rem;
            z-index: 5;
            pointer-events: none;
            transition: color 0.3s ease;
        }

        .input-icon-group .form-control {
            border-radius: 50px;
            padding: 13px 20px 13px 50px;
            font-size: 0.95rem;
            background-color: #f8fafc;
            border: 1.5px solid #f1f5f9;
            color: var(--text-main);
            box-shadow: none;
            transition: all 0.3s ease;
        }

        .input-icon-group .form-control:focus {
            background-color: #ffffff;
            border-color: var(--primary-teal);
            box-shadow: 0 0 0 4px rgba(20, 184, 166, 0.15);
        }

        .input-icon-group .form-control:focus ~ .input-icon-left {
            color: var(--primary-teal);
        }

        /* Rounded turquoise login button */
        .btn-turquoise {
            background-color: var(--primary-teal);
            color: #ffffff;
            border: none;
            padding: 13px 30px;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(20, 184, 166, 0.25);
        }

        .btn-turquoise:hover {
            background-color: var(--primary-teal-hover);
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(20, 184, 166, 0.35);
        }

        .btn-turquoise:active {
            transform: translateY(0);
        }

        /* Links with smooth hover effects */
        .forgot-link {
            font-size: 0.875rem;
            color: var(--text-muted);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .forgot-link:hover {
            color: var(--primary-teal);
        }

        .signup-link {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--primary-teal);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .signup-link:hover {
            color: var(--primary-teal-hover);
            text-decoration: underline !important;
        }

        /* Google button styling */
        .btn-google-custom {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            background-color: #ffffff;
            color: #334155;
            border: 1.5px solid #e2e8f0;
            border-radius: 50px;
            padding: 12px 20px;
            font-size: 0.95rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-google-custom:hover {
            background-color: #f8fafc;
            border-color: var(--primary-teal);
            color: #1e293b;
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .btn-google-custom img {
            width: 18px;
            height: 18px;
        }

        /* OAuth divider */
        .oauth-divider {
            display: flex;
            align-items: center;
            text-align: center;
            color: var(--text-muted);
            margin: 25px 0 20px;
            font-size: 0.85rem;
        }

        .oauth-divider::before,
        .oauth-divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e2e8f0;
        }

        .oauth-divider::before {
            margin-right: 15px;
        }

        .oauth-divider::after {
            margin-left: 15px;
        }

        /* Social icons at bottom */
        .social-icons-container {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }

        .social-icon-btn {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .social-icon-btn:hover {
            background-color: var(--primary-teal);
            border-color: var(--primary-teal);
            color: #ffffff;
            transform: translateY(-3px);
            box-shadow: 0 5px 12px rgba(20, 184, 166, 0.25);
        }
    </style>
</head>
<body>

<!-- Full-screen blurred background using the same image as left panel -->
<div class="full-bg-blur"></div>
<div class="full-bg-overlay"></div>

<!-- Centered login card with subtle box shadow -->
<div class="login-card">
    
    <!-- Left side (45% width): background image with teal overlay & curved wave divider -->
    <div class="left-panel">
        <div class="left-panel-bg"></div>
        <div class="left-panel-overlay"></div>
        
        <div class="left-panel-content">
            <!-- App Branding -->
            <div class="d-flex align-items-center gap-2">
                <img src="assets/logo.png" alt="Orchid Logo" style="height: 42px; width: auto; filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.15));">
                <span class="fs-4 fw-bold tracking-wide" style="font-family: 'Outfit', sans-serif; letter-spacing: 1.5px;">ORCHID</span>
            </div>
            
            <!-- Hero content -->
            <div class="mb-4">
                <h2 class="fw-bold mb-3" style="font-size: 2.1rem; line-height: 1.25; font-family: 'Outfit', sans-serif;">Welcome to ORCHID GIFT & MORE 🎁</h2>
                <p class="opacity-75 small">Manage operations, track real-time analytics, and process items dynamically with our state of the art tools.</p>
            </div>
            
            <!-- Footer brand info -->
            <div class="small opacity-50">
                &copy; <?php echo date('Y'); ?> Orchid, Inc. All rights reserved.
            </div>
        </div>
        
        <!-- Smooth curved/wave divider between image section and form section -->
        <div class="wave-divider">
            <svg viewBox="0 0 100 100" preserveAspectRatio="none">
                <path d="M100,0 C 30,30 30,70 100,100 L 100,100 L 100,0 Z"></path>
            </svg>
        </div>
    </div>

    <!-- Right side (55% width): white login form section -->
    <div class="right-panel">
        
        <!-- Welcome heading and subtitle centered at the top -->
        <div class="text-center mb-4">
            <h3 class="fw-bold text-dark mb-1">Welcome Back</h3>
            <p class="text-muted small">Log in to your account to continue</p>
        </div>

        <!-- PHP Alert messages -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger border-0 rounded-4 py-2 px-3 small text-center mb-4" role="alert">
                <i class="bi bi-exclamation-circle-fill me-2"></i><?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success border-0 rounded-4 py-2 px-3 small text-center mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?php echo htmlspecialchars($success_msg); ?>
            </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form action="login.php?redirect=<?php echo urlencode($redirect); ?>" method="POST">
            
            <!-- Email/Username field with icon inside -->
            <div class="input-icon-group">
                <input type="text" id="username" name="username" class="form-control" required
                       placeholder="yourmail@example.com"
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                       autocomplete="username">
                <span class="input-icon-left"><i class="bi bi-envelope"></i></span>
            </div>

            <!-- Password field with icon inside -->
            <div class="input-icon-group">
                <input type="password" id="password" name="password" class="form-control" required
                       placeholder="••••••••"
                       autocomplete="current-password">
                <span class="input-icon-left"><i class="bi bi-lock"></i></span>
            </div>

            <!-- Forgot password link aligned to the right -->
            <div class="text-end mb-4">
                <a href="#" class="forgot-link" onclick="alert('Please contact your administrator to reset password.'); return false;">Forgot password?</a>
            </div>

            <!-- Submit Button (turquoise) -->
            <div class="mb-3">
                <button type="submit" class="btn btn-turquoise w-100" id="loginBtn">Sign in</button>
            </div>

            <!-- Google Login OAuth -->
            <div class="oauth-divider">
                <span>or</span>
            </div>

            <div class="mb-3">
                <a href="google-oauth.php" class="btn-google-custom">
                    <img src="assets/google-logo.svg" alt="Google logo">
                    Continue with Google
                </a>
            </div>
        </form>

        <!-- Don't have an account? Sign up section below form -->
        <div class="text-center mt-3 mb-4">
            <span class="text-muted small">Don't have an account? </span>
            <a href="register.php" class="signup-link">Sign up!</a>
        </div>

        <!-- Social Media Icons (Facebook, Twitter/X, LinkedIn) at the bottom -->
        <div class="social-login-section mt-auto pt-3 border-top" style="border-top-color: #f1f5f9 !important;">
            <div class="social-icons-container">
                <a href="https://facebook.com" target="_blank" class="social-icon-btn" aria-label="Facebook">
                    <i class="bi bi-facebook"></i>
                </a>
                <a href="https://twitter.com" target="_blank" class="social-icon-btn" aria-label="Twitter/X">
                    <i class="bi bi-twitter-x"></i>
                </a>
                <a href="https://linkedin.com" target="_blank" class="social-icon-btn" aria-label="LinkedIn">
                    <i class="bi bi-linkedin"></i>
                </a>
            </div>
        </div>

    </div>
</div>

<!-- Bootstrap 5 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Show loading feedback on form submission
    document.querySelector('form').addEventListener('submit', () => {
        const btn = document.getElementById('loginBtn');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Signing in...';
        btn.disabled = true;
    });
</script>
</body>
</html>
