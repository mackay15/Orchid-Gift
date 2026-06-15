<?php
// customer/cart.php - Shopping Cart
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

// Handle post requests (updates and deletes)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $pId = intval($_POST['product_id']);
    
    if ($_POST['action'] === 'update_qty') {
        $new_qty = intval($_POST['quantity']);
        if ($new_qty > 0) {
            // Check stock
            $stmt = $pdo->prepare("SELECT stock_quantity FROM products WHERE product_id = ?");
            $stmt->execute([$pId]);
            $stock = $stmt->fetchColumn();
            
            $_SESSION['cart'][$pId] = min($new_qty, $stock);
        } else {
            unset($_SESSION['cart'][$pId]);
        }
    }
    
    if ($_POST['action'] === 'remove_item') {
        unset($_SESSION['cart'][$pId]);
    }
    
    // Refresh to update counts
    echo "<script>window.location.href = window.location.href;</script>";
    exit();
}

$cart_items = [];
$subtotal = 0.00;

if (!empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    // Make placeholder string like ?,?,?
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
            'stock' => $prod['stock_quantity'],
            'image_url' => $prod['image_url'],
            'quantity' => $qty,
            'total_price' => $total
        ];
    }
}

$tax = $subtotal * 0.05; // 5% Vat
$grand_total = $subtotal + $tax;
?>

<div class="container py-5">
    <h2 class="brand-font mb-4 text-dark"><i class="bi bi-cart3 text-primary me-2"></i>Your Shopping Cart</h2>
    
    <?php if (empty($cart_items)): ?>
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center">
            <div class="py-4">
                <i class="bi bi-cart-x fs-1 text-primary text-opacity-25 d-block mb-3"></i>
                <h4 class="fw-bold">Your cart is currently empty!</h4>
                <p class="text-muted mb-4">Explore our beautiful collection and find the perfect gift today.</p>
                <a href="shop.php" class="btn btn-orchid px-4 py-2">Go Shopping</a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <!-- Items list -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr class="text-muted small">
                                    <th scope="col" style="border: none;">Product</th>
                                    <th scope="col" style="border: none;">Price</th>
                                    <th scope="col" style="border: none; width: 150px;">Quantity</th>
                                    <th scope="col" style="border: none;" class="text-end">Total</th>
                                    <th scope="col" style="border: none;" class="text-center">Remove</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart_items as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <img src="<?php echo htmlspecialchars(getProductImage($item['image_url'])); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="rounded" style="width: 60px; height: 60px; object-fit: cover;">
                                                <div>
                                                    <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($item['name']); ?></h6>
                                                    <small class="text-muted">Stock: <?php echo $item['stock']; ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($item['price'], 2); ?></td>
                                        <td>
                                            <form action="cart.php" method="POST" class="d-flex align-items-center">
                                                <input type="hidden" name="action" value="update_qty">
                                                <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                                <div class="input-group input-group-sm">
                                                    <button class="btn btn-outline-secondary" type="submit" name="quantity" value="<?php echo $item['quantity'] - 1; ?>">-</button>
                                                    <input type="text" class="form-control text-center" value="<?php echo $item['quantity']; ?>" readonly style="max-width: 50px;">
                                                    <button class="btn btn-outline-secondary" type="submit" name="quantity" value="<?php echo $item['quantity'] + 1; ?>" <?php echo $item['quantity'] >= $item['stock'] ? 'disabled' : ''; ?>>+</button>
                                                </div>
                                            </form>
                                        </td>
                                        <td class="text-end fw-bold text-primary"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($item['total_price'], 2); ?></td>
                                        <td class="text-center">
                                            <form action="cart.php" method="POST">
                                                <input type="hidden" name="action" value="remove_item">
                                                <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                                <button type="submit" class="btn btn-link text-danger p-0"><i class="bi bi-trash fs-5"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Summary card -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 glass-panel">
                    <h5 class="fw-bold mb-4 brand-font">Order Summary</h5>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal</span>
                        <span class="fw-bold"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">VAT Tax (5%)</span>
                        <span class="fw-bold"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($tax, 2); ?></span>
                    </div>
                    
                    <hr class="my-3" style="border-color: rgba(90, 24, 154, 0.15);">
                    
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="fs-5 fw-bold text-dark">Grand Total</span>
                        <span class="fs-4 fw-bold text-primary"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($grand_total, 2); ?></span>
                    </div>
                    
                    <a href="checkout.php" class="btn btn-orchid w-100 py-3 rounded-3 shadow"><i class="bi bi-shield-lock me-2"></i> Proceed to Checkout</a>
                    <a href="shop.php" class="btn btn-orchid-outline w-100 mt-2 py-2.5 rounded-3">Continue Shopping</a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
