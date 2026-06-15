<?php
// customer/checkout.php - Checkout Form & Order Processor
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

// Force login to checkout
if (!isLoggedIn()) {
    header("Location: ../login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

$cart_items = [];
$subtotal = 0.00;

if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

// Fetch products in cart
$ids = array_keys($_SESSION['cart']);
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt = $pdo->prepare("SELECT * FROM products WHERE product_id IN ($placeholders)");
$stmt->execute($ids);
$products = $stmt->fetchAll();

foreach ($products as $prod) {
    $pId = $prod['product_id'];
    $qty = $_SESSION['cart'][$pId];
    $total = $prod['price'] * $qty;
    $subtotal += $total;
    
    $cart_items[] = [
        'product_id' => $pId,
        'name' => $prod['name'],
        'price' => $prod['price'],
        'quantity' => $qty,
        'total_price' => $total,
        'stock' => $prod['stock_quantity']
    ];
}

$tax = $subtotal * 0.05;
$grand_total = $subtotal + $tax;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $payment_method = $_POST['payment_method'] ?? 'Cash';
    
    if (empty($phone) || empty($address)) {
        $error = "Please provide a shipping address and contact phone number.";
    } else {
        try {
            $pdo->beginTransaction();
            
            // 1. Double check stock availability
            foreach ($cart_items as $item) {
                $stmt = $pdo->prepare("SELECT stock_quantity FROM products WHERE product_id = ? FOR UPDATE");
                $stmt->execute([$item['product_id']]);
                $current_stock = $stmt->fetchColumn();
                
                if ($current_stock < $item['quantity']) {
                    throw new Exception("Error: '" . $item['name'] . "' is out of stock or does not have enough quantity available.");
                }
            }

            // 2. Create Order
            $pay_status = ($payment_method === 'Cash') ? 'Unpaid' : 'Paid';
            $stmt = $pdo->prepare("INSERT INTO orders (customer_id, cashier_id, order_type, total_amount, order_status, payment_status) VALUES (?, NULL, 'online', ?, 'Pending', ?)");
            $stmt->execute([$_SESSION['user_id'], $grand_total, $pay_status]);
            $orderId = $pdo->lastInsertId();
            
            // 3. Create Order Items & Decrement Stock
            $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?)");
            $stmtStock = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?");
            
            foreach ($cart_items as $item) {
                $stmtItem->execute([$orderId, $item['product_id'], $item['quantity'], $item['price'], $item['total_price']]);
                $stmtStock->execute([$item['quantity'], $item['product_id']]);
                
                // Add notifications for low stock alert
                $new_stock = $item['stock'] - $item['quantity'];
                if ($new_stock <= 3) {
                    $stmtAdmin = $pdo->prepare("SELECT user_id FROM users WHERE role='admin' LIMIT 1");
                    $stmtAdmin->execute();
                    $admin_id = $stmtAdmin->fetchColumn();
                    if ($admin_id) {
                        $lowStockMsg = "Low Stock Alert: Product '" . $item['name'] . "' has only $new_stock remaining units.";
                        $stmtN = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                        $stmtN->execute([$admin_id, $lowStockMsg]);
                    }
                }
            }
            
            // 4. Create Payment
            $pay_stage = ($payment_method === 'Cash') ? 'Pending' : 'Completed';
            $stmtPay = $pdo->prepare("INSERT INTO payments (order_id, amount, payment_method, payment_status) VALUES (?, ?, ?, ?)");
            $stmtPay->execute([$orderId, $grand_total, $payment_method, $pay_stage]);
            
            // 5. Create Sale Entry & Transaction id
            $txnId = ($payment_method === 'Cash') ? NULL : 'TXN' . rand(100000, 999999);
            $stmtSale = $pdo->prepare("INSERT INTO sales (order_id, total_amount, payment_method, transaction_id) VALUES (?, ?, ?, ?)");
            $stmtSale->execute([$orderId, $grand_total, $payment_method, $txnId]);
            
            // 6. Notify Admin about the order
            $stmtAdmin = $pdo->prepare("SELECT user_id FROM users WHERE role='admin' LIMIT 1");
            $stmtAdmin->execute();
            $admin_id = $stmtAdmin->fetchColumn();
            if ($admin_id) {
                $orderMsg = "New online order #" . $orderId . " placed by " . $_SESSION['full_name'] . " (" . CURRENCY_SYMBOL . number_format($grand_total,2) . ").";
                $stmtN = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                $stmtN->execute([$admin_id, $orderMsg]);
            }
            
            $pdo->commit();
            
            // Clear cart
            $_SESSION['cart'] = [];
            
            // Redirect to order success page
            header("Location: orders.php?success_id=" . $orderId);
            exit();
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <h2 class="brand-font mb-4 text-dark"><i class="bi bi-credit-card text-primary me-2"></i>Checkout</h2>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Billing / Shipping Form -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <h5 class="fw-bold mb-4 brand-font text-dark">Shipping & Contact Details</h5>
                
                <form action="checkout.php" method="POST">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label text-muted small fw-bold">Customer Name</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($_SESSION['full_name']); ?>" disabled>
                        </div>
                        
                        <!-- 10 digit Phone validation prompt context -->
                        <div class="col-md-12">
                            <label for="phone" class="form-label text-muted small fw-bold">Phone Number (10 digits)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-telephone"></i></span>
                                <input type="tel" class="form-control" id="phone" name="phone" required placeholder="e.g. 0244123456" pattern="[0-9]{10}">
                            </div>
                            <small class="text-muted" style="font-size: 0.8rem;">Enter exactly 10 numeric digits.</small>
                        </div>
                        
                        <div class="col-12">
                            <label for="address" class="form-label text-muted small fw-bold">Delivery Shipping Address</label>
                            <textarea class="form-control" id="address" name="address" rows="3" required placeholder="e.g. House No. 23, Orchid Garden Ave, Accra"></textarea>
                        </div>
                        
                        <div class="col-12 mt-4">
                            <h5 class="fw-bold mb-3 brand-font text-dark">Select Payment Method</h5>
                            
                            <div class="d-flex flex-column gap-2">
                                <div class="form-check p-3 border rounded-3 d-flex align-items-center justify-content-between">
                                    <div>
                                        <input class="form-check-input ms-0 me-3" type="radio" name="payment_method" id="pay_momo" value="Mobile Money" checked>
                                        <label class="form-check-label fw-bold text-dark" for="pay_momo">Mobile Money (Momo)</label>
                                        <small class="text-muted d-block ms-1">Process instantly via MTN / Vodafone / AirtelTigo.</small>
                                    </div>
                                    <i class="bi bi-phone-vibrate fs-3 text-primary"></i>
                                </div>
                                
                                <div class="form-check p-3 border rounded-3 d-flex align-items-center justify-content-between">
                                    <div>
                                        <input class="form-check-input ms-0 me-3" type="radio" name="payment_method" id="pay_card" value="Card">
                                        <label class="form-check-label fw-bold text-dark" for="pay_card">Credit / Debit Card</label>
                                        <small class="text-muted d-block ms-1">Visa, Mastercard, or local bank card.</small>
                                    </div>
                                    <i class="bi bi-credit-card-2-back fs-3 text-primary"></i>
                                </div>

                                <div class="form-check p-3 border rounded-3 d-flex align-items-center justify-content-between">
                                    <div>
                                        <input class="form-check-input ms-0 me-3" type="radio" name="payment_method" id="pay_cash" value="Cash">
                                        <label class="form-check-label fw-bold text-dark" for="pay_cash">Cash on Delivery (COD)</label>
                                        <small class="text-muted d-block ms-1">Pay with physical cash upon package delivery.</small>
                                    </div>
                                    <i class="bi bi-cash-stack fs-3 text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-orchid w-100 py-3 rounded-3 mt-4 fs-6 shadow"><i class="bi bi-bag-check-fill me-2"></i> Place Order Now</button>
                </form>
            </div>
        </div>
        
        <!-- Summary Sidebar -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 p-4 glass-panel">
                <h5 class="fw-bold mb-4 brand-font text-dark">Order Items</h5>
                
                <div class="d-flex flex-column gap-3 mb-4">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0 fw-bold small text-dark"><?php echo htmlspecialchars($item['name']); ?></h6>
                                <small class="text-muted">Qty: <?php echo $item['quantity']; ?> × <?php echo CURRENCY_SYMBOL; ?><?php echo number_format($item['price'], 2); ?></small>
                            </div>
                            <span class="fw-bold small text-primary"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($item['total_price'], 2); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <hr style="border-color: rgba(90, 24, 154, 0.15);">
                
                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-muted">Subtotal</span>
                    <span class="fw-semibold"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($subtotal, 2); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-3 small">
                    <span class="text-muted">VAT Tax (5%)</span>
                    <span class="fw-semibold"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($tax, 2); ?></span>
                </div>
                
                <hr style="border-color: rgba(90, 24, 154, 0.15);">
                
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark fs-5">Grand Total</span>
                    <span class="fw-bold text-primary fs-4"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($grand_total, 2); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
