<?php
// admin/reviews.php - Admin Review Moderation
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('admin');

$error = '';
$success = '';

// Handle Moderation actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $review_id = intval($_POST['review_id']);
    $action = $_POST['action'];
    
    if (in_array($action, ['Approve', 'Reject'])) {
        $stmt = $pdo->prepare("UPDATE reviews SET status = ? WHERE review_id = ?");
        $stmt->execute([$action === 'Approve' ? 'Approved' : 'Rejected', $review_id]);
        $success = "Review feedback status set to: " . ($action === 'Approve' ? 'Approved' : 'Rejected');
    }
}

// Fetch reviews
$query = "SELECT r.*, u.full_name, p.name as product_name, p.image_url 
          FROM reviews r 
          JOIN users u ON r.customer_id = u.user_id 
          JOIN products p ON r.product_id = p.product_id 
          ORDER BY FIELD(r.status, 'Pending', 'Approved', 'Rejected'), r.created_at DESC";
$reviews = $pdo->query($query)->fetchAll();
?>

<div class="container py-5">
    <?php if (!empty($success)): ?>
        <div class="alert alert-success border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4 p-4">
        <h4 class="brand-font mb-4 text-dark"><i class="bi bi-chat-left-text text-primary me-2"></i>Review Moderation Queue</h4>
        
        <?php if (empty($reviews)): ?>
            <div class="text-center py-5 text-muted">No reviews submitted by customers yet.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr class="text-muted small">
                            <th>Product</th>
                            <th>Customer Feedback</th>
                            <th>Rating</th>
                            <th>Status Log</th>
                            <th>Moderation Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reviews as $rev): 
                            $badge = 'bg-secondary';
                            if ($rev['status'] === 'Pending') $badge = 'bg-warning-subtle text-warning-emphasis';
                            elseif ($rev['status'] === 'Approved') $badge = 'bg-success-subtle text-success';
                            elseif ($rev['status'] === 'Rejected') $badge = 'bg-danger-subtle text-danger';
                        ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="<?php echo htmlspecialchars($rev['image_url']); ?>" alt="" class="rounded" style="width: 40px; height: 40px; object-fit: cover;">
                                        <div class="small fw-bold text-dark text-truncate" style="max-width: 150px;"><?php echo htmlspecialchars($rev['product_name']); ?></div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold small text-dark"><?php echo htmlspecialchars($rev['full_name']); ?></div>
                                    <p class="text-secondary small mb-0 italic">"<?php echo htmlspecialchars($rev['review']); ?>"</p>
                                    <span class="text-muted" style="font-size: 0.7rem;"><?php echo date('M d, Y', strtotime($rev['created_at'])); ?></span>
                                </td>
                                <td>
                                    <div class="rating-stars small">
                                        <?php for($i=1; $i<=5; $i++): ?>
                                            <i class="bi bi-star-fill<?php echo ($i <= $rev['rating']) ? '' : '-half text-muted'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge <?php echo $badge; ?> px-2 py-1.5"><?php echo $rev['status']; ?></span>
                                </td>
                                <td>
                                    <?php if ($rev['status'] === 'Pending'): ?>
                                        <div class="d-flex gap-1">
                                            <form action="reviews.php" method="POST" class="d-inline">
                                                <input type="hidden" name="review_id" value="<?php echo $rev['review_id']; ?>">
                                                <input type="hidden" name="action" value="Approve">
                                                <button type="submit" class="btn btn-success btn-xs py-1 px-2.5 rounded-pill" style="font-size: 0.75rem;"><i class="bi bi-check-circle"></i> Approve</button>
                                            </form>
                                            
                                            <form action="reviews.php" method="POST" class="d-inline">
                                                <input type="hidden" name="review_id" value="<?php echo $rev['review_id']; ?>">
                                                <input type="hidden" name="action" value="Reject">
                                                <button type="submit" class="btn btn-outline-danger btn-xs py-1 px-2.5 rounded-pill" style="font-size: 0.75rem;"><i class="bi bi-x-circle"></i> Reject</button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small italic">Processed</span>
                                    <?php endif; ?>
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
