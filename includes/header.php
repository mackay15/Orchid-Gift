<?php
// includes/header.php - Global Navigation Header
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/auth.php';

// Base URL computed dynamically for server environment compatibility
$project_root = str_replace('\\', '/', dirname(__DIR__));
$doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$base = '';
if (strcasecmp(substr($project_root, 0, strlen($doc_root)), $doc_root) === 0) {
    $base = substr($project_root, strlen($doc_root));
}
$base = str_replace('\\', '/', rtrim($base, '/'));

// Count cart items
$cart_count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $qty) {
        $cart_count += $qty;
    }
}

// Helper function to resolve product image paths (local upload or remote URL)
function getProductImage($url) {
    global $base;
    if (empty($url)) {
        return 'https://images.unsplash.com/photo-1513151233558-d860c5398176?auto=format&fit=crop&w=600&q=80';
    }
    if (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0 || strpos($url, 'data:') === 0) {
        return $url;
    }
    return $base . '/' . ltrim($url, '/');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ORCHID - Gift & More Management System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?php echo $base; ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>

<!-- Dynamic Navbar -->
<nav class="navbar navbar-expand-lg glass-nav sticky-top py-3">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-3" href="<?php echo $base; ?>/index.php">
            <img src="<?php echo $base; ?>/assets/orchid_logo.png" alt="Orchid Gift & More Logo" class="brand-logo">
            <div class="d-flex flex-column">
                <span class="brand-text">ORCHID</span>
                <small class="text-muted small">Gift & More</small>
            </div>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $base; ?>/index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $base; ?>/customer/shop.php">Shop Gifts</a>
                </li>
            </ul>
            
            <div class="d-flex align-items-center gap-3">
                <!-- Customer Search Bar (Quick search redirection) -->
                <form class="d-none d-lg-flex" action="<?php echo $base; ?>/customer/shop.php" method="GET">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search gifts..." aria-label="Search">
                        <button class="btn btn-sm btn-orchid" type="submit"><i class="bi bi-search"></i></button>
                    </div>
                </form>

                <!-- Customer Wishlist and Cart -->
                <a href="<?php echo $base; ?>/customer/wishlist.php" class="btn btn-link text-dark position-relative p-1">
                    <i class="bi bi-heart fs-5 text-danger"></i>
                </a>
                
                <a href="<?php echo $base; ?>/customer/cart.php" class="btn btn-link text-dark position-relative p-1 me-2">
                    <i class="bi bi-cart3 fs-5"></i>
                    <?php if ($cart_count > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.7rem;">
                            <?php echo $cart_count; ?>
                        </span>
                    <?php endif; ?>
                </a>

                <?php if (isLoggedIn()): ?>
                    <div class="dropdown">
                        <button class="btn btn-orchid dropdown-toggle" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" aria-labelledby="userMenu">
                            <?php if (hasRole('admin')): ?>
                                <li><a class="dropdown-item" href="<?php echo $base; ?>/admin/index.php"><i class="bi bi-speedometer2 me-2"></i>Admin Panel</a></li>
                            <?php elseif (hasRole('cashier')): ?>
                                <li><a class="dropdown-item" href="<?php echo $base; ?>/cashier/index.php"><i class="bi bi-cpu me-2"></i>Cashier POS</a></li>
                            <?php else: ?>
                                <li><a class="dropdown-item" href="<?php echo $base; ?>/customer/index.php"><i class="bi bi-bag-heart me-2"></i>My Portal</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?php echo $base; ?>/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?php echo $base; ?>/login.php" class="btn btn-orchid-outline">Login</a>
                    <a href="<?php echo $base; ?>/register.php" class="btn btn-orchid">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- Main Wrapper to allow footer sticking -->
<div class="min-vh-100 d-flex flex-column justify-content-between">
    <main class="flex-grow-1">
