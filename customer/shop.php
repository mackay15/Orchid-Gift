<?php
// customer/shop.php - Product Catalog Shop Page
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

// Handle Add to Cart action directly from shop page
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
    $pId = intval($_POST['product_id']);
    $qty = intval($_POST['quantity'] ?? 1);
    
    if ($pId > 0) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        $_SESSION['cart'][$pId] = ($_SESSION['cart'][$pId] ?? 0) + $qty;
        
        $success_msg = "Item successfully added to cart!";
        // Refresh header cart count
        echo "<script>window.location.href = window.location.href;</script>";
        exit();
    }
}

// Filters
$category_filter = isset($_GET['category']) ? intval($_GET['category']) : 0;
$search_filter = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort_filter = $_GET['sort'] ?? 'newest';

// Base Query
$query = "SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.category_id WHERE 1=1";
$params = [];

if ($category_filter > 0) {
    $query .= " AND p.category_id = ?";
    $params[] = $category_filter;
}

if (!empty($search_filter)) {
    $query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search_filter%";
    $params[] = "%$search_filter%";
}

// Sorting
if ($sort_filter === 'price_asc') {
    $query .= " ORDER BY p.price ASC";
} elseif ($sort_filter === 'price_desc') {
    $query .= " ORDER BY p.price DESC";
} elseif ($sort_filter === 'name_asc') {
    $query .= " ORDER BY p.name ASC";
} else {
    $query .= " ORDER BY p.created_at DESC";
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Fetch all categories for sidebar filter
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
?>

<div class="container py-5">
    <div class="row">
        <!-- Sidebar Filters -->
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 glass-panel">
                <h5 class="fw-bold mb-4 brand-font"><i class="bi bi-funnel text-primary me-2"></i>Filter Gifts</h5>
                
                <!-- Search Form (Mobile/Sidebar) -->
                <form action="shop.php" method="GET" class="mb-4">
                    <label class="form-label text-muted small fw-bold">Search Keywords</label>
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Search..." value="<?php echo htmlspecialchars($search_filter); ?>">
                        <?php if ($category_filter > 0): ?>
                            <input type="hidden" name="category" value="<?php echo $category_filter; ?>">
                        <?php endif; ?>
                        <button class="btn btn-orchid" type="submit"><i class="bi bi-search"></i></button>
                    </div>
                </form>

                <!-- Categories -->
                <div class="mb-4">
                    <label class="form-label text-muted small fw-bold mb-2">Categories</label>
                    <div class="list-group list-group-flush">
                        <a href="shop.php?search=<?php echo urlencode($search_filter); ?>&sort=<?php echo $sort_filter; ?>" class="list-group-item list-group-item-action border-0 px-0 d-flex justify-content-between align-items-center <?php echo $category_filter === 0 ? 'text-primary fw-bold' : ''; ?>">
                            <span>All Categories</span>
                        </a>
                        <?php foreach ($categories as $cat): ?>
                            <a href="shop.php?category=<?php echo $cat['category_id']; ?>&search=<?php echo urlencode($search_filter); ?>&sort=<?php echo $sort_filter; ?>" class="list-group-item list-group-item-action border-0 px-0 d-flex justify-content-between align-items-center <?php echo $category_filter === $cat['category_id'] ? 'text-primary fw-bold' : ''; ?>">
                                <span><?php echo htmlspecialchars($cat['name']); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Catalog Section -->
        <div class="col-lg-9">
            <!-- Catalog Header Controls -->
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
                <p class="text-muted mb-0">Showing <b><?php echo count($products); ?></b> gifts found</p>
                
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted small text-nowrap">Sort By:</span>
                    <select class="form-select form-select-sm border-0 shadow-sm rounded-pill px-3 py-2" style="width: auto;" onchange="location = this.value;">
                        <option value="shop.php?sort=newest&category=<?php echo $category_filter; ?>&search=<?php echo urlencode($search_filter); ?>" <?php echo $sort_filter === 'newest' ? 'selected' : ''; ?>>Newest Arrivals</option>
                        <option value="shop.php?sort=price_asc&category=<?php echo $category_filter; ?>&search=<?php echo urlencode($search_filter); ?>" <?php echo $sort_filter === 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="shop.php?sort=price_desc&category=<?php echo $category_filter; ?>&search=<?php echo urlencode($search_filter); ?>" <?php echo $sort_filter === 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                        <option value="shop.php?sort=name_asc&category=<?php echo $category_filter; ?>&search=<?php echo urlencode($search_filter); ?>" <?php echo $sort_filter === 'name_asc' ? 'selected' : ''; ?>>Alphabetical (A-Z)</option>
                    </select>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="row g-4">
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $prod): ?>
                        <div class="col-md-4 col-sm-6">
                            <div class="card orchid-card h-100 d-flex flex-column justify-content-between">
                                <div class="position-relative overflow-hidden" style="height: 220px;">
                                    <img src="<?php echo htmlspecialchars(getProductImage($prod['image_url'])); ?>" class="card-img-top w-100 h-100" alt="<?php echo htmlspecialchars($prod['name']); ?>">
                                    <span class="position-absolute top-0 end-0 bg-primary text-white text-xs px-2 py-1 m-3 rounded-pill" style="font-size: 0.75rem;">
                                        <?php echo htmlspecialchars($prod['category_name']); ?>
                                    </span>
                                </div>
                                <div class="card-body d-flex flex-column justify-content-between">
                                    <div>
                                        <h5 class="card-title text-dark fs-6 mb-1"><?php echo htmlspecialchars($prod['name']); ?></h5>
                                        <p class="card-text text-muted text-xs mb-3" style="font-size: 0.85rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                            <?php echo htmlspecialchars($prod['description']); ?>
                                        </p>
                                    </div>
                                    
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span class="fs-5 fw-bold text-primary"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($prod['price'], 2); ?></span>
                                            <span class="small text-muted">Stock: <b><?php echo $prod['stock_quantity']; ?></b></span>
                                        </div>
                                        
                                        <div class="d-flex gap-2 align-items-stretch">
                                            <a href="product.php?id=<?php echo $prod['product_id']; ?>"
                                               class="btn btn-orchid-outline btn-sm flex-fill d-flex align-items-center justify-content-center gap-1"
                                               style="padding-top: 8px; padding-bottom: 8px;">
                                                <i class="bi bi-eye"></i> Details
                                            </a>
                                            <form action="shop.php?category=<?php echo $category_filter; ?>&search=<?php echo urlencode($search_filter); ?>&sort=<?php echo $sort_filter; ?>" method="POST" class="flex-fill d-flex">
                                                <input type="hidden" name="action" value="add_to_cart">
                                                <input type="hidden" name="product_id" value="<?php echo $prod['product_id']; ?>">
                                                <button type="submit"
                                                        class="btn btn-orchid btn-sm w-100 d-flex align-items-center justify-content-center gap-1"
                                                        style="padding-top: 8px; padding-bottom: 8px;"
                                                        <?php echo $prod['stock_quantity'] <= 0 ? 'disabled' : ''; ?>>
                                                    <i class="bi bi-cart-plus"></i> Add
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5 text-muted">
                        <i class="bi bi-gift-fill fs-1 text-primary text-opacity-25 d-block mb-3"></i>
                        <p class="mb-0">No matching gifts found. Try searching for a different keyword or category.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
