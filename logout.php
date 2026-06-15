<?php
// logout.php - Session destruction utility
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    logCashierAction('Logout', 'Logged out of system portal.');
}

$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

header("Location: index.php");
exit();
?>
