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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --primary:       #5a189a;
            --primary-light: #9d4edd;
            --text-dark:     #2c1f45;
            --text-mid:      #6b5b82;
        }

        html, body { height: 100%; font-family: 'Outfit', sans-serif; }

        body {
            min-height: 100vh;
            /* SHOP BACKGROUND — swap this URL for your real shop photo when ready */
            background-image: url('assets/shop_bg_placeholder.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            display: flex;
            align-items: flex-start;       /* prevents flex from clipping overflow */
            justify-content: center;
            padding: 60px 16px 60px;       /* top/bottom breathing room */
            overflow-y: auto;              /* allow scroll on small screens */
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                linear-gradient(160deg,
                    rgba(18, 6, 34, 0.75) 0%,
                    rgba(90, 24, 154, 0.48) 55%,
                    rgba(185, 28, 28, 0.28) 100%);
            z-index: 0;
        }

        /* Ambient bokeh */
        .bokeh {
            position: fixed;
            border-radius: 50%;
            filter: blur(70px);
            pointer-events: none;
            z-index: 0;
            animation: drift 14s ease-in-out infinite;
        }
        .bokeh-1 { width: 400px; height: 400px; background: radial-gradient(circle, rgba(255,133,161,0.18), transparent 65%); top: -10%; left: -10%; animation-delay: 0s; }
        .bokeh-2 { width: 350px; height: 350px; background: radial-gradient(circle, rgba(123,44,191,0.20), transparent 65%); bottom: -8%; right: -8%; animation-delay: -5s; }
        .bokeh-3 { width: 240px; height: 240px; background: radial-gradient(circle, rgba(247,37,133,0.14), transparent 65%); top: 40%; left: 25%; animation-delay: -9s; }

        @keyframes drift {
            0%, 100% { transform: translate(0, 0)       scale(1);    }
            33%       { transform: translate(15px, -20px) scale(1.04); }
            66%       { transform: translate(-10px, 12px) scale(0.97); }
        }

        /* ── Card wrapper ── */
        .auth-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 500px;
            animation: riseIn 0.65s cubic-bezier(0.34, 1.56, 0.64, 1) both;
        }

        @keyframes riseIn {
            from { opacity: 0; transform: translateY(50px) scale(0.97); }
            to   { opacity: 1; transform: translateY(0)    scale(1);    }
        }

        /* ── Glass card ── */
        .auth-card {
            background: rgba(255, 255, 255, 0.86);
            backdrop-filter: blur(28px);
            -webkit-backdrop-filter: blur(28px);
            border: 1px solid rgba(255, 255, 255, 0.55);
            border-radius: 32px;
            overflow: hidden;
            box-shadow:
                0 40px 100px rgba(0, 0, 0, 0.38),
                0 0 0 1px rgba(255, 255, 255, 0.18) inset;
        }

        /* ── Dark brand header ── */
        .card-brand-header {
            background: linear-gradient(145deg, #1a0530, #3a0f6e, #5a189a);
            padding: 32px 32px 26px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            position: relative;
            overflow: hidden;
        }

        .card-brand-header::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 15% 0%, rgba(247,37,133,0.18) 0%, transparent 45%),
                radial-gradient(circle at 88% 100%, rgba(255,133,161,0.13) 0%, transparent 40%);
            pointer-events: none;
        }

        .card-logo-ring {
            position: relative;
            z-index: 1;
            width: 92px;
            height: 92px;
            border-radius: 50%;
            overflow: hidden;              /* clips image corners to circle */
            box-shadow:
                0 0 0 3px rgba(255,255,255,0.20),
                0 0 0 8px rgba(255,255,255,0.07),
                0 12px 40px rgba(0,0,0,0.45);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.4s ease, box-shadow 0.4s ease;
        }

        .card-logo-ring:hover {
            transform: scale(1.06) rotate(-3deg);
            box-shadow:
                0 0 0 3px rgba(255,133,161,0.45),
                0 0 0 8px rgba(255,133,161,0.12),
                0 18px 50px rgba(0,0,0,0.5);
        }

        .card-logo-ring img {
            width: 100%;          /* fill the ring container fully */
            height: 100%;
            object-fit: cover;    /* cover so the circular logo fills the circle */
            object-position: center;
            clip-path: circle(50% at 50% 50%); /* hard-clip to perfect circle */
            filter: drop-shadow(0 4px 14px rgba(0,0,0,0.3));
        }

        .card-brand-name {
            position: relative;
            z-index: 1;
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem;
            font-weight: 700;
            color: #fff;
            text-align: center;
            letter-spacing: 0.05em;
            text-shadow: 0 2px 8px rgba(0,0,0,0.4);
        }

        .card-brand-tagline {
            position: relative;
            z-index: 1;
            font-size: 0.76rem;
            color: rgba(255,255,255,0.62);
            letter-spacing: 0.15em;
            text-transform: uppercase;
            margin-top: -6px;
        }

        /* Smooth curve into form */
        .card-brand-header::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0; right: 0;
            height: 28px;
            background: rgba(255,255,255,0.86);
            border-radius: 28px 28px 0 0;
        }

        /* ── Form body ── */
        .card-body-form {
            padding: 26px 34px 34px;
        }

        .form-heading {
            font-family: 'Playfair Display', serif;
            font-size: 1.3rem;
            color: var(--text-dark);
            font-weight: 700;
            text-align: center;
            margin-bottom: 3px;
        }

        .form-subheading {
            font-size: 0.85rem;
            color: var(--text-mid);
            text-align: center;
            margin-bottom: 22px;
        }

        /* ── Two-column grid ── */
        .fields-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            column-gap: 14px;
        }
        .full-col { grid-column: 1 / -1; }

        /* ── Input fields ── */
        .field-wrap { margin-bottom: 16px; }

        .field-wrap label {
            display: block;
            font-size: 0.80rem;
            font-weight: 600;
            color: var(--text-mid);
            margin-bottom: 6px;
            letter-spacing: 0.02em;
        }

        .field-inner {
            position: relative;
            display: flex;
            align-items: center;
        }

        .field-icon {
            position: absolute;
            left: 13px;
            color: #a88cc8;
            font-size: 0.95rem;
            z-index: 2;
            pointer-events: none;
        }

        .field-inner input {
            width: 100%;
            padding: 11px 13px 11px 38px;
            border: 1.5px solid rgba(90, 24, 154, 0.14);
            border-radius: 13px;
            font-family: 'Outfit', sans-serif;
            font-size: 0.92rem;
            color: var(--text-dark);
            background: rgba(248, 243, 255, 0.85);
            transition: all 0.25s ease;
            outline: none;
        }

        .field-inner input:focus {
            border-color: var(--primary-light);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(157, 78, 221, 0.14);
        }

        .field-inner input::placeholder { color: #c4aee0; font-size: 0.86rem; }

        /* Password strength bar */
        .pw-strength-bar {
            display: flex;
            gap: 4px;
            margin-top: 7px;
        }
        .pw-strength-bar span {
            flex: 1;
            height: 3px;
            border-radius: 999px;
            background: rgba(90,24,154,0.1);
            transition: background 0.3s ease;
        }
        .pw-strength-bar.weak  span:nth-child(-n+1) { background: #ef4444; }
        .pw-strength-bar.fair  span:nth-child(-n+2) { background: #f59e0b; }
        .pw-strength-bar.good  span:nth-child(-n+3) { background: #10b981; }
        .pw-strength-bar.strong span:nth-child(-n+4) { background: #5a189a; }
        .pw-strength-label { font-size: 0.73rem; color: var(--text-mid); margin-top: 3px; }

        .toggle-pw {
            position: absolute;
            right: 12px;
            color: #a88cc8;
            cursor: pointer;
            font-size: 0.93rem;
            z-index: 2;
            transition: color 0.2s;
        }
        .toggle-pw:hover { color: var(--primary); }

        /* ── Alert ── */
        .auth-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 14px;
            border-radius: 12px;
            font-size: 0.86rem;
            margin-bottom: 16px;
        }
        .auth-alert-error { background: rgba(220,38,38,0.10); border: 1px solid rgba(220,38,38,0.22); color: #b91c1c; }

        /* ── Checkbox ── */
        .terms-row {
            display: flex;
            align-items: flex-start;
            gap: 9px;
            margin: 4px 0 16px;
            font-size: 0.81rem;
            color: var(--text-mid);
        }
        .terms-row input[type="checkbox"] {
            accent-color: var(--primary);
            width: 14px; height: 14px;
            flex-shrink: 0; margin-top: 2px; cursor: pointer;
        }

        /* ── Submit button ── */
        .btn-register {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #3d0d75, #5a189a, #7b2cbf);
            color: #fff;
            border: none;
            border-radius: 16px;
            font-family: 'Outfit', sans-serif;
            font-size: 0.96rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(90,24,154,0.32);
            position: relative;
            overflow: hidden;
        }
        .btn-register::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.12), transparent);
            transition: left 0.5s ease;
        }
        .btn-register:hover { transform: translateY(-2px); box-shadow: 0 16px 40px rgba(90,24,154,0.40); }
        .btn-register:hover::before { left: 100%; }
        .btn-register:active { transform: translateY(0); }

        /* ── Footer ── */
        .card-footer-link {
            text-align: center;
            margin-top: 16px;
            font-size: 0.85rem;
            color: var(--text-mid);
        }
        .card-footer-link a { color: var(--primary); font-weight: 600; text-decoration: none; }
        .card-footer-link a:hover { color: var(--primary-light); text-decoration: underline; }

        /* ── Benefit chips ── */
        .benefits-strip {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 14px;
            flex-wrap: wrap;
        }
        .benefit-chip {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.75rem;
            color: var(--text-mid);
            background: rgba(90,24,154,0.06);
            border: 1px solid rgba(90,24,154,0.1);
            border-radius: 999px;
            padding: 4px 12px;
        }
        .benefit-chip i { color: var(--primary); font-size: 0.78rem; }

        @media (max-width: 520px) {
            .card-body-form { padding: 22px 18px 28px; }
            .card-brand-header { padding: 26px 20px 22px; }
            .card-logo-ring { width: 76px; height: 76px; }
            .card-logo-ring img { width: 64px; height: 64px; }
            .fields-grid { grid-template-columns: 1fr; }
            .full-col { grid-column: 1; }
        }
    </style>
</head>
<body>

<div class="bokeh bokeh-1"></div>
<div class="bokeh bokeh-2"></div>
<div class="bokeh bokeh-3"></div>

<div class="auth-wrapper">
    <div class="auth-card">

        <!-- ── Branded header with logo inside the card ── -->
        <div class="card-brand-header">
            <div class="card-logo-ring">
                <img src="assets/orchid_logo.png" alt="Orchid Gifts & More Logo">
            </div>
            <div class="card-brand-name">Orchid Gifts &amp; More</div>
            <div class="card-brand-tagline">Where every gift tells a story</div>
        </div>

        <!-- ── Registration form ── -->
        <div class="card-body-form">
            <h1 class="form-heading">Create your account</h1>
            <p class="form-subheading">Join us and start gifting with style</p>

            <?php if (!empty($error)): ?>
                <div class="auth-alert auth-alert-error">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST" id="registerForm" novalidate>
                <div class="fields-grid">

                    <div class="field-wrap full-col">
                        <label for="full_name">Full Name</label>
                        <div class="field-inner">
                            <i class="bi bi-person field-icon"></i>
                            <input type="text" id="full_name" name="full_name" required
                                   placeholder="e.g. Jane Doe"
                                   value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                        </div>
                    </div>

                    <div class="field-wrap full-col">
                        <label for="email">Email Address</label>
                        <div class="field-inner">
                            <i class="bi bi-envelope field-icon"></i>
                            <input type="email" id="email" name="email" required
                                   placeholder="e.g. jane@example.com"
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                    </div>

                    <div class="field-wrap full-col">
                        <label for="username">Username</label>
                        <div class="field-inner">
                            <i class="bi bi-at field-icon"></i>
                            <input type="text" id="username" name="username" required
                                   placeholder="Choose a username"
                                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                        </div>
                    </div>

                    <div class="field-wrap">
                        <label for="password">Password</label>
                        <div class="field-inner">
                            <i class="bi bi-lock field-icon"></i>
                            <input type="password" id="password" name="password" required
                                   placeholder="Min. 6 chars" autocomplete="new-password">
                            <i class="bi bi-eye toggle-pw" id="togglePw" title="Show / hide"></i>
                        </div>
                        <div class="pw-strength-bar" id="pwStrengthBar">
                            <span></span><span></span><span></span><span></span>
                        </div>
                        <div class="pw-strength-label" id="pwStrengthLabel"></div>
                    </div>

                    <div class="field-wrap">
                        <label for="confirm_password">Confirm Password</label>
                        <div class="field-inner">
                            <i class="bi bi-shield-lock field-icon"></i>
                            <input type="password" id="confirm_password" name="confirm_password" required
                                   placeholder="Repeat password" autocomplete="new-password">
                        </div>
                    </div>

                </div><!-- /.fields-grid -->

                <div class="terms-row">
                    <input type="checkbox" id="agreeTerms" required>
                    <label for="agreeTerms">
                        I agree to receive order updates and promotions from Orchid Gifts &amp; More.
                    </label>
                </div>

                <button type="submit" class="btn-register" id="registerBtn">
                    <i class="bi bi-bag-heart me-2"></i>Create Free Account
                </button>
            </form>

            <div class="card-footer-link">
                Already have an account? <a href="login.php">Sign in here</a>
            </div>

            <div class="benefits-strip">
                <div class="benefit-chip"><i class="bi bi-gift"></i> Exclusive offers</div>
                <div class="benefit-chip"><i class="bi bi-truck"></i> Track orders</div>
                <div class="benefit-chip"><i class="bi bi-heart"></i> Wishlist</div>
            </div>
        </div><!-- /.card-body-form -->

    </div><!-- /.auth-card -->
</div><!-- /.auth-wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Password toggle
    const togglePw = document.getElementById('togglePw');
    const pwInput  = document.getElementById('password');
    togglePw.addEventListener('click', () => {
        const show = pwInput.type === 'password';
        pwInput.type = show ? 'text' : 'password';
        togglePw.classList.toggle('bi-eye',       !show);
        togglePw.classList.toggle('bi-eye-slash',  show);
    });

    // Strength meter
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

    // Confirm match feedback
    document.getElementById('confirm_password').addEventListener('input', function () {
        this.style.borderColor = (this.value && this.value !== pwInput.value) ? '#ef4444' : '';
    });

    // Submit loading state
    document.getElementById('registerForm').addEventListener('submit', function (e) {
        if (!document.getElementById('agreeTerms').checked) {
            e.preventDefault();
            alert('Please agree to the terms before creating your account.');
            return;
        }
        const btn = document.getElementById('registerBtn');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating account…';
        btn.disabled = true;
    });
</script>
</body>
</html>
