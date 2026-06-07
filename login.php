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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --primary:       #5a189a;
            --primary-light: #9d4edd;
            --accent-red:    #b91c1c;
            --text-dark:     #2c1f45;
            --text-mid:      #6b5b82;
        }

        html, body { height: 100%; font-family: 'Outfit', sans-serif; }

        /* ── Full-screen shop background ── */
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

        /* Layered dark + tinted overlay */
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

        /* Ambient bokeh blobs */
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
        .bokeh-3 { width: 240px; height: 240px; background: radial-gradient(circle, rgba(247,37,133,0.14), transparent 65%); top: 45%; left: 28%; animation-delay: -9s; }

        @keyframes drift {
            0%, 100% { transform: translate(0, 0)    scale(1);    }
            33%       { transform: translate(15px, -20px) scale(1.04); }
            66%       { transform: translate(-10px, 12px) scale(0.97); }
        }

        /* ── Auth card wrapper ── */
        .auth-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 460px;
            animation: riseIn 0.65s cubic-bezier(0.34, 1.56, 0.64, 1) both;
        }

        @keyframes riseIn {
            from { opacity: 0; transform: translateY(50px) scale(0.97); }
            to   { opacity: 1; transform: translateY(0)    scale(1);    }
        }

        /* ── Glassmorphic card ── */
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

        /* ── Card header band with logo inside ── */
        .card-brand-header {
            background: linear-gradient(145deg, #1a0530, #3a0f6e, #5a189a);
            padding: 36px 32px 28px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 14px;
            position: relative;
            overflow: hidden;
        }

        /* Subtle pattern inside header */
        .card-brand-header::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 20% 0%, rgba(247,37,133,0.18) 0%, transparent 45%),
                radial-gradient(circle at 85% 100%, rgba(255,133,161,0.14) 0%, transparent 40%);
            pointer-events: none;
        }

        /* ── Logo inside header ── */
        .card-logo-ring {
            position: relative;
            z-index: 1;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            overflow: hidden;              /* clips image corners to circle */
            /* Glowing ring effect */
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
            font-size: 1.5rem;
            font-weight: 700;
            color: #fff;
            text-align: center;
            letter-spacing: 0.05em;
            line-height: 1.2;
            text-shadow: 0 2px 8px rgba(0,0,0,0.4);
        }

        .card-brand-tagline {
            position: relative;
            z-index: 1;
            font-size: 0.78rem;
            color: rgba(255,255,255,0.65);
            letter-spacing: 0.16em;
            text-transform: uppercase;
            margin-top: -8px;
        }

        /* Curved bottom of the header for smooth transition */
        .card-brand-header::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            right: 0;
            height: 28px;
            background: rgba(255,255,255,0.86);
            border-radius: 28px 28px 0 0;
        }

        /* ── Card body (form area) ── */
        .card-body-form {
            padding: 28px 36px 36px;
        }

        .form-heading {
            font-family: 'Playfair Display', serif;
            font-size: 1.35rem;
            color: var(--text-dark);
            font-weight: 700;
            text-align: center;
            margin-bottom: 4px;
        }

        .form-subheading {
            font-size: 0.86rem;
            color: var(--text-mid);
            text-align: center;
            margin-bottom: 24px;
        }

        /* ── Input fields ── */
        .field-wrap {
            margin-bottom: 18px;
        }

        .field-wrap label {
            display: block;
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--text-mid);
            margin-bottom: 7px;
            letter-spacing: 0.02em;
        }

        .field-inner {
            position: relative;
            display: flex;
            align-items: center;
        }

        .field-icon {
            position: absolute;
            left: 14px;
            color: #a88cc8;
            font-size: 1rem;
            z-index: 2;
            pointer-events: none;
        }

        .field-inner input {
            width: 100%;
            padding: 12px 14px 12px 42px;
            border: 1.5px solid rgba(90, 24, 154, 0.14);
            border-radius: 14px;
            font-family: 'Outfit', sans-serif;
            font-size: 0.95rem;
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

        .field-inner input::placeholder { color: #c4aee0; font-size: 0.88rem; }

        .toggle-pw {
            position: absolute;
            right: 14px;
            color: #a88cc8;
            cursor: pointer;
            font-size: 1rem;
            z-index: 2;
            transition: color 0.2s;
        }
        .toggle-pw:hover { color: var(--primary); }

        /* ── Alerts ── */
        .auth-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 14px;
            border-radius: 12px;
            font-size: 0.86rem;
            margin-bottom: 18px;
        }
        .auth-alert-error   { background: rgba(220,38,38,0.10); border: 1px solid rgba(220,38,38,0.22); color: #b91c1c; }
        .auth-alert-success { background: rgba(22,163,74,0.10);  border: 1px solid rgba(22,163,74,0.25);  color: #15803d; }

        /* ── Submit button ── */
        .btn-signin {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #3d0d75, #5a189a, #7b2cbf);
            color: #fff;
            border: none;
            border-radius: 16px;
            font-family: 'Outfit', sans-serif;
            font-size: 0.97rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(90,24,154,0.32);
            margin-top: 8px;
            position: relative;
            overflow: hidden;
        }
        .btn-signin::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.12), transparent);
            transition: left 0.5s ease;
        }
        .btn-signin:hover { transform: translateY(-2px); box-shadow: 0 16px 40px rgba(90,24,154,0.40); }
        .btn-signin:hover::before { left: 100%; }
        .btn-signin:active { transform: translateY(0); }

        /* ── Footer links ── */
        .card-footer-link {
            text-align: center;
            margin-top: 18px;
            font-size: 0.86rem;
            color: var(--text-mid);
        }
        .card-footer-link a {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
        }
        .card-footer-link a:hover { color: var(--primary-light); text-decoration: underline; }

        /* ── Staff hint ── */
        .staff-hint {
            margin-top: 16px;
            padding: 10px 14px;
            background: rgba(90,24,154,0.06);
            border: 1px solid rgba(90,24,154,0.11);
            border-radius: 12px;
            font-size: 0.77rem;
            color: #7a5f9f;
            text-align: center;
        }
        .staff-hint code {
            background: rgba(90,24,154,0.09);
            color: var(--primary);
            border-radius: 5px;
            padding: 1px 5px;
        }

        @media (max-width: 480px) {
            .card-body-form { padding: 24px 22px 30px; }
            .card-brand-header { padding: 28px 22px 24px; }
            .card-logo-ring { width: 82px; height: 82px; }
            .card-logo-ring img { width: 70px; height: 70px; }
            .card-brand-name { font-size: 1.25rem; }
        }
    </style>
</head>
<body>

<!-- Ambient bokeh blobs -->
<div class="bokeh bokeh-1"></div>
<div class="bokeh bokeh-2"></div>
<div class="bokeh bokeh-3"></div>

<div class="auth-wrapper">
    <div class="auth-card">

        <!-- ── Brand header with logo inside the card ── -->
        <div class="card-brand-header">
            <div class="card-logo-ring">
                <img src="assets/orchid_logo.png" alt="Orchid Gifts & More Logo">
            </div>
            <div class="card-brand-name">Orchid Gifts &amp; More</div>
            <div class="card-brand-tagline">Where every gift tells a story</div>
        </div>

        <!-- ── Form body ── -->
        <div class="card-body-form">
            <h1 class="form-heading">Welcome back</h1>
            <p class="form-subheading">Sign in to continue to your account</p>

            <?php if (!empty($error)): ?>
                <div class="auth-alert auth-alert-error">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_msg)): ?>
                <div class="auth-alert auth-alert-success">
                    <i class="bi bi-check-circle-fill"></i>
                    <?php echo htmlspecialchars($success_msg); ?>
                </div>
            <?php endif; ?>

            <form action="login.php?redirect=<?php echo urlencode($redirect); ?>" method="POST" novalidate>

                <div class="field-wrap">
                    <label for="username">Username or Email</label>
                    <div class="field-inner">
                        <i class="bi bi-person field-icon"></i>
                        <input type="text" id="username" name="username" required
                               placeholder="Enter your username or email"
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                               autocomplete="username">
                    </div>
                </div>

                <div class="field-wrap">
                    <label for="password">Password</label>
                    <div class="field-inner">
                        <i class="bi bi-lock field-icon"></i>
                        <input type="password" id="password" name="password" required
                               placeholder="Enter your password"
                               autocomplete="current-password">
                        <i class="bi bi-eye toggle-pw" id="togglePw" title="Show / hide"></i>
                    </div>
                </div>

                <button type="submit" class="btn-signin" id="loginBtn">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                </button>
            </form>

            <div class="card-footer-link">
                Don't have an account? <a href="register.php">Create one free</a>
            </div>

            <div class="staff-hint">
                <strong>Staff:</strong>&nbsp;
                Admin — <code>admin</code> / <code>admin123</code> &nbsp;·&nbsp;
                Cashier — <code>cashier</code> / <code>cashier123</code>
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

    // Loading state
    document.querySelector('form').addEventListener('submit', () => {
        const btn = document.getElementById('loginBtn');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Signing in…';
        btn.disabled = true;
    });
</script>
</body>
</html>
