<?php
// cashier/sales.php - Cashier Sales History
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole(['cashier', 'admin']);

$cashier_id = $_SESSION['user_id'];

// Fetch all sales processed by this cashier
$stmt = $pdo->prepare("SELECT o.*, u.full_name as customer_name, s.payment_method 
                       FROM orders o 
                       LEFT JOIN users u ON o.customer_id = u.user_id 
                       LEFT JOIN sales s ON o.order_id = s.order_id 
                       WHERE o.cashier_id = ? 
                       ORDER BY o.created_at DESC");
$stmt->execute([$cashier_id]);
$sales = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="brand-font mb-0 text-dark"><i class="bi bi-receipt text-primary me-2"></i>POS Sales History</h2>
        <a href="index.php" class="btn btn-orchid btn-sm"><i class="bi bi-cpu"></i> POS Terminal</a>
    </div>

    <div class="card border-0 shadow-sm rounded-4 p-4">
        <?php if (empty($sales)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-info-circle fs-1 mb-2 d-block text-primary text-opacity-25"></i>
                <p class="mb-0">No walk-in transactions processed by you yet.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr class="text-muted small">
                            <th>Order #</th>
                            <th>Date / Time</th>
                            <th>Customer</th>
                            <th>Payment Option</th>
                            <th>Total Amount</th>
                            <th>Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sales as $s): ?>
                            <tr>
                                <td><b>#<?php echo $s['order_id']; ?></b></td>
                                <td class="small text-muted"><?php echo date('M d, Y H:i', strtotime($s['created_at'])); ?></td>
                                <td><?php echo htmlspecialchars($s['customer_name'] ?? 'Guest Customer'); ?></td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary"><?php echo htmlspecialchars($s['payment_method'] ?? 'Cash'); ?></span>
                                </td>
                                <td class="fw-bold text-primary">$<?php echo number_format($s['total_amount'], 2); ?></td>
                                <td>
                                    <a href="receipt.php?order_id=<?php echo $s['order_id']; ?>" target="_blank" class="btn btn-orchid-outline btn-xs py-1 px-2.5 rounded-pill" style="font-size: 0.75rem;"><i class="bi bi-printer"></i> Reprint</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
