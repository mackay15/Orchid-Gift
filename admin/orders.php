<?php
// admin/orders.php - Admin Order Tracker & Manager
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('admin');

$error = '';
$success = '';

// Handle Status Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $order_id = intval($_POST['order_id']);
    $new_status = $_POST['order_status'];
    $payment_status = $_POST['payment_status'];
    
    // Check validation of status
    if (in_array($new_status, ['Pending', 'Processing', 'Completed', 'Cancelled'])) {
        try {
            $pdo->beginTransaction();
            
            // If completed, make sure payment is Paid
            $pay_clause = '';
            $pay_status = $payment_status;
            if ($new_status === 'Completed') {
                $pay_status = 'Paid';
                
                // If payment was completed, make sure payment records are updated as well
                $stmtPayUpdate = $pdo->prepare("UPDATE payments SET payment_status = 'Completed' WHERE order_id = ?");
                $stmtPayUpdate->execute([$order_id]);
                
                // Update transaction ID if it was cash on delivery
                $stmtSaleUpdate = $pdo->prepare("UPDATE sales SET transaction_id = CONCAT('TXN', FLOOR(100000 + RAND() * 900000)) WHERE order_id = ? AND transaction_id IS NULL");
                $stmtSaleUpdate->execute([$order_id]);
            }
            
            $stmt = $pdo->prepare("UPDATE orders SET order_status = ?, payment_status = ? WHERE order_id = ?");
            $stmt->execute([$new_status, $pay_status, $order_id]);
            
            // Send customer notification alert
            $stmtCust = $pdo->prepare("SELECT customer_id FROM orders WHERE order_id = ?");
            $stmtCust->execute([$order_id]);
            $custId = $stmtCust->fetchColumn();
            
            if ($custId) {
                $notifMsg = "Your order #$order_id is now updated to: $new_status.";
                if ($new_status === 'Completed') {
                    $notifMsg .= " Thank you for shopping with us!";
                }
                $stmtN = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                $stmtN->execute([$custId, $notifMsg]);
            }
            
            $pdo->commit();
            $success = "Order status updated successfully!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = $e->getMessage();
        }
    }
}

// Filters
$status_filter = $_GET['status'] ?? 'All';
$type_filter = $_GET['type'] ?? 'All';

// Fetch orders
$query = "SELECT o.*, u.full_name as customer_name, c.full_name as cashier_name 
          FROM orders o 
          LEFT JOIN users u ON o.customer_id = u.user_id 
          LEFT JOIN users c ON o.cashier_id = c.user_id 
          WHERE 1=1";
$params = [];

if ($status_filter !== 'All') {
    $query .= " AND o.order_status = ?";
    $params[] = $status_filter;
}
if ($type_filter !== 'All') {
    $query .= " AND o.order_type = ?";
    $params[] = $type_filter;
}

$query .= " ORDER BY o.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Handle Order details retrieval for side panel
$view_id = isset($_GET['view_id']) ? intval($_GET['view_id']) : 0;
$view_order = null;
$view_items = [];

if ($view_id > 0) {
    $stmt = $pdo->prepare("SELECT o.*, u.full_name as customer_name, c.full_name as cashier_name 
                           FROM orders o 
                           LEFT JOIN users u ON o.customer_id = u.user_id 
                           LEFT JOIN users c ON o.cashier_id = c.user_id 
                           WHERE o.order_id = ?");
    $stmt->execute([$view_id]);
    $view_order = $stmt->fetch();
    
    if ($view_order) {
        $stmt = $pdo->prepare("SELECT oi.*, p.name, p.image_url FROM order_items oi JOIN products p ON oi.product_id = p.product_id WHERE oi.order_id = ?");
        $stmt->execute([$view_id]);
        $view_items = $stmt->fetchAll();
    }
}
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
        <div class="alert alert-success border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Orders list panel -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
                    <h4 class="brand-font mb-0 text-dark"><i class="bi bi-receipt text-primary me-2"></i>Manage Orders</h4>
                    
                    <!-- Filters controls -->
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm border-0 bg-light" onchange="location = 'orders.php?status=' + this.value + '&type=<?php echo $type_filter; ?>';">
                            <option value="All" <?php echo $status_filter === 'All' ? 'selected' : ''; ?>>All Statuses</option>
                            <option value="Pending" <?php echo $status_filter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="Processing" <?php echo $status_filter === 'Processing' ? 'selected' : ''; ?>>Processing</option>
                            <option value="Completed" <?php echo $status_filter === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="Cancelled" <?php echo $status_filter === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                        
                        <select class="form-select form-select-sm border-0 bg-light" onchange="location = 'orders.php?type=' + this.value + '&status=<?php echo $status_filter; ?>';">
                            <option value="All" <?php echo $type_filter === 'All' ? 'selected' : ''; ?>>All Channels</option>
                            <option value="online" <?php echo $type_filter === 'online' ? 'selected' : ''; ?>>Online Portal</option>
                            <option value="walk-in" <?php echo $type_filter === 'walk-in' ? 'selected' : ''; ?>>POS Walk-in</option>
                        </select>
                    </div>
                </div>

                <?php if (empty($orders)): ?>
                    <div class="text-center py-5 text-muted">No orders found matching the filter.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr class="text-muted small">
                                    <th>Order #</th>
                                    <th>Channel</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                    <th>Invoice</th>
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
                                    <tr style="cursor: pointer;" onclick="location.href='orders.php?view_id=<?php echo $o['order_id']; ?>&status=<?php echo $status_filter; ?>&type=<?php echo $type_filter; ?>'">
                                        <td><b>#<?php echo $o['order_id']; ?></b><br><small class="text-muted" style="font-size:0.75rem;"><?php echo date('M d H:i', strtotime($o['created_at'])); ?></small></td>
                                        <td class="text-uppercase small"><?php echo $o['order_type']; ?></td>
                                        <td class="fw-bold text-primary">$<?php echo number_format($o['total_amount'], 2); ?></td>
                                        <td><span class="badge <?php echo $status_badge; ?>"><?php echo $o['order_status']; ?></span></td>
                                        <td><span class="badge <?php echo $pay_badge; ?>"><?php echo $o['payment_status']; ?></span></td>
                                        <td><a href="orders.php?view_id=<?php echo $o['order_id']; ?>&status=<?php echo $status_filter; ?>&type=<?php echo $type_filter; ?>" class="btn btn-link btn-xs p-0 text-decoration-none">Review</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Orders details side panel -->
        <div class="col-lg-5">
            <?php if ($view_order): ?>
                <div class="card border-0 shadow-sm rounded-4 p-4 glass-panel">
                    <h5 class="fw-bold mb-4 brand-font text-dark">Order #<?php echo $view_order['order_id']; ?> details</h5>
                    
                    <div class="mb-4 small">
                        <div><b>Customer:</b> <?php echo htmlspecialchars($view_order['customer_name'] ?? 'Guest Customer'); ?></div>
                        <div><b>Processed by:</b> <?php echo htmlspecialchars($view_order['cashier_name'] ?? 'System/Online'); ?></div>
                        <div><b>Channel:</b> <span class="text-uppercase text-primary fw-bold"><?php echo $view_order['order_type']; ?></span></div>
                        <div><b>Placed At:</b> <?php echo date('F d, Y H:i:s', strtotime($view_order['created_at'])); ?></div>
                    </div>

                    <h6 class="fw-bold mb-3 small text-muted text-uppercase">Items list</h6>
                    <div class="d-flex flex-column gap-3 mb-4">
                        <?php foreach ($view_items as $item): ?>
                            <div class="d-flex justify-content-between align-items-center bg-white p-2 rounded shadow-sm">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="" class="rounded" style="width: 35px; height: 35px; object-fit: cover;">
                                    <div>
                                        <h6 class="mb-0 small fw-bold text-dark"><?php echo htmlspecialchars($item['name']); ?></h6>
                                        <small class="text-muted"><?php echo $item['quantity']; ?> × $<?php echo number_format($item['unit_price'], 2); ?></small>
                                    </div>
                                </div>
                                <span class="fw-bold small text-primary">$<?php echo number_format($item['total_price'], 2); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <hr style="border-color: rgba(90, 24, 154, 0.15);">

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="fw-bold text-dark">Grand Total:</span>
                        <span class="fw-bold text-primary fs-5">$<?php echo number_format($view_order['total_amount'], 2); ?></span>
                    </div>
                    
                    <!-- Change Status Form (Only allowed if order type is online, POS orders are instantly completed) -->
                    <?php if ($view_order['order_type'] === 'online'): ?>
                        <form action="orders.php?view_id=<?php echo $view_id; ?>&status=<?php echo $status_filter; ?>&type=<?php echo $type_filter; ?>" method="POST" class="border-top pt-3">
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="order_id" value="<?php echo $view_id; ?>">
                            
                            <div class="mb-3">
                                <label for="order_status" class="form-label text-muted small fw-bold">Update Dispatch Status</label>
                                <select name="order_status" id="order_status" class="form-select form-select-sm">
                                    <option value="Pending" <?php echo $view_order['order_status'] === 'Pending' ? 'selected' : ''; ?>>Pending Approval</option>
                                    <option value="Processing" <?php echo $view_order['order_status'] === 'Processing' ? 'selected' : ''; ?>>In Processing</option>
                                    <option value="Completed" <?php echo $view_order['order_status'] === 'Completed' ? 'selected' : ''; ?>>Completed & Dispatched</option>
                                    <option value="Cancelled" <?php echo $view_order['order_status'] === 'Cancelled' ? 'selected' : ''; ?>>Cancelled / Refunded</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="payment_status" class="form-label text-muted small fw-bold">Update Payment Status</label>
                                <select name="payment_status" id="payment_status" class="form-select form-select-sm">
                                    <option value="Unpaid" <?php echo $view_order['payment_status'] === 'Unpaid' ? 'selected' : ''; ?>>Unpaid (Awaiting)</option>
                                    <option value="Paid" <?php echo $view_order['payment_status'] === 'Paid' ? 'selected' : ''; ?>>Paid (Confirmed)</option>
                                </select>
                            </div>
                            
                            <button type="submit" class="btn btn-orchid btn-sm w-100 py-2">Apply Status Revisions</button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-secondary py-2 small mb-0" role="alert">
                            <i class="bi bi-info-circle-fill me-1"></i> POS Walk-in transactions are automatically completed.
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="card border-0 shadow-sm rounded-4 p-5 text-center text-muted">
                    <i class="bi bi-info-circle fs-2 d-block mb-2"></i>
                    <p class="mb-0">Select an order from the list to view items and adjust shipping/payment status logs.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
