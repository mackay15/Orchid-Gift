<?php
// setup.php - Database Initialization & Seeding Script

$host = 'localhost';
$username = 'root';
$password = '';

try {
    // 1. Establish connection to MySQL server
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS orchid_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    $pdo->exec("USE orchid_db;");

    echo "<div style='font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: auto;'>";
    echo "<h2 style='color: #5a189a;'>Orchid Gift & More System - Database Setup</h2>";
    echo "<p style='color: green;'>✔ Database <b>orchid_db</b> created or already exists.</p>";

    // 3. Create Tables
    
    // USERS Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        user_id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        role ENUM('customer', 'cashier', 'admin') NOT NULL DEFAULT 'customer',
        status ENUM('Active', 'Inactive') DEFAULT 'Active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;");
    echo "<p>✔ Table <b>users</b> created.</p>";

    // CATEGORIES Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
        category_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) UNIQUE NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;");
    echo "<p>✔ Table <b>categories</b> created.</p>";

    // PRODUCTS Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS products (
        product_id INT AUTO_INCREMENT PRIMARY KEY,
        category_id INT NOT NULL,
        name VARCHAR(150) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        stock_quantity INT NOT NULL DEFAULT 0,
        image_url VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE CASCADE
    ) ENGINE=InnoDB;");
    echo "<p>✔ Table <b>products</b> created.</p>";

    // ORDERS Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
        order_id INT AUTO_INCREMENT PRIMARY KEY,
        customer_id INT DEFAULT NULL,
        cashier_id INT DEFAULT NULL,
        order_type ENUM('online', 'walk-in') NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        order_status ENUM('Pending', 'Processing', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Pending',
        payment_status ENUM('Unpaid', 'Paid') NOT NULL DEFAULT 'Unpaid',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (customer_id) REFERENCES users(user_id) ON DELETE SET NULL,
        FOREIGN KEY (cashier_id) REFERENCES users(user_id) ON DELETE SET NULL
    ) ENGINE=InnoDB;");
    echo "<p>✔ Table <b>orders</b> created.</p>";

    // ORDER_ITEMS Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
        item_id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL,
        unit_price DECIMAL(10,2) NOT NULL,
        total_price DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
    ) ENGINE=InnoDB;");
    echo "<p>✔ Table <b>order_items</b> created.</p>";

    // REVIEWS Table (As explicitly requested by user)
    $pdo->exec("CREATE TABLE IF NOT EXISTS reviews (
        review_id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        customer_id INT NOT NULL,
        rating INT NOT NULL,
        review TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
        FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
        FOREIGN KEY (customer_id) REFERENCES users(user_id) ON DELETE CASCADE
    ) ENGINE=InnoDB;");
    echo "<p>✔ Table <b>reviews</b> created.</p>";

    // WISHLISTS Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS wishlists (
        wishlist_id INT AUTO_INCREMENT PRIMARY KEY,
        customer_id INT NOT NULL,
        product_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (customer_id) REFERENCES users(user_id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
    ) ENGINE=InnoDB;");
    echo "<p>✔ Table <b>wishlists</b> created.</p>";

    // SALES Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS sales (
        sales_id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        payment_method ENUM('Cash', 'Card', 'Mobile Money') NOT NULL,
        transaction_id VARCHAR(100) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE
    ) ENGINE=InnoDB;");
    echo "<p>✔ Table <b>sales</b> created.</p>";

    // PAYMENTS Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS payments (
        payment_id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        payment_method VARCHAR(50) NOT NULL,
        payment_status ENUM('Pending', 'Completed', 'Failed') NOT NULL DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE
    ) ENGINE=InnoDB;");
    echo "<p>✔ Table <b>payments</b> created.</p>";

    // NOTIFICATIONS Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS notifications (
        notification_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        message TEXT NOT NULL,
        is_read TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    ) ENGINE=InnoDB;");
    echo "<p>✔ Table <b>notifications</b> created.</p>";

    // 4. Seed Data
    echo "<h3 style='color: #5a189a;'>Seeding Database...</h3>";

    // Seed default users (if not exists)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $adminPass = password_hash('admin123', PASSWORD_BCRYPT);
        $cashierPass = password_hash('cashier123', PASSWORD_BCRYPT);
        $customerPass = password_hash('customer123', PASSWORD_BCRYPT);

        $pdo->exec("INSERT INTO users (username, password, email, full_name, role) VALUES 
            ('admin', '$adminPass', 'admin@orchid.com', 'Orchid Admin', 'admin'),
            ('cashier', '$cashierPass', 'cashier@orchid.com', 'Sarah Cashier', 'cashier'),
            ('customer', '$customerPass', 'customer@orchid.com', 'John Doe Customer', 'customer');");
        echo "<p>✔ Default users created (Passwords: username + '123').</p>";
    }

    // Seed categories (if not exists)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO categories (name, description) VALUES 
            ('Flowers', 'Fresh cut roses, mixed bouquets, floral baskets, and custom arrangements.'),
            ('Chocolate Gifts', 'Imported artisanal chocolates, premium truffles, and sweet combination boxes.'),
            ('Cakes', 'Freshly baked designer cakes, custom message cupcakes, and celebration treats.'),
            ('Hampers', 'Curated luxury hampers containing selection of gourmet foods, wine, and keepsakes.'),
            ('Customized Gifts', 'Personalized photo frames, engraved mugs, custom jewelry boxes, and keychains.'),
            ('Perfumes', 'Luxurious original designer fragrances for men and women.'),
            ('Jewelry', 'Elegant necklaces, sterling silver bracelets, earrings, and fashion statement pieces.'),
            ('Greeting Cards', 'Beautiful pop-up greeting cards, handcrafted letters, and warm wishes cards.');");
        echo "<p>✔ Categories seeded.</p>";
    }

    // Seed products (if not exists)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        // Fetch category IDs to link products accurately
        $cats = $pdo->query("SELECT name, category_id FROM categories")->fetchAll(PDO::FETCH_KEY_PAIR);

        $productsData = [
            [$cats['Flowers'], 'Midnight Rose Bouquet', 'A stunning arrangement of 12 premium dark red roses wrapped in black silk paper with a lavender ribbon.', 45.00, 15, 'https://images.unsplash.com/photo-1561181286-d3fee7d55364?auto=format&fit=crop&w=600&q=80'],
            [$cats['Flowers'], 'Orchid Meadow Vase', 'Elegant violet and purple orchids in a premium glass bowl vase.', 60.00, 8, 'https://images.unsplash.com/photo-1526047932273-341f2a7631f9?auto=format&fit=crop&w=600&q=80'],
            
            [$cats['Chocolate Gifts'], 'Gourmet Gold Truffles', 'A premium golden box of 24 handcrafted Belgian dark and milk chocolate truffles.', 32.50, 25, 'https://images.unsplash.com/photo-1548907040-4d42b52145ca?auto=format&fit=crop&w=600&q=80'],
            [$cats['Chocolate Gifts'], 'Chocolate Lover Hamper', 'Assorted Lindt bars, dark cocoa nibs, and chocolate-dipped almonds in a lavender basket.', 45.00, 10, 'https://images.unsplash.com/photo-1511381939415-e44015466834?auto=format&fit=crop&w=600&q=80'],

            [$cats['Cakes'], 'Velvet Orchid Cake', 'A delicious 1kg Red Velvet cake layered with premium cream cheese frosting and edible flower decoration.', 35.00, 5, 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=600&q=80'],

            [$cats['Hampers'], 'Luxury Spa & Tea Hamper', 'Relaxation hamper with lavender essential oils, floral tea leaves, mug, and organic honey.', 75.00, 12, 'https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=600&q=80'],
            
            [$cats['Customized Gifts'], 'Custom Engraved Wooden Frame', 'Premium oak wood photo frame with personalized engraving at the bottom.', 20.00, 50, 'https://images.unsplash.com/photo-1534447677768-be436bb09401?auto=format&fit=crop&w=600&q=80'],

            [$cats['Perfumes'], 'Orchid Noir Parfum', 'A deep floral woodsy signature parfum spray for women (100ml).', 85.00, 10, 'https://images.unsplash.com/photo-1541643600914-78b084683601?auto=format&fit=crop&w=600&q=80'],

            [$cats['Jewelry'], 'Sterling Silver Rose Pendant', 'Elegant handcrafted 925 sterling silver necklace with a rose pendant in a soft violet box.', 55.00, 15, 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?auto=format&fit=crop&w=600&q=80'],

            [$cats['Greeting Cards'], '3D Pop-Up Orchid Card', 'Laser-cut pop-up card displaying a beautiful blooming orchid garden.', 7.50, 40, 'https://images.unsplash.com/photo-1513151233558-d860c5398176?auto=format&fit=crop&w=600&q=80']
        ];

        $ins = $pdo->prepare("INSERT INTO products (category_id, name, description, price, stock_quantity, image_url) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($productsData as $row) {
            $ins->execute($row);
        }
        echo "<p>✔ Premium products seeded with image references.</p>";
    }

    // Seed Reviews (if not exists)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM reviews");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $pId = $pdo->query("SELECT product_id FROM products LIMIT 1")->fetchColumn();
        $cId = $pdo->query("SELECT user_id FROM users WHERE role='customer' LIMIT 1")->fetchColumn();
        if ($pId && $cId) {
            $pdo->exec("INSERT INTO reviews (product_id, customer_id, rating, review, status) VALUES 
                ($pId, $cId, 5, 'Absolutely gorgeous! The roses were fresh and smelled amazing. Worth every penny.', 'Approved'),
                ($pId, $cId, 4, 'Very nice presentation, but delivery was delayed by about 20 minutes.', 'Approved');");
            echo "<p>✔ Sample customer reviews seeded.</p>";
        }
    }

    // Seed mock sales report entries for analytics dashboard representation
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $cId = $pdo->query("SELECT user_id FROM users WHERE role='customer' LIMIT 1")->fetchColumn();
        $pId = $pdo->query("SELECT product_id, price FROM products LIMIT 1")->fetch();
        if ($cId && $pId) {
            // Seed a few past orders across last 3 days to make charts look great
            for ($i = 3; $i >= 0; $i--) {
                $dateStr = date('Y-m-d H:i:s', strtotime("-$i days"));
                
                // Add order
                $pdo->exec("INSERT INTO orders (customer_id, order_type, total_amount, order_status, payment_status, created_at) 
                            VALUES ($cId, 'online', {$pId['price']}, 'Completed', 'Paid', '$dateStr')");
                $orderId = $pdo->lastInsertId();
                
                // Add item
                $pdo->exec("INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price)
                            VALUES ($orderId, {$pId['product_id']}, 1, {$pId['price']}, {$pId['price']})");
                
                // Add payment
                $pdo->exec("INSERT INTO payments (order_id, amount, payment_method, payment_status, created_at)
                            VALUES ($orderId, {$pId['price']}, 'Card', 'Completed', '$dateStr')");
                
                // Add sale
                $pdo->exec("INSERT INTO sales (order_id, total_amount, payment_method, transaction_id, created_at)
                            VALUES ($orderId, {$pId['price']}, 'Card', 'TXN" . rand(100000, 999999) . "', '$dateStr')");
            }
            echo "<p>✔ Mock transaction data seeded for analytical reports.</p>";
        }
    }

    echo "<h3 style='color: green;'>Database Setup Successfully Completed!</h3>";
    echo "<p><a href='index.php' style='background-color: #5a189a; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Home Page</a></p>";
    echo "</div>";

} catch (PDOException $e) {
    die("<div style='color: red; padding: 20px; font-family: Arial, sans-serif;'>Database Connection or Creation Failed: " . $e->getMessage() . "</div>");
}
?>
