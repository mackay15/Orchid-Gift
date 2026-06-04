<?php
// customer/orders.php - Order History & Invoice View
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

// Force login
if (!isLoggedIn()) {
    header("Location: ../login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

$user_id = $_SESSION['user_id'];
$success_id = isset($_GET['success_id']) ? intval($_GET['success_id']) : 0;
$view_id = isset($_GET['view_id']) ? intval($_GET['view_id']) : 0;

// Fetch order list
$stmt = $pdo->prepare("SELECT * FROM orders WHERE customer_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();

// Handle detailed view
$view_order = null;
$view_items = [];
if ($view_id > 0) {
    // Confirm order belongs to customer
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = ? AND customer_id = ?");
    $stmt->execute([$view_id, $user_id]);
    $view_order = $stmt->fetch();
    
    if ($view_order) {
        $stmt = $pdo->prepare("SELECT oi.*, p.name, p.image_url FROM order_items oi JOIN products p ON oi.product_id = p.product_id WHERE oi.order_id = ?");
        $stmt->execute([$view_id]);
        $view_items = $stmt->fetchAll();
    }
}

// Handle success order display
if ($success_id > 0 && empty($view_order)) {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = ? AND customer_id = ?");
    $stmt->execute([$success_id, $user_id]);
    $view_order = $stmt->fetch();
    
    if ($view_order) {
        $stmt = $pdo->prepare("SELECT oi.*, p.name, p.image_url FROM order_items oi JOIN products p ON oi.product_id = p.product_id WHERE oi.order_id = ?");
        $stmt->execute([$success_id]);
        $view_items = $stmt->fetchAll();
        $view_id = $success_id;
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <?php if ($success_id > 0 && $view_order): ?>
        <!-- Celebrating Banner -->
        <div class="card border-0 bg-success bg-opacity-10 shadow-sm rounded-4 p-4 text-center mb-5 border-start border-success border-4">
            <i class="bi bi-patch-check-fill text-success fs-1 mb-2"></i>
            <h3 class="fw-bold text-success-emphasis">Order Placed Successfully!</h3>
            <p class="text-secondary small">Thank you for your order. We are preparing your gift arrangement. Order reference: <b>#<?php echo $success_id; ?></b></p>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- List of Orders -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <h4 class="brand-font mb-4 text-dark"><i class="bi bi-clock-history text-primary me-2"></i>Order History</h4>
                
                <?php if (empty($orders)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-bag-x fs-1 d-block mb-3"></i>
                        <p>No orders placed yet.</p>
                        <a href="shop.php" class="btn btn-orchid btn-sm mt-2">Start Shopping</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr class="text-muted small">
                                    <th>Order #</th>
                                    <th>Date</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $o): 
                                    $status_badge = 'bg-secondary';
                                    if ($o['order_status'] === 'Pending') $status_badge = 'bg-warning-subtle text-warning-emphasis';
                                    elseif ($o['order_status'] === 'Processing') $status_badge = 'bg-primary-subtle text-primary';
                                    elseif ($o['order_status'] === 'Completed') $status_badge = 'bg-success-subtle text-success';
                                    elseif ($o['order_status'] === 'Cancelled') $status_badge = 'bg-danger-subtle text-danger';
                                    
                                    $pay_badge = ($o['payment_status'] === 'Paid') ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger';
                                ?>
                                    <tr style="cursor: pointer;" onclick="location.href='orders.php?view_id=<?php echo $o['order_id']; ?>'">
                                        <td><b>#<?php echo $o['order_id']; ?></b></td>
                                        <td class="small text-muted"><?php echo date('M d, Y', strtotime($o['created_at'])); ?></td>
                                        <td class="fw-bold text-primary">$<?php echo number_format($o['total_amount'], 2); ?></td>
                                        <td><span class="badge <?php echo $status_badge; ?> px-2 py-1.5"><?php echo $o['order_status']; ?></span></td>
                                        <td><span class="badge <?php echo $pay_badge; ?>"><?php echo $o['payment_status']; ?></span></td>
                                        <td>
                                            <a href="orders.php?view_id=<?php echo $o['order_id']; ?>" class="btn btn-orchid-outline btn-xs py-1 px-2.5 rounded-pill" style="font-size: 0.75rem;">View Invoice</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Detailed Invoice/Receipt View -->
        <div class="col-lg-5">
            <?php if ($view_order): ?>
                <div class="card border-0 shadow-sm rounded-4 p-4 glass-panel">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0 brand-font text-dark">Invoice #<?php echo $view_order['order_id']; ?></h5>
                        <span class="text-muted small"><?php echo date('M d, Y H:i', strtotime($view_order['created_at'])); ?></span>
                    </div>
                    
                    <div class="mb-4 small">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Order Type:</span>
                            <span class="fw-semibold text-uppercase"><?php echo $view_order['order_type']; ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Delivery Status:</span>
                            <span class="fw-bold text-primary"><?php echo $view_order['order_status']; ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Payment:</span>
                            <span class="fw-semibold"><?php echo $view_order['payment_status']; ?></span>
                        </div>
                    </div>
                    
                    <h6 class="fw-bold mb-3 small text-muted text-uppercase" style="letter-spacing: 0.5px;">Items</h6>
                    <div class="d-flex flex-column gap-3 mb-4">
                        <?php foreach ($view_items as $item): ?>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="rounded" style="width: 40px; height: 40px; object-fit: cover;">
                                    <div>
                                        <h6 class="mb-0 small fw-bold text-dark"><?php echo htmlspecialchars($item['name']); ?></h6>
                                        <small class="text-muted">Qty: <?php echo $item['quantity']; ?> × $<?php echo number_format($item['unit_price'], 2); ?></small>
                                    </div>
                                </div>
                                <span class="fw-bold small text-primary">$<?php echo number_format($item['total_price'], 2); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <hr style="border-color: rgba(90, 24, 154, 0.15);">
                    
                    <!-- Computation -->
                    <?php 
                        $items_total = 0;
                        foreach ($view_items as $item) $items_total += $item['total_price'];
                        $tax_total = $items_total * 0.05;
                    ?>
                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Subtotal</span>
                        <span class="fw-semibold">$<?php echo number_format($items_total, 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3 small">
                        <span class="text-muted">VAT (5%)</span>
                        <span class="fw-semibold">$<?php echo number_format($tax_total, 2); ?></span>
                    </div>
                    
                    <hr style="border-color: rgba(90, 24, 154, 0.15);">
                    
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="fw-bold text-dark">Grand Total</span>
                        <span class="fw-bold text-primary fs-5">$<?php echo number_format($view_order['total_amount'], 2); ?></span>
                    </div>
                    
                    <button class="btn btn-orchid btn-sm w-100" onclick="window.print()"><i class="bi bi-printer me-2"></i> Print Invoice / PDF</button>
                </div>
            <?php else: ?>
                <div class="card border-0 shadow-sm rounded-4 p-5 text-center text-muted">
                    <i class="bi bi-info-circle fs-2 mb-2 d-block"></i>
                    <p class="mb-0">Select an order from the list to view its complete receipt invoice details.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
