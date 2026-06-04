<?php
// index.php - Main Landing Page
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';

// Fetch categories
try {
    $categories = $pdo->query("SELECT * FROM categories LIMIT 6")->fetchAll();
    
    // Fetch best selling/featured products
    $products = $pdo->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.category_id LIMIT 4")->fetchAll();
    
    // Fetch approved customer reviews
    $reviews = $pdo->query("SELECT r.*, u.full_name, p.name as product_name 
                             FROM reviews r 
                             JOIN users u ON r.customer_id = u.user_id 
                             JOIN products p ON r.product_id = p.product_id 
                             WHERE r.status = 'Approved' 
                             ORDER BY r.created_at DESC 
                             LIMIT 3")->fetchAll();
} catch (PDOException $e) {
    // Fallback if schema is missing (will redirect to setup in db.php but in case)
    $categories = [];
    $products = [];
    $reviews = [];
}
?>

<!-- Hero Banner -->
<section class="hero-banner d-flex align-items-center">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <h6 class="text-primary text-uppercase fw-bold mb-3" style="letter-spacing: 2px;">Premium Gift Shop & POS</h6>
                <h1 class="display-4 text-dark mb-4" style="line-height: 1.2;">Making Every Gift <span class="text-primary font-italic">Memorable</span></h1>
                <p class="lead text-muted mb-5">Discover carefully curated flowers, handcrafted designer cakes, premium chocolate baskets, and customized keepsakes designed to tell your unique story.</p>
                <div class="d-flex gap-3">
                    <a href="<?php echo $base; ?>/customer/shop.php" class="btn btn-orchid btn-lg px-4">Browse Collection</a>
                    <a href="#about" class="btn btn-orchid-outline btn-lg px-4">Our Story</a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="position-relative">
                    <!-- Premium Glassmorphic Card Overlaying Image -->
                    <div class="card glass-panel p-4 position-absolute top-50 start-0 translate-middle-y z-3 d-none d-sm-block shadow" style="width: 280px; left: -20px !important;">
                        <div class="d-flex align-items-center mb-3">
                            <span class="p-2 bg-orchid-grad rounded-circle text-white me-3"><i class="bi bi-gift fs-5"></i></span>
                            <div>
                                <h6 class="mb-0 fw-bold">Special Offer</h6>
                                <small class="text-muted">Gift Wrapped Free</small>
                            </div>
                        </div>
                        <p class="small text-secondary mb-0">Use code <b class="text-primary">ORCHIDLOVE</b> at checkout for 10% discount on personalized engraving.</p>
                    </div>
                    <img src="https://images.unsplash.com/photo-1549465220-1a8b9238cd48?auto=format&fit=crop&w=800&q=80" alt="Beautifully Wrapped Gift" class="img-fluid rounded-4 shadow-lg w-100" style="object-fit: cover; height: 450px;">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Categories -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="h1 mb-2">Explore Our Collections</h2>
            <p class="text-muted">Choose the perfect category to express your thoughts</p>
        </div>
        
        <div class="row g-4">
            <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $cat): 
                    // Assign default display icons/images for categories
                    $img = 'https://images.unsplash.com/photo-1513151233558-d860c5398176?auto=format&fit=crop&w=300&q=80';
                    if ($cat['name'] == 'Flowers') $img = 'https://images.unsplash.com/photo-1561181286-d3fee7d55364?auto=format&fit=crop&w=300&q=80';
                    if ($cat['name'] == 'Chocolate Gifts') $img = 'https://images.unsplash.com/photo-1548907040-4d42b52145ca?auto=format&fit=crop&w=300&q=80';
                    if ($cat['name'] == 'Cakes') $img = 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=300&q=80';
                    if ($cat['name'] == 'Hampers') $img = 'https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=300&q=80';
                    if ($cat['name'] == 'Customized Gifts') $img = 'https://images.unsplash.com/photo-1534447677768-be436bb09401?auto=format&fit=crop&w=300&q=80';
                    if ($cat['name'] == 'Perfumes') $img = 'https://images.unsplash.com/photo-1541643600914-78b084683601?auto=format&fit=crop&w=300&q=80';
                ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card orchid-card h-100">
                            <div class="position-relative overflow-hidden" style="height: 200px;">
                                <img src="<?php echo $img; ?>" class="card-img-top w-100 h-100" alt="<?php echo htmlspecialchars($cat['name']); ?>">
                                <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-25"></div>
                                <h3 class="position-absolute bottom-0 start-0 m-3 text-white h4"><?php echo htmlspecialchars($cat['name']); ?></h3>
                            </div>
                            <div class="card-body">
                                <p class="card-text text-muted small"><?php echo htmlspecialchars($cat['description']); ?></p>
                                <a href="<?php echo $base; ?>/customer/shop.php?category=<?php echo $cat['category_id']; ?>" class="btn btn-link text-primary p-0 fw-semibold text-decoration-none">
                                    Browse Products <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center text-muted">No categories available. Please seed database via setup.php.</div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Featured/Best Sellers -->
<section class="py-5" style="background-color: #fcf9f5;">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-5">
            <div>
                <h2 class="h1 mb-2">Featured Gift Ideas</h2>
                <p class="text-muted mb-0">Carefully handpicked items for your special celebrations</p>
            </div>
            <a href="<?php echo $base; ?>/customer/shop.php" class="btn btn-orchid-outline d-none d-sm-inline-block">View All Products</a>
        </div>
        
        <div class="row g-4">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $prod): ?>
                    <div class="col-lg-3 col-md-6">
                        <div class="card orchid-card h-100">
                            <div class="position-relative overflow-hidden" style="height: 240px;">
                                <img src="<?php echo htmlspecialchars($prod['image_url']); ?>" class="card-img-top w-100 h-100" alt="<?php echo htmlspecialchars($prod['name']); ?>">
                                <span class="position-absolute top-0 end-0 bg-primary text-white text-xs px-2 py-1 m-3 rounded-pill" style="font-size: 0.75rem;">
                                    <?php echo htmlspecialchars($prod['category_name']); ?>
                                </span>
                            </div>
                            <div class="card-body d-flex flex-column justify-content-between">
                                <div>
                                    <h5 class="card-title text-dark fs-6 mb-1"><?php echo htmlspecialchars($prod['name']); ?></h5>
                                    <p class="card-text text-muted text-xs mb-3 text-truncate" style="font-size: 0.85rem;"><?php echo htmlspecialchars($prod['description']); ?></p>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fs-5 fw-bold text-primary">$<?php echo number_format($prod['price'], 2); ?></span>
                                    <a href="<?php echo $base; ?>/customer/product.php?id=<?php echo $prod['product_id']; ?>" class="btn btn-orchid btn-sm"><i class="bi bi-eye me-1"></i> View</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center text-muted">No products available. Please seed database.</div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Customer Reviews Slider / Section -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="h1 mb-2">What Our Customers Say</h2>
            <p class="text-muted">Honest reviews from customers who shared their special moments with us</p>
        </div>
        
        <div class="row g-4 justify-content-center">
            <?php if (!empty($reviews)): ?>
                <?php foreach ($reviews as $rev): ?>
                    <div class="col-md-4">
                        <div class="card glass-panel h-100 p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($rev['full_name']); ?></h6>
                                    <small class="text-muted">Reviewed <a href="<?php echo $base; ?>/customer/product.php?id=<?php echo $rev['product_id']; ?>" class="text-decoration-none text-primary"><?php echo htmlspecialchars($rev['product_name']); ?></a></small>
                                </div>
                                <div class="rating-stars">
                                    <?php for($i=1; $i<=5; $i++): ?>
                                        <i class="bi bi-star-fill<?php echo ($i <= $rev['rating']) ? '' : '-half text-muted'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <p class="card-text text-secondary italic small">"<?php echo htmlspecialchars($rev['review']); ?>"</p>
                            <small class="text-muted d-block mt-auto text-end"><?php echo date('M d, Y', strtotime($rev['created_at'])); ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center text-muted">
                    <p class="mb-0">No reviews published yet. Be the first to share your experience!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- About Section -->
<section id="about" class="py-5" style="background-color: #fcf9f5;">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <img src="https://images.unsplash.com/photo-1526047932273-341f2a7631f9?auto=format&fit=crop&w=800&q=80" alt="Orchid florist shop" class="img-fluid rounded-4 shadow">
            </div>
            <div class="col-lg-6">
                <h2 class="display-5 mb-4">About ORCHID GIFT AND MORE</h2>
                <h5 class="text-primary italic mb-3">"Thoughtful Gifts for Every Occasion"</h5>
                <p class="text-muted mb-4">Established as a small boutique, Orchid Gift & More has grown into a leading digital and walk-in platform. We aim to automate local florist and gift shop business operations while maintaining that cozy, personal touch. Our automated point of sales helps processing walk-in orders with high efficiency, while our e-commerce portal lets customers order customized gift packages with personalized reviews.</p>
                
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill text-primary me-2"></i>
                            <span class="small fw-bold">Fresh, Handpicked Florals</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill text-primary me-2"></i>
                            <span class="small fw-bold">Secure Online Ordering</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill text-primary me-2"></i>
                            <span class="small fw-bold">Custom Craft Engravings</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill text-primary me-2"></i>
                            <span class="small fw-bold">Reliable Delivery & POS</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
require_once __DIR__ . '/includes/footer.php';
?>
