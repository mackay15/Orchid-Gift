<?php
// google-oauth.php - Initiator for Google OAuth Flow
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/config.php';

// If credentials are still placeholders, show a friendly configuration message
if (GOOGLE_CLIENT_ID === 'YOUR_GOOGLE_CLIENT_ID_PLACEHOLDER' || GOOGLE_CLIENT_SECRET === 'YOUR_GOOGLE_CLIENT_SECRET_PLACEHOLDER') {
    die("<div style='font-family: sans-serif; padding: 40px; text-align: center; max-width: 600px; margin: auto;'>
            <h2 style='color: #d9453b;'>Google Sign-In Configuration Required</h2>
            <p>Please edit <code>includes/config.php</code> and set your real Google API credentials.</p>
            <p><a href='login.php' style='display: inline-block; margin-top: 15px; padding: 10px 20px; background-color: #F4714A; color: white; text-decoration: none; border-radius: 5px;'>Back to Login</a></p>
         </div>");
}

// Generate secure anti-CSRF state token
$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;

// Build Google OAuth authorization URL
$params = [
    'response_type' => 'code',
    'client_id'     => GOOGLE_CLIENT_ID,
    'redirect_uri'  => GOOGLE_REDIRECT_URI,
    'scope'         => 'openid email profile',
    'state'         => $state,
    'prompt'        => 'select_account'
];

$auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);

// Redirect user to Google authorization consent screen
header('Location: ' . $auth_url);
exit();
?>
