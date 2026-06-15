<?php
// admin/products.php - Admin Product Manager
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('admin');

$error = '';
$success = '';

// Edit Mode Check
$edit_id = isset($_GET['edit_id']) ? intval($_GET['edit_id']) : 0;
$edit_product = null;
if ($edit_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->execute([$edit_id]);
    $edit_product = $stmt->fetch();
}

// Handle Form Submissions (Add / Update / Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add' || $action === 'update') {
        $name = trim($_POST['name'] ?? '');
        $cat_id = intval($_POST['category_id'] ?? 0);
        $price = floatval($_POST['price'] ?? 0);
        $stock = intval($_POST['stock_quantity'] ?? 0);
        $img = trim($_POST['image_url'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        
        if (empty($name) || $cat_id <= 0 || $price <= 0 || $stock < 0) {
            $error = "Please fill in all required fields accurately (price and stock must be positive).";
        } else {
            // Handle Local Image Upload if present
            if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
                $file_name = $_FILES['product_image']['name'];
                $file_tmp = $_FILES['product_image']['tmp_name'];
                $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (in_array($ext, $allowed)) {
                    $upload_dir = __DIR__ . '/../assets/uploads';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    $new_name = uniqid('prod_', true) . '.' . $ext;
                    $dest_path = $upload_dir . '/' . $new_name;
                    
                    if (move_uploaded_file($file_tmp, $dest_path)) {
                        // If updating and previous image was local, delete it
                        if ($action === 'update' && $edit_product && !empty($edit_product['image_url'])) {
                            if (strpos($edit_product['image_url'], 'assets/uploads/') === 0) {
                                $old_local_path = __DIR__ . '/../' . $edit_product['image_url'];
                                if (file_exists($old_local_path)) {
                                    unlink($old_local_path);
                                }
                            }
                        }
                        $img = 'assets/uploads/' . $new_name;
                    } else {
                        $error = "Failed to upload image locally.";
                    }
                } else {
                    $error = "Invalid file type. Only JPG, JPEG, PNG, GIF, and WEBP are allowed.";
                }
            } elseif (isset($_FILES['product_image']) && $_FILES['product_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $error = "Error during file upload: " . $_FILES['product_image']['error'];
            }
            
            if (empty($error)) {
                if (empty($img)) {
                    if ($action === 'update' && $edit_product) {
                        $img = $edit_product['image_url'];
                    } else {
                        $img = 'https://images.unsplash.com/photo-1513151233558-d860c5398176?auto=format&fit=crop&w=600&q=80';
                    }
                }
                
                if ($action === 'add') {
                    $stmt = $pdo->prepare("INSERT INTO products (category_id, name, description, price, stock_quantity, image_url) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$cat_id, $name, $desc, $price, $stock, $img]);
                    $success = "Product added successfully!";
                } else {
                    $stmt = $pdo->prepare("UPDATE products SET category_id = ?, name = ?, description = ?, price = ?, stock_quantity = ?, image_url = ? WHERE product_id = ?");
                    $stmt->execute([$cat_id, $name, $desc, $price, $stock, $img, $edit_id]);
                    $success = "Product updated successfully!";
                    $edit_id = 0;
                    $edit_product = null;
                }
            }
        }
    }
    
    elseif ($action === 'delete') {
        $del_id = intval($_POST['product_id']);
        // Fetch product to see if there is a local image to delete
        $stmt = $pdo->prepare("SELECT image_url FROM products WHERE product_id = ?");
        $stmt->execute([$del_id]);
        $prod_to_del = $stmt->fetch();
        if ($prod_to_del && !empty($prod_to_del['image_url'])) {
            $img_url = $prod_to_del['image_url'];
            if (strpos($img_url, 'assets/uploads/') === 0) {
                $local_path = __DIR__ . '/../' . $img_url;
                if (file_exists($local_path)) {
                    unlink($local_path);
                }
            }
        }
        $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt->execute([$del_id]);
        $success = "Product deleted successfully!";
    }
}

// Fetch all products
$search = $_GET['search'] ?? '';
$query = "SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.category_id WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND p.name LIKE ?";
    $params[] = "%$search%";
}
$query .= " ORDER BY p.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Fetch categories for form selects
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
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
        <!-- Products List Panel -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
                    <h4 class="brand-font mb-0 text-dark"><i class="bi bi-gift text-primary me-2"></i>Product Catalog</h4>
                    
                    <form action="products.php" method="GET" class="d-flex gap-2">
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search item..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn btn-orchid btn-sm"><i class="bi bi-search"></i></button>
                    </form>
                </div>
                
                <?php if (empty($products)): ?>
                    <div class="text-center py-5 text-muted">No products found.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr class="text-muted small">
                                    <th>Image</th>
                                    <th>Product Details</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $p): ?>
                                    <tr>
                                        <td>
                                            <img src="<?php echo htmlspecialchars(getProductImage($p['image_url'])); ?>" alt="" class="rounded shadow-sm" style="width: 50px; height: 50px; object-fit: cover;">
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($p['name']); ?></div>
                                            <span class="badge bg-secondary-subtle text-secondary" style="font-size: 0.7rem;"><?php echo htmlspecialchars($p['category_name']); ?></span>
                                        </td>
                                        <td class="fw-bold text-primary"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($p['price'], 2); ?></td>
                                        <td>
                                            <?php if ($p['stock_quantity'] <= 3): ?>
                                                <span class="badge bg-danger bg-opacity-10 text-danger p-2">Low: <?php echo $p['stock_quantity']; ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-success bg-opacity-10 text-success p-2">Healthy: <?php echo $p['stock_quantity']; ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="products.php?edit_id=<?php echo $p['product_id']; ?>" class="btn btn-sm btn-orchid-outline btn-xs px-2 py-1"><i class="bi bi-pencil-square"></i></a>
                                                
                                                <form action="products.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="product_id" value="<?php echo $p['product_id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger btn-xs px-2 py-1"><i class="bi bi-trash"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Add / Edit Form Panel -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 glass-panel">
                <h5 class="fw-bold mb-4 brand-font text-dark">
                    <i class="bi <?php echo $edit_product ? 'bi-pencil-square' : 'bi-plus-circle-fill'; ?> text-primary me-2"></i>
                    <?php echo $edit_product ? 'Edit Product' : 'Add New Product'; ?>
                </h5>
                
                <form action="products.php<?php echo $edit_product ? '?edit_id='.$edit_id : ''; ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="<?php echo $edit_product ? 'update' : 'add'; ?>">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label text-muted small fw-bold">Product Name *</label>
                        <input type="text" class="form-control form-control-sm" id="name" name="name" required placeholder="e.g. Lavender Joy Basket" value="<?php echo $edit_product ? htmlspecialchars($edit_product['name']) : ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="category_id" class="form-label text-muted small fw-bold">Category *</label>
                        <select name="category_id" id="category_id" class="form-select form-select-sm" required>
                            <option value="">-- Choose Category --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['category_id']; ?>" <?php echo ($edit_product && $edit_product['category_id'] == $cat['category_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="price" class="form-label text-muted small fw-bold">Price (<?php echo CURRENCY_SYMBOL; ?>) *</label>
                            <input type="number" step="0.01" class="form-control form-control-sm" id="price" name="price" required placeholder="0.00" value="<?php echo $edit_product ? $edit_product['price'] : ''; ?>">
                        </div>
                        <div class="col-6">
                            <label for="stock_quantity" class="form-label text-muted small fw-bold">Stock Qty *</label>
                            <input type="number" class="form-control form-control-sm" id="stock_quantity" name="stock_quantity" required placeholder="0" value="<?php echo $edit_product ? $edit_product['stock_quantity'] : ''; ?>">
                        </div>
                    </div>

                    <?php if ($edit_product && !empty($edit_product['image_url'])): ?>
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold d-block">Current Product Image</label>
                            <img src="<?php echo htmlspecialchars(getProductImage($edit_product['image_url'])); ?>" alt="Current Product Image" class="img-thumbnail" style="max-height: 120px; object-fit: cover;">
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="product_image" class="form-label text-muted small fw-bold">Upload Local Image (Recommended)</label>
                        <input type="file" class="form-control form-control-sm" id="product_image" name="product_image" accept="image/*">
                    </div>

                    <div class="mb-3">
                        <label for="image_url" class="form-label text-muted small fw-bold">OR External Image URL</label>
                        <input type="url" class="form-control form-control-sm" id="image_url" name="image_url" placeholder="https://unsplash.com/..." value="<?php echo $edit_product ? htmlspecialchars($edit_product['image_url']) : ''; ?>">
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label text-muted small fw-bold">Description</label>
                        <textarea class="form-control form-control-sm" id="description" name="description" rows="4" placeholder="Product details..."><?php echo $edit_product ? htmlspecialchars($edit_product['description']) : ''; ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-orchid w-100 py-2">
                        <?php echo $edit_product ? 'Update Details' : 'Add to Catalog'; ?>
                    </button>
                    
                    <?php if ($edit_product): ?>
                        <a href="products.php" class="btn btn-orchid-outline w-100 mt-2 py-1.5">Cancel Editing</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
