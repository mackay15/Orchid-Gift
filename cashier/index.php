<?php
// cashier/index.php - Cashier Point of Sale (POS) Terminal
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

// Allow cashiers and admins to access POS
requireRole(['cashier', 'admin']);

$cashier_id = $_SESSION['user_id'];
$search = $_GET['search'] ?? '';
$category = isset($_GET['category']) ? intval($_GET['category']) : 0;

// Fetch categories for POS filters
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();

// Fetch products based on filters
$query = "SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.category_id WHERE p.stock_quantity > 0";
$params = [];

if ($category > 0) {
    $query .= " AND p.category_id = ?";
    $params[] = $category;
}

if (!empty($search)) {
    $query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$query .= " ORDER BY p.name ASC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Fetch customer accounts for selection
$customers = $pdo->query("SELECT user_id, full_name, email FROM users WHERE role = 'customer' AND status = 'Active' ORDER BY full_name ASC")->fetchAll();

// Handle POS Cart Actions
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $pId = intval($_POST['product_id'] ?? 0);
    
    if (!isset($_SESSION['pos_cart'])) {
        $_SESSION['pos_cart'] = [];
    }

    if ($action === 'add') {
        // Fetch product stock
        $stmt = $pdo->prepare("SELECT stock_quantity FROM products WHERE product_id = ?");
        $stmt->execute([$pId]);
        $stock = $stmt->fetchColumn();
        
        $current_qty = $_SESSION['pos_cart'][$pId] ?? 0;
        if ($current_qty < $stock) {
            $_SESSION['pos_cart'][$pId] = $current_qty + 1;
        } else {
            $error = "Cannot add more. Insufficient stock available.";
        }
    }
    
    elseif ($action === 'update_qty') {
        $qty = intval($_POST['quantity']);
        $stmt = $pdo->prepare("SELECT stock_quantity FROM products WHERE product_id = ?");
        $stmt->execute([$pId]);
        $stock = $stmt->fetchColumn();
        
        if ($qty > 0) {
            $_SESSION['pos_cart'][$pId] = min($qty, $stock);
        } else {
            unset($_SESSION['pos_cart'][$pId]);
        }
    }
    
    elseif ($action === 'remove') {
        unset($_SESSION['pos_cart'][$pId]);
    }
    
    elseif ($action === 'clear') {
        $_SESSION['pos_cart'] = [];
        logCashierAction('Clear POS Cart', 'Cleared POS transaction cart.');
    }
    
    elseif ($action === 'checkout') {
        $customer_id = intval($_POST['customer_id'] ?? 0);
        $payment_method = $_POST['payment_method'] ?? 'Cash';
        
        if (empty($_SESSION['pos_cart'])) {
            $error = "POS cart is empty.";
        } else {
            try {
                $pdo->beginTransaction();
                
                // Calculate Subtotal
                $subtotal = 0.00;
                $cart_items = [];
                
                foreach ($_SESSION['pos_cart'] as $productId => $qty) {
                    $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ? FOR UPDATE");
                    $stmt->execute([$productId]);
                    $prod = $stmt->fetch();
                    
                    if (!$prod || $prod['stock_quantity'] < $qty) {
                        throw new Exception("Error: Insufficient stock for product '" . $prod['name'] . "'");
                    }
                    
                    $total_price = $prod['price'] * $qty;
                    $subtotal += $total_price;
                    
                    $cart_items[] = [
                        'product_id' => $productId,
                        'quantity' => $qty,
                        'unit_price' => $prod['price'],
                        'total_price' => $total_price,
                        'name' => $prod['name']
                    ];
                }
                
                $tax = $subtotal * 0.05;
                $grand_total = $subtotal + $tax;
                
                // 1. Create Order
                // For POS orders, cashier_id is logged, customer_id can be 0 (Guest)
                $db_cust_id = ($customer_id > 0) ? $customer_id : NULL;
                $stmtOrder = $pdo->prepare("INSERT INTO orders (customer_id, cashier_id, order_type, total_amount, order_status, payment_status) VALUES (?, ?, 'walk-in', ?, 'Completed', 'Paid')");
                $stmtOrder->execute([$db_cust_id, $cashier_id, $grand_total]);
                $orderId = $pdo->lastInsertId();
                
                // 2. Create Order Items & Update Stocks
                $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?)");
                $stmtStock = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?");
                
                foreach ($cart_items as $item) {
                    $stmtItem->execute([$orderId, $item['product_id'], $item['quantity'], $item['unit_price'], $item['total_price']]);
                    $stmtStock->execute([$item['quantity'], $item['product_id']]);
                }
                
                // 3. Create Payment record
                $stmtPay = $pdo->prepare("INSERT INTO payments (order_id, amount, payment_method, payment_status) VALUES (?, ?, ?, 'Completed')");
                $stmtPay->execute([$orderId, $grand_total, $payment_method]);
                
                // 4. Create Sales record
                $txnId = 'POS' . rand(100000, 999999);
                $stmtSale = $pdo->prepare("INSERT INTO sales (order_id, total_amount, payment_method, transaction_id) VALUES (?, ?, ?, ?)");
                $stmtSale->execute([$orderId, $grand_total, $payment_method, $txnId]);
                
                $pdo->commit();
                $_SESSION['pos_cart'] = [];
                
                logCashierAction('POS Checkout', "Completed POS checkout for walk-in order #$orderId. Total amount: " . CURRENCY_SYMBOL . number_format($grand_total, 2));
                
                // Redirect to receipt printer view
                header("Location: receipt.php?order_id=" . $orderId);
                exit();
                
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = $e->getMessage();
            }
        }
    }
    
    // If it's an AJAX request, return JSON state
    $is_ajax = isset($_POST['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest');
    if ($is_ajax) {
        header('Content-Type: application/json');
        if (!empty($error)) {
            echo json_encode(['success' => false, 'error' => $error]);
        } else {
            echo json_encode(['success' => true]);
        }
        exit();
    }
    
    // Quick reload
    echo "<script>window.location.href = window.location.href;</script>";
    exit();
}

// Fetch POS cart products representation
$pos_items = [];
$pos_subtotal = 0.00;
if (!empty($_SESSION['pos_cart'])) {
    $ids = array_keys($_SESSION['pos_cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id IN ($placeholders)");
    $stmt->execute($ids);
    $products_in_cart = $stmt->fetchAll();
    
    foreach ($products_in_cart as $prod) {
        $pId = $prod['product_id'];
        $qty = $_SESSION['pos_cart'][$pId];
        $total = $prod['price'] * $qty;
        $pos_subtotal += $total;
        
        $pos_items[] = [
            'product_id' => $pId,
            'name' => $prod['name'],
            'price' => $prod['price'],
            'quantity' => $qty,
            'total_price' => $total
        ];
    }
}
$pos_tax = $pos_subtotal * 0.05;
$pos_grand_total = $pos_subtotal + $pos_tax;

require_once __DIR__ . '/../includes/header.php';
?>

<div class="pos-container py-4">
    <div class="container-fluid px-4">
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger border-0 shadow-sm mb-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="row g-4" id="pos-terminal-row">
            <!-- Left Side: POS Product Catalog & Filters -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
                        <h4 class="brand-font mb-0 text-dark"><i class="bi bi-cpu text-primary me-2"></i>POS Terminal</h4>
                        
                        <!-- Search Form -->
                        <form action="index.php" method="GET" class="d-flex gap-2">
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search product..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn btn-orchid btn-sm"><i class="bi bi-search"></i></button>
                        </form>
                    </div>

                    <!-- Category Pills Filter -->
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <a href="index.php?search=<?php echo urlencode($search); ?>" class="btn btn-sm rounded-pill <?php echo $category === 0 ? 'btn-primary' : 'btn-outline-secondary'; ?>">All</a>
                        <?php foreach ($categories as $cat): ?>
                            <a href="index.php?category=<?php echo $cat['category_id']; ?>&search=<?php echo urlencode($search); ?>" class="btn btn-sm rounded-pill <?php echo $category === $cat['category_id'] ? 'btn-primary' : 'btn-outline-secondary'; ?>">
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <!-- Products Grid -->
                    <div class="row g-3" style="max-height: 520px; overflow-y: auto;">
                        <?php if (!empty($products)): ?>
                            <?php foreach ($products as $prod): ?>
                                <div class="col-md-4 col-sm-6">
                                    <div class="card pos-product-card bg-light h-100 p-2 d-flex flex-column justify-content-between">
                                        <div class="position-relative overflow-hidden mb-2 rounded" style="height: 110px;">
                                            <img src="<?php echo htmlspecialchars(getProductImage($prod['image_url'])); ?>" alt="<?php echo htmlspecialchars($prod['name']); ?>" class="w-100 h-100" style="object-fit: cover;">
                                        </div>
                                        <div>
                                            <h6 class="mb-1 small fw-bold text-dark text-truncate" title="<?php echo htmlspecialchars($prod['name']); ?>"><?php echo htmlspecialchars($prod['name']); ?></h6>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="small fw-bold text-primary"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($prod['price'], 2); ?></span>
                                                <span class="small text-muted" style="font-size: 0.75rem;">Stock: <?php echo $prod['stock_quantity']; ?></span>
                                            </div>
                                        </div>
                                        
                                        <form action="index.php" method="POST">
                                            <input type="hidden" name="action" value="add">
                                            <input type="hidden" name="product_id" value="<?php echo $prod['product_id']; ?>">
                                            <button type="submit" class="btn btn-orchid btn-sm w-100 py-1" style="font-size: 0.8rem;"><i class="bi bi-plus-circle"></i> Add to POS</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12 text-center py-5 text-muted">No products available in stock.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right Side: POS Cart Sidebar -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 p-4 pos-sidebar bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0 brand-font text-dark"><i class="bi bi-cart3 text-primary me-2"></i>Checkout Cart</h5>
                        <?php if (!empty($pos_items)): ?>
                            <form action="index.php" method="POST" onsubmit="return confirm('Clear active checkout?');">
                                <input type="hidden" name="action" value="clear">
                                <button type="submit" class="btn btn-link text-danger text-decoration-none btn-sm p-0"><i class="bi bi-trash"></i> Clear</button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <!-- Cart Item list -->
                    <div class="pos-cart-list border rounded p-2 mb-3 bg-light" style="max-height: 240px; overflow-y: auto;">
                        <?php if (empty($pos_items)): ?>
                            <div class="text-center py-5 text-muted small">Cart is empty. Click items to add.</div>
                        <?php else: ?>
                            <div class="d-flex flex-column gap-2">
                                <?php foreach ($pos_items as $item): ?>
                                    <div class="d-flex justify-content-between align-items-center bg-white p-2 rounded shadow-sm">
                                        <div style="max-width: 60%;">
                                            <h6 class="mb-0 fw-bold small text-truncate text-dark"><?php echo htmlspecialchars($item['name']); ?></h6>
                                            <span class="text-muted small"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($item['price'], 2); ?> each</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <!-- Qty Modifier -->
                                            <form action="index.php" method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="update_qty">
                                                <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                                <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="0" class="form-control form-control-sm text-center px-1" style="max-width: 50px;" onchange="if (typeof this.form.requestSubmit === 'function') { this.form.requestSubmit(); } else { this.form.submit(); }">
                                            </form>
                                            
                                            <span class="fw-bold small text-primary"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($item['total_price'], 2); ?></span>
                                            
                                            <!-- Delete -->
                                            <form action="index.php" method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="remove">
                                                <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                                <button type="submit" class="btn btn-link text-danger p-0 ms-1"><i class="bi bi-x-circle-fill"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Checkout Form & Customer selection -->
                    <form action="index.php" method="POST" class="mt-auto">
                        <input type="hidden" name="action" value="checkout">
                        
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="customer_id" class="form-label text-muted small fw-bold mb-0">Select Customer Account</label>
                                <a href="customers.php" class="small text-decoration-none"><i class="bi bi-person-plus"></i> New</a>
                            </div>
                            <select name="customer_id" id="customer_id" class="form-select form-select-sm">
                                <option value="0">Guest Customer (Walk-in)</option>
                                <?php foreach ($customers as $c): ?>
                                    <option value="<?php echo $c['user_id']; ?>"><?php echo htmlspecialchars($c['full_name']); ?> (<?php echo htmlspecialchars($c['email']); ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="payment_method" class="form-label text-muted small fw-bold">Payment Option</label>
                            <select name="payment_method" id="payment_method" class="form-select form-select-sm">
                                <option value="Cash">Cash Transaction</option>
                                <option value="Card">Credit/Debit Card</option>
                                <option value="Mobile Money">Mobile Money (MoMo)</option>
                            </select>
                        </div>

                        <!-- Calculations summary -->
                        <div class="border-top pt-3 mb-3 small">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Subtotal</span>
                                <span><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($pos_subtotal, 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">VAT Tax (5%)</span>
                                <span><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($pos_tax, 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center border-top pt-2">
                                <span class="fw-bold text-dark">Grand Total</span>
                                <span class="fw-bold text-primary fs-5"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($pos_grand_total, 2); ?></span>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-orchid w-100 py-2.5 shadow" <?php echo empty($pos_items) ? 'disabled' : ''; ?>>
                            <i class="bi bi-printer me-2"></i> Print & Finalize Order
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Intercept standard POS operations via event delegation
    document.addEventListener('submit', function(e) {
        const form = e.target;
        const actionInput = form.querySelector('input[name="action"]');
        
        if (actionInput && ['add', 'update_qty', 'remove', 'clear'].includes(actionInput.value)) {
            e.preventDefault();
            
            const formData = new FormData(form);
            formData.append('ajax', '1');
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    refreshTerminal();
                } else {
                    alert(data.error || 'An error occurred.');
                }
            })
            .catch(err => {
                console.error('POS Cart Error:', err);
                alert('Connection error. Could not update POS cart.');
            });
        }
    });

    function refreshTerminal() {
        // Fetch current page content to extract the updated terminal row
        fetch(window.location.href)
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const freshRow = doc.getElementById('pos-terminal-row');
            const targetRow = document.getElementById('pos-terminal-row');
            
            if (freshRow && targetRow) {
                targetRow.innerHTML = freshRow.innerHTML;
            }
        })
        .catch(err => console.error('Failed to refresh POS terminal:', err));
    }
});
</script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
