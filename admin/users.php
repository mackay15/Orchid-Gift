<?php
// admin/users.php - User and Staff Manager
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('admin');

$error = '';
$success = '';

// Handle Actions (Staff creation & status toggling)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // 1. Staff Creation
    if ($action === 'create_staff') {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $fullName = trim($_POST['full_name'] ?? '');
        $role = $_POST['role'] ?? 'cashier';
        $pass = $_POST['password'] ?? '';
        
        if (empty($username) || empty($email) || empty($fullName) || empty($pass)) {
            $error = "Please fill in all fields.";
        } elseif (!in_array($role, ['cashier', 'admin'])) {
            $error = "Invalid staff role selection.";
        } else {
            // Check availability
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetchColumn() > 0) {
                $error = "Username or Email is already registered.";
            } else {
                $hashedPass = password_hash($pass, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, role, status) VALUES (?, ?, ?, ?, ?, 'Active')");
                $stmt->execute([$username, $email, $hashedPass, $fullName, $role]);
                $success = "New staff account created successfully!";
            }
        }
    }
    
    // 2. Toggle Status
    elseif ($action === 'toggle_status') {
        $uId = intval($_POST['user_id']);
        $new_status = $_POST['status'] === 'Active' ? 'Inactive' : 'Active';
        
        // Prevent self deactivation
        if ($uId === $_SESSION['user_id']) {
            $error = "You cannot deactivate your own account.";
        } else {
            $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE user_id = ?");
            $stmt->execute([$new_status, $uId]);
            $success = "User status updated successfully.";
        }
    }
}

// Fetch all users
$search = $_GET['search'] ?? '';
$query = "SELECT user_id, username, email, full_name, role, status, created_at FROM users WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (full_name LIKE ? OR email LIKE ? OR username LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$query .= " ORDER BY role ASC, created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll();
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
        <!-- Users Database Panel -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
                    <h4 class="brand-font mb-0 text-dark"><i class="bi bi-people text-primary me-2"></i>Users Database</h4>
                    
                    <form action="users.php" method="GET" class="d-flex gap-2">
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search users..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn btn-orchid btn-sm"><i class="bi bi-search"></i></button>
                    </form>
                </div>
                
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr class="text-muted small">
                                <th>Name / Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Registered</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($u['full_name']); ?></div>
                                        <span class="text-muted small">@<?php echo htmlspecialchars($u['username']); ?></span>
                                    </td>
                                    <td class="small"><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td>
                                        <?php if ($u['role'] === 'admin'): ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger px-2.5 py-1.5 text-uppercase">Admin</span>
                                        <?php elseif ($u['role'] === 'cashier'): ?>
                                            <span class="badge bg-primary bg-opacity-10 text-primary px-2.5 py-1.5 text-uppercase">Cashier</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary px-2.5 py-1.5 text-uppercase">Customer</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo ($u['status'] === 'Active') ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo $u['status']; ?>
                                        </span>
                                    </td>
                                    <td class="small text-muted"><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                                    <td>
                                        <?php if ($u['user_id'] !== $_SESSION['user_id']): ?>
                                            <form action="users.php" method="POST">
                                                <input type="hidden" name="action" value="toggle_status">
                                                <input type="hidden" name="user_id" value="<?php echo $u['user_id']; ?>">
                                                <input type="hidden" name="status" value="<?php echo $u['status']; ?>">
                                                
                                                <button type="submit" class="btn btn-xs rounded-pill <?php echo ($u['status'] === 'Active') ? 'btn-outline-danger' : 'btn-outline-success'; ?>" style="font-size: 0.75rem;">
                                                    <?php echo ($u['status'] === 'Active') ? 'Deactivate' : 'Activate'; ?>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-muted small italic">Logged In</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Add Staff Panel -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 glass-panel">
                <h5 class="fw-bold mb-4 brand-font text-dark"><i class="bi bi-person-plus-fill text-primary me-2"></i>Create Staff Account</h5>
                
                <form action="users.php" method="POST">
                    <input type="hidden" name="action" value="create_staff">
                    
                    <div class="mb-3">
                        <label for="full_name" class="form-label text-muted small fw-bold">Full Name *</label>
                        <input type="text" class="form-control form-control-sm" id="full_name" name="full_name" required placeholder="e.g. David Tenant">
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label text-muted small fw-bold">Email Address *</label>
                        <input type="email" class="form-control form-control-sm" id="email" name="email" required placeholder="e.g. david@orchid.com">
                    </div>

                    <div class="mb-3">
                        <label for="username" class="form-label text-muted small fw-bold">Username *</label>
                        <input type="text" class="form-control form-control-sm" id="username" name="username" required placeholder="e.g. davidt">
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label text-muted small fw-bold">Assign Role *</label>
                        <select name="role" id="role" class="form-select form-select-sm" required>
                            <option value="cashier">Cashier (POS Dashboard access)</option>
                            <option value="admin">Admin (Full Control access)</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label text-muted small fw-bold">Password *</label>
                        <input type="password" class="form-control form-control-sm" id="password" name="password" required placeholder="••••••••">
                    </div>
                    
                    <button type="submit" class="btn btn-orchid w-100 py-2">Create Staff Member</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
