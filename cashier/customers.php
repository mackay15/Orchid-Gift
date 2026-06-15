<?php
// cashier/customers.php - Cashier Customer Lookup & Quick Signup
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole(['cashier', 'admin']);

$error = '';
$success = '';

// Handle quick customer creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'quick_register') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    
    if (empty($fullName) || empty($email) || empty($username)) {
        $error = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // Create customer with default password 'customer123'
        $regResult = registerUser($username, $email, 'customer123', $fullName);
        if ($regResult === true) {
            logCashierAction('Register Customer', "Registered new customer: " . $fullName . " (@" . $username . ")");
            $success = "Customer account registered successfully! Password is 'customer123'.";
            header("Location: index.php");
            exit();
        } else {
            $error = $regResult;
        }
    }
}

// Fetch all customers for lookup
$search = $_GET['search'] ?? '';
$query = "SELECT user_id, username, email, full_name, status, created_at FROM users WHERE role = 'customer'";
$params = [];

if (!empty($search)) {
    $query .= " AND (full_name LIKE ? OR email LIKE ? OR username LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$customers = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger border-0 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
        <div class="alert alert-success border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Customer Lookup list -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
                    <h4 class="brand-font mb-0 text-dark"><i class="bi bi-people text-primary me-2"></i>Customer Database</h4>
                    
                    <form action="customers.php" method="GET" class="d-flex gap-2">
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search customer..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn btn-orchid btn-sm"><i class="bi bi-search"></i></button>
                    </form>
                </div>
                
                <?php if (empty($customers)): ?>
                    <div class="text-center py-5 text-muted">No customers found.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr class="text-muted small">
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Username</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($customers as $c): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($c['full_name']); ?></div>
                                            <span class="badge <?php echo ($c['status'] === 'Active') ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'; ?>" style="font-size: 0.7rem;"><?php echo $c['status']; ?></span>
                                        </td>
                                        <td class="small"><?php echo htmlspecialchars($c['email']); ?></td>
                                        <td class="small text-muted">@<?php echo htmlspecialchars($c['username']); ?></td>
                                        <td class="small text-muted"><?php echo date('M d, Y', strtotime($c['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Signup Form -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 p-4 glass-panel">
                <h5 class="fw-bold mb-4 brand-font text-dark"><i class="bi bi-person-plus-fill text-primary me-2"></i>Quick Register Customer</h5>
                
                <form action="customers.php" method="POST">
                    <input type="hidden" name="action" value="quick_register">
                    
                    <div class="mb-3">
                        <label for="full_name" class="form-label text-muted small fw-bold">Full Name</label>
                        <input type="text" class="form-control form-control-sm" id="full_name" name="full_name" required placeholder="e.g. Samuel Jackson">
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label text-muted small fw-bold">Email Address</label>
                        <input type="email" class="form-control form-control-sm" id="email" name="email" required placeholder="e.g. sam@example.com">
                    </div>
                    
                    <div class="mb-4">
                        <label for="username" class="form-label text-muted small fw-bold">Desired Username</label>
                        <input type="text" class="form-control form-control-sm" id="username" name="username" required placeholder="e.g. samjack">
                        <div class="form-text text-muted" style="font-size: 0.75rem;">Account will be created with default password: <b>customer123</b></div>
                    </div>
                    
                    <button type="submit" class="btn btn-orchid w-100 py-2">Create & Return to POS</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
