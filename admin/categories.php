<?php
// admin/categories.php - Admin Category Manager
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('admin');

$error = '';
$success = '';

// Edit Mode
$edit_id = isset($_GET['edit_id']) ? intval($_GET['edit_id']) : 0;
$edit_category = null;
if ($edit_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE category_id = ?");
    $stmt->execute([$edit_id]);
    $edit_category = $stmt->fetch();
}

// Handle Forms
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add' || $action === 'update') {
        $name = trim($_POST['name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        
        if (empty($name)) {
            $error = "Category Name is required.";
        } else {
            if ($action === 'add') {
                try {
                    $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
                    $stmt->execute([$name, $desc]);
                    $success = "Category added successfully!";
                } catch (PDOException $e) {
                    $error = "This Category Name is already registered.";
                }
            } else {
                try {
                    $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ? WHERE category_id = ?");
                    $stmt->execute([$name, $desc, $edit_id]);
                    $success = "Category updated successfully!";
                    $edit_id = 0;
                    $edit_category = null;
                } catch (PDOException $e) {
                    $error = "This Category Name is already taken.";
                }
            }
        }
    }
    
    elseif ($action === 'delete') {
        $del_id = intval($_POST['category_id']);
        $stmt = $pdo->prepare("DELETE FROM categories WHERE category_id = ?");
        $stmt->execute([$del_id]);
        $success = "Category and its associated products deleted successfully!";
    }
}

// Fetch categories
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
        <!-- Categories Table List -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <h4 class="brand-font mb-4 text-dark"><i class="bi bi-tags text-primary me-2"></i>Categories List</h4>
                
                <?php if (empty($categories)): ?>
                    <div class="text-center py-5 text-muted">No categories registered.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr class="text-muted small">
                                    <th>Category Name</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $cat): ?>
                                    <tr>
                                        <td><b><?php echo htmlspecialchars($cat['name']); ?></b></td>
                                        <td class="small text-secondary"><?php echo htmlspecialchars($cat['description']); ?></td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="categories.php?edit_id=<?php echo $cat['category_id']; ?>" class="btn btn-sm btn-orchid-outline btn-xs px-2 py-1"><i class="bi bi-pencil-square"></i></a>
                                                
                                                <form action="categories.php" method="POST" onsubmit="return confirm('Deleting category will DELETE all its products. Proceed?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="category_id" value="<?php echo $cat['category_id']; ?>">
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
                    <i class="bi <?php echo $edit_category ? 'bi-pencil-square' : 'bi-plus-circle-fill'; ?> text-primary me-2"></i>
                    <?php echo $edit_category ? 'Edit Category' : 'Add New Category'; ?>
                </h5>
                
                <form action="categories.php<?php echo $edit_category ? '?edit_id='.$edit_id : ''; ?>" method="POST">
                    <input type="hidden" name="action" value="<?php echo $edit_category ? 'update' : 'add'; ?>">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label text-muted small fw-bold">Category Name *</label>
                        <input type="text" class="form-control form-control-sm" id="name" name="name" required placeholder="e.g. Perfumes" value="<?php echo $edit_category ? htmlspecialchars($edit_category['name']) : ''; ?>">
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label text-muted small fw-bold">Description</label>
                        <textarea class="form-control form-control-sm" id="description" name="description" rows="5" placeholder="Details about this category..."><?php echo $edit_category ? htmlspecialchars($edit_category['description']) : ''; ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-orchid w-100 py-2">
                        <?php echo $edit_category ? 'Update Category' : 'Create Category'; ?>
                    </button>
                    
                    <?php if ($edit_category): ?>
                        <a href="categories.php" class="btn btn-orchid-outline w-100 mt-2 py-1.5">Cancel Editing</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
