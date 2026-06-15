<?php
// customer/index.php - Customer Portal Dashboard
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

// Force customer role check
requireRole('customer');

$user_id = $_SESSION['user_id'];

// Get Stats
// 1. Total Orders
$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE customer_id = ?");
$stmt->execute([$user_id]);
$total_orders = $stmt->fetchColumn();

// 2. Active Orders
$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE customer_id = ? AND order_status IN ('Pending', 'Processing')");
$stmt->execute([$user_id]);
$active_orders = $stmt->fetchColumn();

// 3. Wishlist Count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM wishlists WHERE customer_id = ?");
$stmt->execute([$user_id]);
$wishlist_count = $stmt->fetchColumn();

// Recent 3 Orders
$stmt = $pdo->prepare("SELECT * FROM orders WHERE customer_id = ? ORDER BY created_at DESC LIMIT 3");
$stmt->execute([$user_id]);
$recent_orders = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <!-- Welcome banner -->
    <div class="card border-0 bg-orchid-grad text-white rounded-4 p-4 p-lg-5 mb-5 shadow">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="display-6 brand-font mb-2">Welcome back, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h1>
                <p class="mb-0 text-white-50">Welcome to your personal gift portal. Track order dispatches, manage saved wishlists, and write reviews for your purchases.</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="shop.php" class="btn btn-light text-primary px-4 py-2 rounded-pill fw-bold"><i class="bi bi-gift-fill me-1"></i> Send a Gift</a>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card stat-card bg-orchid-grad">
                <h6 class="text-white-50 text-uppercase small fw-bold">Total Orders</h6>
                <div class="d-flex align-items-center justify-content-between mt-2">
                    <span class="fs-1 fw-bold"><?php echo $total_orders; ?></span>
                    <i class="bi bi-bag-check fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card stat-card bg-rose-grad">
                <h6 class="text-white-50 text-uppercase small fw-bold">Active Orders</h6>
                <div class="d-flex align-items-center justify-content-between mt-2">
                    <span class="fs-1 fw-bold"><?php echo $active_orders; ?></span>
                    <i class="bi bi-truck fs-1 text-white-50"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card stat-card bg-ocean-grad">
                <h6 class="text-white-50 text-uppercase small fw-bold">Wishlist Items</h6>
                <div class="d-flex align-items-center justify-content-between mt-2">
                    <span class="fs-1 fw-bold"><?php echo $wishlist_count; ?></span>
                    <i class="bi bi-heart-fill fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Recent Orders -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0 brand-font text-dark"><i class="bi bi-truck-flatbed text-primary me-2"></i>Recent Purchases</h5>
                    <a href="orders.php" class="small text-decoration-none text-primary">View All</a>
                </div>

                <?php if (empty($recent_orders)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-calendar-event fs-1 mb-2 d-block text-primary text-opacity-25"></i>
                        <p>No recent orders found.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr class="text-muted small">
                                    <th>Order #</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Invoice</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_orders as $o): 
                                    $status_badge = 'bg-secondary';
                                    if ($o['order_status'] === 'Pending') $status_badge = 'bg-warning-subtle text-warning-emphasis';
                                    elseif ($o['order_status'] === 'Processing') $status_badge = 'bg-primary-subtle text-primary';
                                    elseif ($o['order_status'] === 'Completed') $status_badge = 'bg-success-subtle text-success';
                                    elseif ($o['order_status'] === 'Cancelled') $status_badge = 'bg-danger-subtle text-danger';
                                ?>
                                    <tr>
                                        <td><b>#<?php echo $o['order_id']; ?></b></td>
                                        <td class="small text-muted"><?php echo date('M d, Y', strtotime($o['created_at'])); ?></td>
                                        <td class="fw-bold text-primary"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($o['total_amount'], 2); ?></td>
                                        <td><span class="badge <?php echo $status_badge; ?>"><?php echo $o['order_status']; ?></span></td>
                                        <td><a href="orders.php?view_id=<?php echo $o['order_id']; ?>" class="btn btn-link btn-sm text-primary p-0 text-decoration-none"><i class="bi bi-receipt me-1"></i> View</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Shortcuts / Quick links -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 glass-panel">
                <h5 class="fw-bold mb-4 brand-font text-dark">Quick Services</h5>
                
                <div class="d-flex flex-column gap-3">
                    <a href="shop.php" class="d-flex align-items-center gap-3 p-3 border rounded-3 text-decoration-none text-dark bg-white shadow-sm" style="transition: var(--transition-smooth);">
                        <span class="p-2 bg-primary bg-opacity-10 text-primary rounded-circle"><i class="bi bi-shop fs-5"></i></span>
                        <div>
                            <h6 class="mb-0 fw-bold">Browse Catalog</h6>
                            <small class="text-muted small">Explore gift arrangements</small>
                        </div>
                    </a>
                    
                    <a href="wishlist.php" class="d-flex align-items-center gap-3 p-3 border rounded-3 text-decoration-none text-dark bg-white shadow-sm" style="transition: var(--transition-smooth);">
                        <span class="p-2 bg-danger bg-opacity-10 text-danger rounded-circle"><i class="bi bi-heart fs-5"></i></span>
                        <div>
                            <h6 class="mb-0 fw-bold">View Wishlist</h6>
                            <small class="text-muted small">Saved items and gifts</small>
                        </div>
                    </a>

                    <a href="orders.php" class="d-flex align-items-center gap-3 p-3 border rounded-3 text-decoration-none text-dark bg-white shadow-sm" style="transition: var(--transition-smooth);">
                        <span class="p-2 bg-success bg-opacity-10 text-success rounded-circle"><i class="bi bi-receipt fs-5"></i></span>
                        <div>
                            <h6 class="mb-0 fw-bold">Order History</h6>
                            <small class="text-muted small">Invoice lists & printing</small>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
