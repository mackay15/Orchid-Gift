<?php
// cashier/receipt.php - Print receipt view
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole(['cashier', 'admin']);

$orderId = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

// Fetch order
$stmt = $pdo->prepare("SELECT o.*, u.full_name as customer_name, c.full_name as cashier_name, s.payment_method, s.transaction_id 
                       FROM orders o 
                       LEFT JOIN users u ON o.customer_id = u.user_id 
                       LEFT JOIN users c ON o.cashier_id = c.user_id 
                       LEFT JOIN sales s ON o.order_id = s.order_id 
                       WHERE o.order_id = ?");
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) {
    die("<div style='text-align: center; font-family: sans-serif; padding: 50px;'><h3 style='color: red;'>Order Not Found!</h3><a href='index.php'>Return to POS</a></div>");
}

// Fetch order items
$stmt = $pdo->prepare("SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.product_id WHERE oi.order_id = ?");
$stmt->execute([$orderId]);
$items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ORCHID Receipt #<?php echo $orderId; ?></title>
    <!-- Custom CSS -->
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        body {
            background-color: #f1ecf6;
            margin: 0;
            padding: 20px;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: none;
                padding: 0;
            }
            .receipt-box {
                box-shadow: none;
                margin: 0;
                width: 100%;
            }
        }
    </style>
</head>
<body onload="window.print()">

<div class="no-print text-center mb-4">
    <button onclick="window.print()" class="btn btn-orchid btn-sm me-2"><i class="bi bi-printer"></i> Print Receipt</button>
    <a href="index.php" class="btn btn-orchid-outline btn-sm"><i class="bi bi-cpu"></i> New POS Transaction</a>
</div>

<div class="receipt-box">
    <div class="receipt-header">
        <h4 style="margin: 0 0 5px 0; font-weight: bold; letter-spacing: 1px;">ORCHID GIFT & MORE</h4>
        <small>45 Orchid Garden St, POS Plaza</small><br>
        <small>Phone: +1 (233) 555-ORCHID</small><br>
        <small><b>RECEIPT</b></small>
    </div>
    
    <div style="font-size: 0.85rem; line-height: 1.5;">
        <div><b>Date:</b> <?php echo date('Y-m-d H:i:s', strtotime($order['created_at'])); ?></div>
        <div><b>Receipt #:</b> POS-<?php echo $order['order_id']; ?></div>
        <div><b>Cashier:</b> <?php echo htmlspecialchars($order['cashier_name'] ?? 'System/Online'); ?></div>
        <div><b>Customer:</b> <?php echo htmlspecialchars($order['customer_name'] ?? 'Guest Customer'); ?></div>
    </div>
    
    <div class="receipt-divider"></div>
    
    <!-- Table items -->
    <table style="width: 100%; font-size: 0.85rem; border-collapse: collapse;">
        <thead>
            <tr style="border-bottom: 1px dashed #333;">
                <th style="text-align: left; padding-bottom: 5px;">Item</th>
                <th style="text-align: center; padding-bottom: 5px; width: 60px;">Qty</th>
                <th style="text-align: right; padding-bottom: 5px; width: 80px;">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php 
                $subtotal = 0.00;
                foreach ($items as $item): 
                    $subtotal += $item['total_price'];
            ?>
                <tr>
                    <td style="padding: 5px 0;"><?php echo htmlspecialchars($item['name']); ?></td>
                    <td style="text-align: center; padding: 5px 0;"><?php echo $item['quantity']; ?></td>
                    <td style="text-align: right; padding: 5px 0;"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($item['total_price'], 2); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div class="receipt-divider"></div>
    
    <?php 
        $tax = $subtotal * 0.05;
        $total = $subtotal + $tax;
    ?>
    <div style="font-size: 0.85rem; line-height: 1.5;">
        <div style="display: flex; justify-content: space-between;">
            <span>Subtotal:</span>
            <span><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($subtotal, 2); ?></span>
        </div>
        <div style="display: flex; justify-content: space-between;">
            <span>VAT (5%):</span>
            <span><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($tax, 2); ?></span>
        </div>
        <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 1rem; margin-top: 5px;">
            <span>TOTAL:</span>
            <span><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($total, 2); ?></span>
        </div>
    </div>
    
    <div class="receipt-divider"></div>
    
    <div style="font-size: 0.85rem; line-height: 1.4;">
        <div><b>Payment Type:</b> <?php echo $order['payment_method'] ?? 'Cash'; ?></div>
        <div><b>Trans ID:</b> <?php echo $order['transaction_id'] ?? 'N/A'; ?></div>
        <div><b>Status:</b> PAID</div>
    </div>
    
    <div class="receipt-divider"></div>
    
    <div style="text-align: center; font-size: 0.85rem; margin-top: 15px;">
        <div>Where Every Gift Tells a Story</div>
        <div style="margin-top: 5px; font-weight: bold;">THANK YOU FOR SHOPPING!</div>
    </div>
</div>

</body>
</html>
