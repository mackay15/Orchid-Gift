<?php
// admin/reports.php - Admin Sales Report Exporter
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('admin');

// Handle CSV Export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    // Clear buffer to prevent header issues
    ob_end_clean();
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=ORCHID_Sales_Report_' . date('Ymd_His') . '.csv');
    
    $output = fopen('php://output', 'w');
    
    // Header Row
    fputcsv($output, ['Sale ID', 'Order ID', 'Channel', 'Customer Name', 'Cashier/Attendant', 'Total Amount ($)', 'Payment Option', 'Transaction Reference ID', 'Date/Time']);
    
    // Fetch data
    $query = "SELECT s.sales_id, s.order_id, o.order_type, u.full_name as customer_name, c.full_name as cashier_name, s.total_amount, s.payment_method, s.transaction_id, s.created_at 
              FROM sales s
              JOIN orders o ON s.order_id = o.order_id
              LEFT JOIN users u ON o.customer_id = u.user_id
              LEFT JOIN users c ON o.cashier_id = c.user_id
              ORDER BY s.created_at DESC";
    $stmt = $pdo->query($query);
    
    while ($row = $stmt->fetch()) {
        fputcsv($output, [
            $row['sales_id'],
            $row['order_id'],
            strtoupper($row['order_type']),
            $row['customer_name'] ?? 'Guest Customer',
            $row['cashier_name'] ?? 'Online System',
            number_format($row['total_amount'], 2),
            $row['payment_method'],
            $row['transaction_id'] ?? 'Pending Cash COD',
            $row['created_at']
        ]);
    }
    
    fclose($output);
    exit();
}

// Otherwise, display a gorgeous preview screen
// Calculate brief previews
$total_revenue = $pdo->query("SELECT SUM(total_amount) FROM sales")->fetchColumn() ?? 0;
$momo_revenue = $pdo->query("SELECT SUM(total_amount) FROM sales WHERE payment_method='Mobile Money'")->fetchColumn() ?? 0;
$card_revenue = $pdo->query("SELECT SUM(total_amount) FROM sales WHERE payment_method='Card'")->fetchColumn() ?? 0;
$cash_revenue = $pdo->query("SELECT SUM(total_amount) FROM sales WHERE payment_method='Cash'")->fetchColumn() ?? 0;

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="brand-font mb-0 text-dark"><i class="bi bi-file-earmark-spreadsheet text-primary me-2"></i>Business Sales Reports</h2>
        <a href="index.php" class="btn btn-orchid-outline btn-sm"><i class="bi bi-speedometer2"></i> Admin Panel</a>
    </div>

    <div class="row g-4">
        <!-- Preview Analytics Grid -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <h5 class="fw-bold mb-4 brand-font text-dark">Revenue Channel Distribution</h5>
                
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr class="text-muted small">
                                <th>Billing Option</th>
                                <th>Transaction Stream</th>
                                <th>Revenue Generated</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><i class="bi bi-phone-vibrate text-primary fs-5 me-2"></i><b>Mobile Money (Momo)</b></td>
                                <td class="small text-muted">All online & walk-in MoMo payments</td>
                                <td class="fw-bold text-primary">$<?php echo number_format($momo_revenue, 2); ?></td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-credit-card-2-back text-primary fs-5 me-2"></i><b>Card Payments</b></td>
                                <td class="small text-muted">All Visa, Mastercards, POS cards</td>
                                <td class="fw-bold text-primary">$<?php echo number_format($card_revenue, 2); ?></td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-cash-stack text-success fs-5 me-2"></i><b>Cash Transactions</b></td>
                                <td class="small text-muted">Physical registers & COD payments</td>
                                <td class="fw-bold text-success">$<?php echo number_format($cash_revenue, 2); ?></td>
                            </tr>
                            <tr class="table-light">
                                <td><b>TOTAL REVENUE</b></td>
                                <td>All channels aggregate</td>
                                <td class="fw-bold text-primary fs-5">$<?php echo number_format($total_revenue, 2); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Export Action Card -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 glass-panel text-center">
                <i class="bi bi-filetype-csv display-3 text-primary mb-3"></i>
                <h5 class="fw-bold brand-font text-dark mb-2">Export Data File</h5>
                <p class="text-muted small mb-4">Export transactional audit sheet containing order types, item amounts, client registrations, and cashier names for bookkeeping.</p>
                
                <a href="reports.php?export=csv" class="btn btn-orchid w-100 py-3 shadow"><i class="bi bi-cloud-arrow-down-fill me-2"></i> Download CSV Spreadsheet</a>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
