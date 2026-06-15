<?php
// google-callback.php - Handles callback from Google OAuth2
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/config.php';

// If credentials are still placeholders, fail gracefully
if (GOOGLE_CLIENT_ID === 'YOUR_GOOGLE_CLIENT_ID_PLACEHOLDER' || GOOGLE_CLIENT_SECRET === 'YOUR_GOOGLE_CLIENT_SECRET_PLACEHOLDER') {
    header("Location: login.php?error=" . urlencode("Google API credentials are not configured."));
    exit();
}

// 1. Validate authorization parameters
if (!isset($_GET['code']) || !isset($_GET['state'])) {
    header("Location: login.php?error=" . urlencode("Authorization flow interrupted."));
    exit();
}

// 2. CSRF State Verification
if (!isset($_SESSION['oauth_state']) || $_GET['state'] !== $_SESSION['oauth_state']) {
    header("Location: login.php?error=" . urlencode("Invalid session state token. Please try again."));
    exit();
}
unset($_SESSION['oauth_state']); // Clear state token once verified

$code = $_GET['code'];

// 3. Exchange authorization code for access and ID token
$token_url = 'https://oauth2.googleapis.com/token';
$post_fields = [
    'code'          => $code,
    'client_id'     => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri'  => GOOGLE_REDIRECT_URI,
    'grant_type'    => 'authorization_code'
];

$ch = curl_init($token_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_fields));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

$token_response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200) {
    header("Location: login.php?error=" . urlencode("Could not exchange token with Google."));
    exit();
}

$token_data = json_decode($token_response, true);
$access_token = $token_data['access_token'] ?? '';

if (empty($access_token)) {
    header("Location: login.php?error=" . urlencode("Access token not found in Google response."));
    exit();
}

// 4. Retrieve user profile information using the access token
$profile_url = 'https://www.googleapis.com/oauth2/v3/userinfo';
$ch = curl_init($profile_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $access_token
]);
$profile_response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200) {
    header("Location: login.php?error=" . urlencode("Could not retrieve user info from Google."));
    exit();
}

$profile_data = json_decode($profile_response, true);
$google_id = $profile_data['sub'] ?? '';
$email     = $profile_data['email'] ?? '';
$full_name = $profile_data['name'] ?? '';

if (empty($google_id) || empty($email)) {
    header("Location: login.php?error=" . urlencode("Incomplete user profile returned from Google."));
    exit();
}

// 5. Query and map user account to DB
try {
    global $pdo;
    
    // Check if google_id already exists in users table
    $stmt = $pdo->prepare("SELECT * FROM users WHERE google_id = ?");
    $stmt->execute([$google_id]);
    $user = $stmt->fetch();
    
    if ($user) {
        // Log in the user
        if ($user['status'] !== 'Active') {
            header("Location: login.php?error=" . urlencode("This account has been deactivated."));
            exit();
        }
        
        $_SESSION['user_id']   = $user['user_id'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['role']      = $user['role'];
        $_SESSION['full_name'] = $user['full_name'];
        
        header("Location: customer/index.php");
        exit();
    }
    
    // Check if the email is already registered (link accounts)
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user) {
        if ($user['status'] !== 'Active') {
            header("Location: login.php?error=" . urlencode("Associated account is deactivated."));
            exit();
        }
        
        // Link google_id to the existing user record
        $stmt = $pdo->prepare("UPDATE users SET google_id = ? WHERE user_id = ?");
        $stmt->execute([$google_id, $user['user_id']]);
        
        $_SESSION['user_id']   = $user['user_id'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['role']      = $user['role'];
        $_SESSION['full_name'] = $user['full_name'];
        
        header("Location: customer/index.php");
        exit();
    }
    
    // If user does not exist, auto-register them
    // Generate a unique username based on the email address prefix
    $username_base = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', strstr($email, '@', true)));
    if (empty($username_base)) {
        $username_base = 'googleuser';
    }
    
    $username = $username_base;
    $counter = 1;
    while (true) {
        $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $check_stmt->execute([$username]);
        if ($check_stmt->fetchColumn() == 0) {
            break;
        }
        $username = $username_base . $counter;
        $counter++;
    }
    
    // Generate secure randomized password hash since password column cannot be null
    $random_password = bin2hex(random_bytes(16));
    $hashed_password = password_hash($random_password, PASSWORD_BCRYPT);
    
    // Create new customer account
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, role, status, google_id) VALUES (?, ?, ?, ?, 'customer', 'Active', ?)");
    $stmt->execute([$username, $email, $hashed_password, $full_name, $google_id]);
    
    $new_user_id = $pdo->lastInsertId();
    
    $_SESSION['user_id']   = $new_user_id;
    $_SESSION['username']  = $username;
    $_SESSION['role']      = 'customer';
    $_SESSION['full_name'] = $full_name;
    
    header("Location: customer/index.php");
    exit();
    
} catch (PDOException $e) {
    header("Location: login.php?error=" . urlencode("Database error occurred during login."));
    exit();
}
?>
