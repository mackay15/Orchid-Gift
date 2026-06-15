<?php
// admin/index.php - Administrator Analytics Dashboard
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

// Force admin role check
requireRole('admin');

$admin_id = $_SESSION['user_id'];

// Clear Notification action
if (isset($_GET['clear_notif'])) {
    $notif_id = intval($_GET['clear_notif']);
    $stmt = $pdo->prepare("DELETE FROM notifications WHERE notification_id = ? AND user_id = ?");
    $stmt->execute([$notif_id, $admin_id]);
    header("Location: index.php");
    exit();
}

// 1. Calculate Gross Sales
$stmt = $pdo->prepare("SELECT SUM(total_amount) FROM orders WHERE payment_status = 'Paid'");
$stmt->execute();
$gross_sales = $stmt->fetchColumn() ?? 0.00;

// 2. Count Total Orders
$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders");
$stmt->execute();
$total_orders = $stmt->fetchColumn();

// 3. Count Low Stock Products
$stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE stock_quantity <= 3");
$stmt->execute();
$low_stock = $stmt->fetchColumn();

// 4. Count Pending Reviews
$stmt = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE status = 'Pending'");
$stmt->execute();
$pending_reviews = $stmt->fetchColumn();

// Fetch last 5 notifications
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$admin_id]);
$notifications = $stmt->fetchAll();

// Fetch low stock products
$low_stock_items = $pdo->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.category_id WHERE p.stock_quantity <= 3 ORDER BY p.stock_quantity ASC LIMIT 5")->fetchAll();

// Fetch Sales Grouped by Day for the last 7 days (Chart.js dynamic data representation)
$chart_labels = [];
$chart_values = [];

for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $chart_labels[] = date('D, M d', strtotime($date));
    
    $stmt = $pdo->prepare("SELECT SUM(total_amount) FROM orders WHERE DATE(created_at) = ? AND payment_status = 'Paid'");
    $stmt->execute([$date]);
    $val = $stmt->fetchColumn() ?? 0;
    $chart_values[] = floatval($val);
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="brand-font mb-0 text-dark"><i class="bi bi-speedometer2 text-primary me-2"></i>Admin Dashboard</h2>
        <a href="reports.php" class="btn btn-orchid btn-sm"><i class="bi bi-file-earmark-spreadsheet"></i> Export Report (CSV)</a>
    </div>

    <!-- Quick Navigation Links (Admin Sub-menus) -->
    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="products.php" class="btn btn-sm btn-orchid-outline"><i class="bi bi-gift"></i> Products CRUD</a>
        <a href="categories.php" class="btn btn-sm btn-orchid-outline"><i class="bi bi-tags"></i> Categories CRUD</a>
        <a href="orders.php" class="btn btn-sm btn-orchid-outline"><i class="bi bi-receipt"></i> Orders List</a>
        <a href="reviews.php" class="btn btn-sm btn-orchid-outline"><i class="bi bi-star"></i> Reviews Moderation (<?php echo $pending_reviews; ?>)</a>
        <a href="users.php" class="btn btn-sm btn-orchid-outline"><i class="bi bi-people"></i> Manage Staff & Users</a>
        <a href="cashier_logs.php" class="btn btn-sm btn-orchid-outline"><i class="bi bi-journal-text"></i> Cashier Logs</a>
    </div>

    <!-- Stats Row -->
    <div class="row g-4 mb-5">
        <div class="col-md-3 col-sm-6">
            <div class="card stat-card bg-orchid-grad shadow-sm">
                <h6 class="text-white-50 text-uppercase small fw-bold">Gross Revenue</h6>
                <div class="d-flex align-items-center justify-content-between mt-2">
                    <span class="fs-2 fw-bold"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($gross_sales, 2); ?></span>
                    <i class="bi bi-wallet2 fs-2 text-white-50"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="card stat-card bg-rose-grad shadow-sm">
                <h6 class="text-white-50 text-uppercase small fw-bold">Total Sales Count</h6>
                <div class="d-flex align-items-center justify-content-between mt-2">
                    <span class="fs-2 fw-bold"><?php echo $total_orders; ?></span>
                    <i class="bi bi-cart3 fs-2 text-white-50"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="card stat-card bg-ocean-grad shadow-sm">
                <h6 class="text-white-50 text-uppercase small fw-bold">Low Stock Warning</h6>
                <div class="d-flex align-items-center justify-content-between mt-2">
                    <span class="fs-2 fw-bold"><?php echo $low_stock; ?></span>
                    <i class="bi bi-exclamation-triangle fs-2 text-white-50"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="card stat-card bg-dark shadow-sm" style="background: linear-gradient(135deg, #2b2d42, #11131c);">
                <h6 class="text-white-50 text-uppercase small fw-bold">Pending Reviews</h6>
                <div class="d-flex align-items-center justify-content-between mt-2">
                    <span class="fs-2 fw-bold"><?php echo $pending_reviews; ?></span>
                    <i class="bi bi-chat-dots fs-2 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart & Notification List -->
    <div class="row g-4">
        <!-- Sales line Chart -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <h5 class="fw-bold mb-4 brand-font text-dark"><i class="bi bi-graph-up-arrow text-primary me-2"></i>Weekly Sales Volume (<?php echo CURRENCY_SYMBOL; ?>)</h5>
                <div style="height: 320px; position: relative;">
                    <canvas id="weeklySalesChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Alert Notification List -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 glass-panel h-100">
                <h5 class="fw-bold mb-4 brand-font text-dark"><i class="bi bi-bell-fill text-primary me-2"></i>System Notifications</h5>
                
                <?php if (empty($notifications)): ?>
                    <div class="text-center py-5 text-muted small">No notifications found.</div>
                <?php else: ?>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($notifications as $n): ?>
                            <div class="bg-white p-3 border rounded shadow-sm position-relative">
                                <p class="small text-secondary mb-1" style="padding-right: 15px;"><?php echo htmlspecialchars($n['message']); ?></p>
                                <span class="text-muted d-block" style="font-size: 0.7rem;"><?php echo date('M d, Y H:i', strtotime($n['created_at'])); ?></span>
                                
                                <a href="index.php?clear_notif=<?php echo $n['notification_id']; ?>" class="position-absolute top-0 end-0 m-2 text-danger" title="Clear alert">
                                    <i class="bi bi-x"></i>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Low Stock Alert Grid -->
    <div class="card border-0 shadow-sm rounded-4 p-4 mt-5">
        <h5 class="fw-bold mb-4 brand-font text-dark"><i class="bi bi-shield-alert text-danger me-2"></i>Out of Stock / Critical Stock Alerts</h5>
        
        <?php if (empty($low_stock_items)): ?>
            <div class="text-center py-4 text-muted small">All products are healthy and adequately stocked.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr class="text-muted small">
                            <th>Image</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Stock Count</th>
                            <th>Restock Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($low_stock_items as $item): ?>
                            <tr>
                                <td><img src="<?php echo htmlspecialchars(getProductImage($item['image_url'])); ?>" alt="" class="rounded" style="width: 45px; height: 45px; object-fit: cover;"></td>
                                <td><b><?php echo htmlspecialchars($item['name']); ?></b></td>
                                <td><span class="badge bg-secondary-subtle text-secondary"><?php echo htmlspecialchars($item['category_name']); ?></span></td>
                                <td>
                                    <span class="badge bg-danger bg-opacity-10 text-danger p-2 px-3 fw-bold">
                                        Only <?php echo $item['stock_quantity']; ?> left
                                    </span>
                                </td>
                                <td>
                                    <a href="products.php?edit_id=<?php echo $item['product_id']; ?>" class="btn btn-orchid btn-xs py-1 px-2.5 rounded-pill" style="font-size: 0.75rem;"><i class="bi bi-plus"></i> Restock</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('weeklySalesChart').getContext('2d');
    const labels = <?php echo json_encode($chart_labels); ?>;
    const values = <?php echo json_encode($chart_values); ?>;
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Gross Sales (<?php echo CURRENCY_SYMBOL; ?>)',
                data: values,
                backgroundColor: 'rgba(123, 44, 191, 0.1)',
                borderColor: '#7b2cbf',
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#ff85a1',
                pointBorderColor: '#fff',
                pointRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
</script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
