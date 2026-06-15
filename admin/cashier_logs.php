<?php
// admin/cashier_logs.php - Cashier Activity Logs Monitor
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

// Force admin role check
requireRole('admin');

// Filter values
$cashier_filter = isset($_GET['cashier_id']) ? intval($_GET['cashier_id']) : 0;
$action_filter = $_GET['action_filter'] ?? '';
$search = trim($_GET['search'] ?? '');

// Pagination settings
$limit = 15;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Base query parts
$where_clauses = ["1=1"];
$params = [];

if ($cashier_filter > 0) {
    $where_clauses[] = "cl.cashier_id = ?";
    $params[] = $cashier_filter;
}

if (!empty($action_filter)) {
    $where_clauses[] = "cl.action = ?";
    $params[] = $action_filter;
}

if (!empty($search)) {
    $where_clauses[] = "(cl.details LIKE ? OR cl.action LIKE ? OR u.full_name LIKE ? OR u.username LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$where_sql = implode(" AND ", $where_clauses);

// 1. Get total logs count for pagination
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM cashier_logs cl JOIN users u ON cl.cashier_id = u.user_id WHERE $where_sql");
$count_stmt->execute($params);
$total_logs = $count_stmt->fetchColumn();
$total_pages = ceil($total_logs / $limit);

// 2. Fetch logs with pagination
$query = "SELECT cl.*, u.full_name, u.username, u.role 
          FROM cashier_logs cl 
          JOIN users u ON cl.cashier_id = u.user_id 
          WHERE $where_sql 
          ORDER BY cl.created_at DESC 
          LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$logs = $stmt->fetchAll();

// 3. Fetch all cashiers/admins for the dropdown filter
$users_stmt = $pdo->query("SELECT user_id, username, full_name, role FROM users WHERE role IN ('cashier', 'admin') ORDER BY role ASC, full_name ASC");
$users = $users_stmt->fetchAll();

// 4. Fetch distinct action types for filter dropdown
$actions_stmt = $pdo->query("SELECT DISTINCT action FROM cashier_logs ORDER BY action ASC");
$actions = $actions_stmt->fetchAll(PDO::FETCH_COLUMN);

// Action badge helper
function getActionBadgeClass($action) {
    switch ($action) {
        case 'Login':
            return 'bg-success bg-opacity-10 text-success';
        case 'Logout':
            return 'bg-secondary bg-opacity-10 text-secondary';
        case 'POS Checkout':
            return 'bg-primary bg-opacity-10 text-primary';
        case 'Register Customer':
            return 'bg-info bg-opacity-10 text-info';
        case 'Clear POS Cart':
            return 'bg-warning bg-opacity-10 text-warning-emphasis';
        default:
            return 'bg-dark bg-opacity-10 text-dark';
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="brand-font mb-0 text-dark"><i class="bi bi-journal-text text-primary me-2"></i>Cashier Activity Logs</h2>
            <p class="text-muted small mb-0">Monitor POS transactions, customer registrations, and session log history.</p>
        </div>
        <a href="index.php" class="btn btn-orchid-outline btn-sm"><i class="bi bi-speedometer2"></i> Admin Dashboard</a>
    </div>

    <!-- Quick Navigation Links (Admin Sub-menus) -->
    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="products.php" class="btn btn-sm btn-orchid-outline"><i class="bi bi-gift"></i> Products CRUD</a>
        <a href="categories.php" class="btn btn-sm btn-orchid-outline"><i class="bi bi-tags"></i> Categories CRUD</a>
        <a href="orders.php" class="btn btn-sm btn-orchid-outline"><i class="bi bi-receipt"></i> Orders List</a>
        <a href="reviews.php" class="btn btn-sm btn-orchid-outline"><i class="bi bi-star"></i> Reviews Moderation</a>
        <a href="users.php" class="btn btn-sm btn-orchid-outline"><i class="bi bi-people"></i> Manage Staff & Users</a>
        <a href="cashier_logs.php" class="btn btn-sm btn-orchid"><i class="bi bi-journal-text"></i> Cashier Logs</a>
    </div>

    <!-- Filters Panel -->
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 glass-panel">
        <form method="GET" action="cashier_logs.php" class="row g-3">
            <div class="col-md-3">
                <label for="cashier_id" class="form-label text-muted small fw-bold">Filter By Staff</label>
                <select name="cashier_id" id="cashier_id" class="form-select form-select-sm">
                    <option value="0">-- All Staff Members --</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?php echo $u['user_id']; ?>" <?php echo $cashier_filter === intval($u['user_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($u['full_name']); ?> (@<?php echo htmlspecialchars($u['username']); ?>) - <?php echo ucfirst($u['role']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-3">
                <label for="action_filter" class="form-label text-muted small fw-bold">Filter By Action</label>
                <select name="action_filter" id="action_filter" class="form-select form-select-sm">
                    <option value="">-- All Actions --</option>
                    <?php foreach ($actions as $act): ?>
                        <option value="<?php echo htmlspecialchars($act); ?>" <?php echo $action_filter === $act ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($act); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label for="search" class="form-label text-muted small fw-bold">Search Details</label>
                <div class="input-group input-group-sm">
                    <input type="text" name="search" id="search" class="form-control" placeholder="Search order ID, customer, etc..." value="<?php echo htmlspecialchars($search); ?>">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                </div>
            </div>

            <div class="col-md-2 d-flex align-items-end">
                <div class="d-flex gap-2 w-100">
                    <button type="submit" class="btn btn-orchid btn-sm w-100"><i class="bi bi-funnel"></i> Apply</button>
                    <a href="cashier_logs.php" class="btn btn-outline-secondary btn-sm" title="Clear Filters"><i class="bi bi-x-circle"></i></a>
                </div>
            </div>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="card border-0 shadow-sm rounded-4 p-4">
        <?php if (empty($logs)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-journal-x fs-1 mb-2 d-block text-primary text-opacity-25"></i>
                <p class="mb-0">No cashier logs match your search filters.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr class="text-muted small">
                            <th>Log ID</th>
                            <th>Staff Member</th>
                            <th>Action</th>
                            <th>Details</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td class="text-muted small">#<?php echo $log['log_id']; ?></td>
                                <td>
                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($log['full_name']); ?></div>
                                    <span class="text-muted small">@<?php echo htmlspecialchars($log['username']); ?> • <?php echo ucfirst($log['role']); ?></span>
                                </td>
                                <td>
                                    <span class="badge px-3 py-1.5 rounded-pill <?php echo getActionBadgeClass($log['action']); ?>">
                                        <?php echo htmlspecialchars($log['action']); ?>
                                    </span>
                                </td>
                                <td class="small text-secondary" style="max-width: 350px; word-break: break-word;">
                                    <?php echo htmlspecialchars($log['details']); ?>
                                </td>
                                <td class="small text-muted">
                                    <i class="bi bi-clock me-1"></i> <?php echo date('M d, Y H:i:s', strtotime($log['created_at'])); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Grid -->
            <?php if ($total_pages > 1): ?>
                <nav class="d-flex justify-content-between align-items-center mt-4">
                    <span class="text-muted small">Showing <?php echo count($logs); ?> of <?php echo $total_logs; ?> entries</span>
                    <ul class="pagination pagination-sm mb-0">
                        <!-- Previous Link -->
                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&cashier_id=<?php echo $cashier_filter; ?>&action_filter=<?php echo urlencode($action_filter); ?>&search=<?php echo urlencode($search); ?>"><i class="bi bi-chevron-left"></i></a>
                        </li>
                        
                        <!-- Page numbers -->
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&cashier_id=<?php echo $cashier_filter; ?>&action_filter=<?php echo urlencode($action_filter); ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <!-- Next Link -->
                        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&cashier_id=<?php echo $cashier_filter; ?>&action_filter=<?php echo urlencode($action_filter); ?>&search=<?php echo urlencode($search); ?>"><i class="bi bi-chevron-right"></i></a>
                        </li>
                    </ul>
                </nav>
            <?php else: ?>
                <div class="text-muted small mt-2">Showing all <?php echo $total_logs; ?> entries</div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
