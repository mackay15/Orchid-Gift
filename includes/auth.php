<?php
// includes/auth.php - Authentication and Authorization helpers
require_once __DIR__ . '/db.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUser() {
    global $pdo;
    if (!isLoggedIn()) {
        return null;
    }
    
    $stmt = $pdo->prepare("SELECT user_id, username, email, full_name, role, status FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function hasRole($roles) {
    if (!isLoggedIn()) return false;
    
    $user = getCurrentUser();
    if (!$user || $user['status'] !== 'Active') return false;
    
    if (is_array($roles)) {
        return in_array($user['role'], $roles);
    }
    return $user['role'] === $roles;
}

function requireRole($roles) {
    if (!isLoggedIn()) {
        header("Location: ../login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
        exit();
    }
    
    if (!hasRole($roles)) {
        // Redirect to their default dashboard depending on who they are
        $user = getCurrentUser();
        if ($user) {
            if ($user['role'] === 'admin') {
                header("Location: ../admin/index.php");
            } elseif ($user['role'] === 'cashier') {
                header("Location: ../cashier/index.php");
            } else {
                header("Location: ../customer/index.php");
            }
        } else {
            header("Location: ../login.php");
        }
        exit();
    }
}

function loginUser($usernameOrEmail, $password) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$usernameOrEmail, $usernameOrEmail]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        if ($user['status'] !== 'Active') {
            return "This account has been deactivated. Please contact support.";
        }
        
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['full_name'] = $user['full_name'];
        return true;
    }
    
    return "Invalid username/email or password.";
}

function registerUser($username, $email, $password, $fullName) {
    global $pdo;
    
    // Check if username or email exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetchColumn() > 0) {
        return "Username or Email is already registered.";
    }
    
    $hashedPass = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, role, status) VALUES (?, ?, ?, ?, 'customer', 'Active')");
    $stmt->execute([$username, $email, $hashedPass, $fullName]);
    
    return true;
}

function logCashierAction($action, $details = '') {
    global $pdo;
    if (isLoggedIn() && isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
        $role = $_SESSION['role'] ?? '';
        if ($role === 'cashier' || $role === 'admin') {
            $stmt = $pdo->prepare("INSERT INTO cashier_logs (cashier_id, action, details) VALUES (?, ?, ?)");
            $stmt->execute([$userId, $action, $details]);
        }
    }
}
?>
