<?php
// customer/wishlist.php - Saved items wishlist
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

// Force login to view wishlist
if (!isLoggedIn()) {
    header("Location: ../login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

$user_id = $_SESSION['user_id'];
$action_msg = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $pId = intval($_POST['product_id']);
    
    // Add to cart from wishlist
    if ($_POST['action'] === 'add_to_cart') {
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
        $_SESSION['cart'][$pId] = ($_SESSION['cart'][$pId] ?? 0) + 1;
        $action_msg = "Item successfully added to cart!";
    }
    
    // Remove from wishlist
    if ($_POST['action'] === 'remove') {
        $stmt = $pdo->prepare("DELETE FROM wishlists WHERE customer_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $pId]);
        $action_msg = "Item removed from wishlist.";
    }
}

// Fetch wishlist items
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM wishlists w JOIN products p ON w.product_id = p.product_id JOIN categories c ON p.category_id = c.category_id WHERE w.customer_id = ?");
$stmt->execute([$user_id]);
$items = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <h2 class="brand-font mb-4 text-dark"><i class="bi bi-heart-fill text-danger me-2"></i>My Wishlist</h2>
    
    <?php if (!empty($action_msg)): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> <?php echo $action_msg; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (empty($items)): ?>
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center">
            <div class="py-4">
                <i class="bi bi-heart-break fs-1 text-danger text-opacity-25 d-block mb-3"></i>
                <h4 class="fw-bold">Your wishlist is empty</h4>
                <p class="text-muted mb-4">Save products you love to your wishlist to buy them later.</p>
                <a href="shop.php" class="btn btn-orchid px-4 py-2">Discover Gifts</a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($items as $item): ?>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="card orchid-card h-100 d-flex flex-column justify-content-between">
                        <div class="position-relative overflow-hidden" style="height: 200px;">
                            <img src="<?php echo htmlspecialchars(getProductImage($item['image_url'])); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="card-img-top w-100 h-100" style="object-fit: cover;">
                            <span class="position-absolute top-0 end-0 bg-primary text-white text-xs px-2 py-1 m-3 rounded-pill" style="font-size: 0.75rem;">
                                <?php echo htmlspecialchars($item['category_name']); ?>
                            </span>
                        </div>
                        <div class="card-body d-flex flex-column justify-content-between">
                            <div>
                                <h6 class="card-title text-dark fw-bold mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                                <p class="text-muted small mb-3"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($item['price'], 2); ?></p>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <!-- Add to Cart -->
                                <form action="wishlist.php" method="POST" class="w-100">
                                    <input type="hidden" name="action" value="add_to_cart">
                                    <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                    <button type="submit" class="btn btn-orchid btn-sm w-100" <?php echo $item['stock_quantity'] <= 0 ? 'disabled' : ''; ?>>
                                        <i class="bi bi-cart-plus"></i> Add
                                    </button>
                                </form>
                                
                                <!-- Remove -->
                                <form action="wishlist.php" method="POST">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                    <button type="submit" class="btn btn-orchid-outline btn-sm"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
