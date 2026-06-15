<?php
// register.php - Customer Registration
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName        = trim($_POST['full_name']);
    $email           = trim($_POST['email']);
    $username        = trim($_POST['username']);
    $password        = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    if (empty($fullName) || empty($email) || empty($username) || empty($password) || empty($confirmPassword)) {
        $error = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } else {
        $regResult = registerUser($username, $email, $password, $fullName);
        if ($regResult === true) {
            header("Location: login.php?registered=1");
            exit();
        } else {
            $error = $regResult;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account — Orchid Gifts & More</title>
    <meta name="description" content="Create a free Orchid Gifts & More account and discover unique gifts for every occasion.">
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
            min-height: 640px;
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

        /* Right side: White form section (55% width) */
        .right-panel {
            width: 55%;
            background-color: var(--card-bg);
            padding: 40px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            z-index: 3;
        }

        /* Responsive Styles */
        @media (max-width: 991.98px) {
            .right-panel {
                padding: 35px 40px;
            }
        }

        @media (max-width: 767.98px) {
            .login-card {
                flex-direction: column;
                max-width: 480px;
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

        /* Two-column fields grid inside the form */
        .fields-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            column-gap: 14px;
        }
        .full-col { grid-column: 1 / -1; }

        @media (max-width: 575.98px) {
            .fields-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Input Fields with icons inside */
        .input-icon-group {
            position: relative;
            margin-bottom: 16px;
        }

        .input-icon-group label {
            display: block;
            font-size: 0.825rem;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 5px;
        }

        .input-inner {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon-left {
            position: absolute;
            left: 20px;
            color: var(--text-muted);
            font-size: 1.05rem;
            z-index: 5;
            pointer-events: none;
            transition: color 0.3s ease;
        }

        .input-inner .form-control {
            border-radius: 50px;
            padding: 11px 20px 11px 50px;
            font-size: 0.925rem;
            background-color: #f8fafc;
            border: 1.5px solid #f1f5f9;
            color: var(--text-main);
            box-shadow: none;
            transition: all 0.3s ease;
            width: 100%;
        }

        .input-inner .form-control:focus {
            background-color: #ffffff;
            border-color: var(--primary-teal);
            box-shadow: 0 0 0 4px rgba(20, 184, 166, 0.15);
        }

        .input-inner .form-control:focus ~ .input-icon-left {
            color: var(--primary-teal);
        }

        /* Eye toggle for password */
        .toggle-pw {
            position: absolute;
            right: 20px;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 1rem;
            z-index: 5;
            transition: color 0.2s;
        }
        .toggle-pw:hover {
            color: var(--primary-teal);
        }

        /* Password strength bar */
        .pw-strength-bar {
            display: flex;
            gap: 4px;
            margin-top: 6px;
            padding: 0 10px;
        }
        .pw-strength-bar span {
            flex: 1;
            height: 3px;
            border-radius: 999px;
            background: rgba(20, 184, 166, 0.1);
            transition: background 0.3s ease;
        }
        .pw-strength-bar.weak  span:nth-child(-n+1) { background: #ef4444; }
        .pw-strength-bar.fair  span:nth-child(-n+2) { background: #f59e0b; }
        .pw-strength-bar.good  span:nth-child(-n+3) { background: #10b981; }
        .pw-strength-bar.strong span:nth-child(-n+4) { background: var(--primary-teal); }
        .pw-strength-label { font-size: 0.725rem; color: var(--text-muted); margin-top: 3px; padding: 0 10px; }

        /* Checkbox Terms row */
        .terms-row {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin: 5px 0 16px;
            font-size: 0.8rem;
            color: var(--text-muted);
            padding: 0 5px;
        }
        .terms-row input[type="checkbox"] {
            accent-color: var(--primary-teal);
            width: 15px;
            height: 15px;
            flex-shrink: 0;
            margin-top: 2px;
            cursor: pointer;
        }

        /* Rounded turquoise register button */
        .btn-turquoise {
            background-color: var(--primary-teal);
            color: #ffffff;
            border: none;
            padding: 12px 30px;
            font-size: 0.95rem;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(20, 184, 166, 0.25);
            width: 100%;
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
        .signin-link {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--primary-teal);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .signin-link:hover {
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
            padding: 11px 20px;
            font-size: 0.925rem;
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
            margin: 20px 0 15px;
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

        /* Benefit chips at the bottom */
        .benefits-strip {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
        }
        .benefit-chip {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.75rem;
            color: var(--text-muted);
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 999px;
            padding: 4px 12px;
            transition: all 0.3s ease;
        }
        .benefit-chip:hover {
            background: rgba(20, 184, 166, 0.05);
            border-color: var(--primary-teal);
            color: var(--primary-teal-hover);
        }
        .benefit-chip i {
            color: var(--primary-teal);
            font-size: 0.78rem;
        }
    </style>
</head>
<body>

<!-- Full-screen blurred background using the same image as left panel -->
<div class="full-bg-blur"></div>
<div class="full-bg-overlay"></div>

<!-- Centered signup card with subtle box shadow -->
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
                <h2 class="fw-bold mb-3" style="font-size: 2.1rem; line-height: 1.25; font-family: 'Outfit', sans-serif;">Join Orchid Gifts</h2>
                <p class="opacity-75 small">Create your free account today and discover unique gifts, track exclusive orders, and unlock special dashboard perks.</p>
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

    <!-- Right side (55% width): white registration form section -->
    <div class="right-panel">
        
        <!-- Heading and subtitle centered at the top -->
        <div class="text-center mb-3">
            <h3 class="fw-bold text-dark mb-1">Create Account</h3>
            <p class="text-muted small">Join us and start gifting with style</p>
        </div>

        <!-- PHP Alert messages -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger border-0 rounded-4 py-2 px-3 small text-center mb-3" role="alert">
                <i class="bi bi-exclamation-circle-fill me-2"></i><?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Signup Form -->
        <form action="register.php" method="POST" id="registerForm" novalidate>
            <div class="fields-grid">

                <!-- Full Name field -->
                <div class="input-icon-group full-col">
                    <label for="full_name">Full Name</label>
                    <div class="input-inner">
                        <input type="text" id="full_name" name="full_name" class="form-control" required
                               placeholder="e.g. Jane Doe"
                               value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                        <span class="input-icon-left"><i class="bi bi-person"></i></span>
                    </div>
                </div>

                <!-- Email address field -->
                <div class="input-icon-group full-col">
                    <label for="email">Email Address</label>
                    <div class="input-inner">
                        <input type="email" id="email" name="email" class="form-control" required
                               placeholder="e.g. jane@example.com"
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        <span class="input-icon-left"><i class="bi bi-envelope"></i></span>
                    </div>
                </div>

                <!-- Username field -->
                <div class="input-icon-group full-col">
                    <label for="username">Username</label>
                    <div class="input-inner">
                        <input type="text" id="username" name="username" class="form-control" required
                               placeholder="Choose a username"
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                        <span class="input-icon-left"><i class="bi bi-at"></i></span>
                    </div>
                </div>

                <!-- Password field with strength meter -->
                <div class="input-icon-group">
                    <label for="password">Password</label>
                    <div class="input-inner">
                        <input type="password" id="password" name="password" class="form-control" required
                               placeholder="Min. 6 chars" autocomplete="new-password">
                        <span class="input-icon-left"><i class="bi bi-lock"></i></span>
                        <i class="bi bi-eye toggle-pw" id="togglePw" title="Show / hide"></i>
                    </div>
                    <div class="pw-strength-bar" id="pwStrengthBar">
                        <span></span><span></span><span></span><span></span>
                    </div>
                    <div class="pw-strength-label" id="pwStrengthLabel"></div>
                </div>

                <!-- Confirm Password field -->
                <div class="input-icon-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="input-inner">
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required
                               placeholder="Repeat password" autocomplete="new-password">
                        <span class="input-icon-left"><i class="bi bi-shield-lock"></i></span>
                    </div>
                </div>

            </div><!-- /.fields-grid -->

            <!-- Terms agreement row -->
            <div class="terms-row">
                <input type="checkbox" id="agreeTerms" required>
                <label for="agreeTerms">
                    I agree to receive order updates and promotions from Orchid Gifts &amp; More.
                </label>
            </div>

            <!-- Submit Button (turquoise) -->
            <div class="mb-3">
                <button type="submit" class="btn btn-turquoise" id="registerBtn">
                    <i class="bi bi-bag-heart me-2"></i>Create Free Account
                </button>
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

        <!-- Link back to Sign In page -->
        <div class="text-center mt-2 mb-3">
            <span class="text-muted small">Already have an account? </span>
            <a href="login.php" class="signin-link">Sign in here</a>
        </div>

        <!-- Benefit chips at the bottom -->
        <div class="benefits-strip pt-2 border-top" style="border-top-color: #f1f5f9 !important;">
            <div class="benefit-chip"><i class="bi bi-gift"></i> Exclusive offers</div>
            <div class="benefit-chip"><i class="bi bi-truck"></i> Track orders</div>
            <div class="benefit-chip"><i class="bi bi-heart"></i> Wishlist</div>
        </div>

    </div>
</div>

<!-- Bootstrap 5 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Password visibility toggle
    const togglePw = document.getElementById('togglePw');
    const pwInput  = document.getElementById('password');
    togglePw.addEventListener('click', () => {
        const show = pwInput.type === 'password';
        pwInput.type = show ? 'text' : 'password';
        togglePw.classList.toggle('bi-eye',       !show);
        togglePw.classList.toggle('bi-eye-slash',  show);
    });

    // Password strength bar indicators
    const pwStrengthBar   = document.getElementById('pwStrengthBar');
    const pwStrengthLabel = document.getElementById('pwStrengthLabel');
    pwInput.addEventListener('input', function () {
        const v = this.value;
        let score = 0;
        if (v.length >= 6)  score++;
        if (v.length >= 10) score++;
        if (/[A-Z]/.test(v) && /[a-z]/.test(v)) score++;
        if (/\d/.test(v) && /[^A-Za-z0-9]/.test(v)) score++;
        const levels = ['', 'weak', 'fair', 'good', 'strong'];
        const labels = ['', 'Weak', 'Fair', 'Good', 'Strong 💪'];
        pwStrengthBar.className = 'pw-strength-bar ' + (levels[score] || '');
        pwStrengthLabel.textContent = v.length ? labels[score] : '';
    });

    // Confirm passwords match indicators
    document.getElementById('confirm_password').addEventListener('input', function () {
        this.style.borderColor = (this.value && this.value !== pwInput.value) ? '#ef4444' : '';
    });

    // Show loading spinner on form submit
    document.getElementById('registerForm').addEventListener('submit', function (e) {
        if (!document.getElementById('agreeTerms').checked) {
            e.preventDefault();
            alert('Please agree to the terms before creating your account.');
            return;
        }
        const btn = document.getElementById('registerBtn');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Creating account…';
        btn.disabled = true;
    });
</script>
</body>
</html>
