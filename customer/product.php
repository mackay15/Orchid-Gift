<?php
// customer/product.php - Product Detail & Reviews Page
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$pId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch product details
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.category_id WHERE p.product_id = ?");
$stmt->execute([$pId]);
$product = $stmt->fetch();

if (!$product) {
    echo "<div class='container py-5 text-center'><h3 class='text-danger'>Product Not Found</h3><a href='shop.php' class='btn btn-orchid mt-3'>Back to Shop</a></div>";
    require_once __DIR__ . '/../includes/footer.php';
    exit();
}

$user_id = $_SESSION['user_id'] ?? 0;
$in_wishlist = false;

// Check if item is in user's wishlist
if ($user_id > 0) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM wishlists WHERE customer_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $pId]);
    $in_wishlist = ($stmt->fetchColumn() > 0);
}

// Handle actions (Wishlist and Reviews submission)
$action_msg = '';
$action_err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        // 1. Handle add to cart
        if ($_POST['action'] === 'add_to_cart') {
            $qty = intval($_POST['quantity'] ?? 1);
            if ($qty > 0) {
                if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
                $_SESSION['cart'][$pId] = ($_SESSION['cart'][$pId] ?? 0) + $qty;
                $action_msg = "Added $qty item(s) to your shopping cart!";
            }
        }
        
        // 2. Handle wishlist toggle
        if ($_POST['action'] === 'toggle_wishlist') {
            if ($user_id <= 0) {
                header("Location: ../login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
                exit();
            }
            if ($in_wishlist) {
                $stmt = $pdo->prepare("DELETE FROM wishlists WHERE customer_id = ? AND product_id = ?");
                $stmt->execute([$user_id, $pId]);
                $in_wishlist = false;
                $action_msg = "Removed from wishlist.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO wishlists (customer_id, product_id) VALUES (?, ?)");
                $stmt->execute([$user_id, $pId]);
                $in_wishlist = true;
                $action_msg = "Added to wishlist.";
            }
        }
        
        // 3. Handle review submission
        if ($_POST['action'] === 'submit_review') {
            if ($user_id <= 0) {
                header("Location: ../login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
                exit();
            }
            $rating = intval($_POST['rating'] ?? 5);
            $review_text = trim($_POST['review'] ?? '');
            
            if (empty($review_text)) {
                $action_err = "Please write a review comment.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO reviews (product_id, customer_id, rating, review, status) VALUES (?, ?, ?, ?, 'Pending')");
                $stmt->execute([$pId, $user_id, $rating, $review_text]);
                
                // Add Admin notification for review approval
                $stmt = $pdo->prepare("SELECT user_id FROM users WHERE role='admin' LIMIT 1");
                $stmt->execute();
                $admin_id = $stmt->fetchColumn();
                if ($admin_id) {
                    $cName = $_SESSION['full_name'];
                    $pName = $product['name'];
                    $nMsg = "New review submitted by customer '$cName' on product '$pName' (Rating: $rating★). Approval required.";
                    $stmtN = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                    $stmtN->execute([$admin_id, $nMsg]);
                }
                
                $action_msg = "Your review has been submitted and is pending administrator approval!";
            }
        }
    }
}

// Fetch approved reviews for this product
$stmt = $pdo->prepare("SELECT r.*, u.full_name FROM reviews r JOIN users u ON r.customer_id = u.user_id WHERE r.product_id = ? AND r.status = 'Approved' ORDER BY r.created_at DESC");
$stmt->execute([$pId]);
$reviews = $stmt->fetchAll();

// Calculate average rating
$avg_rating = 0.0;
if (count($reviews) > 0) {
    $sum = 0;
    foreach ($reviews as $r) $sum += $r['rating'];
    $avg_rating = round($sum / count($reviews), 1);
}
?>

<div class="container py-5">
    <!-- Success or Error Feedback Alerts -->
    <?php if (!empty($action_msg)): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> <?php echo $action_msg; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($action_err)): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $action_err; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Product Detail Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
        <div class="row g-0">
            <div class="col-md-6" style="max-height: 480px;">
                <img src="<?php echo htmlspecialchars(getProductImage($product['image_url'])); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-100 h-100" style="object-fit: cover;">
            </div>
            <div class="col-md-6 p-4 p-lg-5 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge bg-primary text-uppercase"><?php echo htmlspecialchars($product['category_name']); ?></span>
                        
                        <!-- Wishlist Toggle -->
                        <form action="product.php?id=<?php echo $pId; ?>" method="POST">
                            <input type="hidden" name="action" value="toggle_wishlist">
                            <button type="submit" class="btn btn-link text-danger p-0 fs-4">
                                <i class="bi bi-heart<?php echo $in_wishlist ? '-fill' : ''; ?>"></i>
                            </button>
                        </form>
                    </div>
                    
                    <h2 class="display-6 brand-font text-dark mb-3"><?php echo htmlspecialchars($product['name']); ?></h2>
                    
                    <!-- Rating summary -->
                    <div class="d-flex align-items-center mb-4 gap-2">
                        <div class="rating-stars">
                            <?php for($i=1; $i<=5; $i++): ?>
                                <i class="bi bi-star-fill<?php echo ($i <= $avg_rating) ? '' : '-half text-muted'; ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <span class="text-muted small fw-bold">(<?php echo $avg_rating; ?> / 5.0 from <?php echo count($reviews); ?> reviews)</span>
                    </div>

                    <p class="text-secondary mb-4"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                </div>

                <div>
                    <hr class="my-4" style="border-color: rgba(90, 24, 154, 0.1);">
                    
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <small class="text-muted d-block">Price</small>
                            <span class="fs-2 fw-bold text-primary"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($product['price'], 2); ?></span>
                        </div>
                        
                        <div>
                            <small class="text-muted d-block text-end">Availability</small>
                            <?php if ($product['stock_quantity'] > 0): ?>
                                <span class="badge bg-success-subtle text-success p-2">In Stock (<?php echo $product['stock_quantity']; ?> units)</span>
                            <?php else: ?>
                                <span class="badge bg-danger-subtle text-danger p-2">Out of Stock</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Add to Cart Form -->
                    <form action="product.php?id=<?php echo $pId; ?>" method="POST" class="row g-3 align-items-center">
                        <input type="hidden" name="action" value="add_to_cart">
                        <div class="col-sm-4">
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted">Qty</span>
                                <input type="number" class="form-control" name="quantity" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>" <?php echo $product['stock_quantity'] <= 0 ? 'disabled' : ''; ?>>
                            </div>
                        </div>
                        <div class="col-sm-8">
                            <button type="submit" class="btn btn-orchid w-100 py-2.5" <?php echo $product['stock_quantity'] <= 0 ? 'disabled' : ''; ?>>
                                <i class="bi bi-cart-plus me-2"></i> Add to Shopping Cart
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Reviews Section -->
    <div class="row g-5">
        <div class="col-lg-7">
            <h4 class="brand-font fw-bold mb-4 text-dark"><i class="bi bi-chat-left-text text-primary me-2"></i>Customer Feedback</h4>
            
            <?php if (!empty($reviews)): ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($reviews as $rev): ?>
                        <div class="card border-0 shadow-sm rounded-3 p-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($rev['full_name']); ?></h6>
                                <div class="rating-stars small">
                                    <?php for($i=1; $i<=5; $i++): ?>
                                        <i class="bi bi-star-fill<?php echo ($i <= $rev['rating']) ? '' : '-half text-muted'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <p class="text-secondary small mb-2">"<?php echo htmlspecialchars($rev['review']); ?>"</p>
                            <span class="text-muted d-block text-end" style="font-size: 0.75rem;"><?php echo date('F d, Y', strtotime($rev['created_at'])); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="card border-0 shadow-sm rounded-3 p-5 text-center text-muted">
                    <p class="mb-0">No reviews approved for this product yet.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Submit Review Form -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 p-4 glass-panel">
                <h5 class="fw-bold mb-3 brand-font">Write a Review</h5>
                
                <?php if (isLoggedIn()): ?>
                    <form action="product.php?id=<?php echo $pId; ?>" method="POST">
                        <input type="hidden" name="action" value="submit_review">
                        
                        <!-- Rating Choice -->
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">Your Rating</label>
                            <div class="d-flex gap-2" id="stars-container" onmouseleave="resetStars()">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <div class="form-check form-check-inline m-0">
                                        <input class="form-check-input d-none" type="radio" name="rating" id="rate<?php echo $i; ?>" value="<?php echo $i; ?>" <?php echo $i === 5 ? 'checked' : ''; ?>>
                                        <label class="form-check-label fs-4" for="rate<?php echo $i; ?>" style="cursor: pointer;" onclick="setRating(<?php echo $i; ?>)" onmouseenter="highlightStars(<?php echo $i; ?>)">
                                            <i class="bi bi-star-fill star-select text-secondary opacity-25" id="star-icon-<?php echo $i; ?>"></i>
                                        </label>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <!-- Review Text -->
                        <div class="mb-3">
                            <label for="review" class="form-label text-muted small fw-bold">Review Comment</label>
                            <textarea class="form-control" id="review" name="review" rows="4" placeholder="Share your experience with this gift..." required></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-orchid btn-sm w-100">Submit Review for Approval</button>
                    </form>
                <?php else: ?>
                    <div class="text-center py-4">
                        <p class="small text-muted mb-3">You must be signed in to rate products and submit reviews.</p>
                        <a href="../login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="btn btn-orchid-outline btn-sm">Login to Review</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    let currentRating = 5;

    // Initialise star selector colors on load
    document.addEventListener("DOMContentLoaded", () => {
        setRating(5);
    });

    function highlightStars(rating) {
        for (let i = 1; i <= 5; i++) {
            const star = document.getElementById("star-icon-" + i);
            if (i <= rating) {
                star.style.setProperty('color', '#ffb703', 'important');
                star.style.setProperty('opacity', '1', 'important');
            } else {
                star.style.setProperty('color', '#6c757d', 'important');
                star.style.setProperty('opacity', '0.25', 'important');
            }
        }
    }

    function setRating(rating) {
        currentRating = rating;
        const input = document.getElementById("rate" + rating);
        if (input) {
            input.checked = true;
        }
        highlightStars(rating);
    }

    function resetStars() {
        highlightStars(currentRating);
    }
</script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
