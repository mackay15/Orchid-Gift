# Orchid Gift & More Management System (MVP)

A premium, glassmorphic PHP and MySQL-based E-Commerce and POS management system designed for boutique florists, custom gift shops, and premium bakeries. The system automates both online customer ordering and physical walk-in point-of-sale (POS) operations while providing a centralized administration panel.

---

## 🚀 Key Features & MVP Modules

The application is structured around **three distinct user roles**, each tailored to streamline specific areas of business operations:

### 1. Customer E-Commerce Portal (`/customer`)
- **Product Discovery:** Browse products with category filters and search capabilities.
- **Product Details & Social Proof:** View detailed descriptions, stock availability, and user reviews.
- **Cart & Wishlist Management:** Interactive shopping cart with persistent items and personal wishlists.
- **Secure Checkout:** Order placement with custom payment and delivery methods.
- **Order History & Reviews:** Track order status and submit ratings/reviews for purchased products.

### 2. Cashier POS System (`/cashier`)
- **Walk-in Point of Sale:** Clean POS interface to quickly find products, build orders, and select payment methods (Cash, Card, Mobile Money).
- **Customer Management:** Capture customer details for walk-in transactions to track purchase history.
- **Transactions Log:** Record sales and print clean, professional purchase receipts.

### 3. Central Admin Panel (`/admin`)
- **Interactive Dashboard:** High-level summary of total sales, pending orders, user counts, and low-stock alerts.
- **Inventory Control:** Full CRUD operations for **Categories** and **Products** (manage names, descriptions, pricing, image URLs, and stock levels).
- **Order Processing:** Update order fulfillment stages (Pending, Processing, Completed, Cancelled) and payment statuses.
- **User Management:** Monitor, activate, and deactivate registered users (Customers, Cashiers, Admins).
- **Review Moderation:** Approve or reject customer reviews before they display on the public storefront.
- **Sales Analytics Reports:** Data tables documenting transaction history, payment breakdown, and total revenue.

---

## 🛠️ Tech Stack & Requirements

- **Backend Logic:** PHP (PDO Extension)
- **Database:** MySQL / MariaDB
- **Frontend UI:** Bootstrap 5, Bootstrap Icons, Custom CSS with glassmorphism styling
- **Assets & Styling:** Styled with custom color tokens (Deep Purples `#5a189a` and Soft Lavenders) to present a premium look.

---

## 📂 Database Schema Overview (`database.sql`)

The database consists of **10 interconnected tables** designed to support both online retail and point of sales:

1. **`users`**: Manages credentials, contact info, status (Active/Inactive), and access roles (`customer`, `cashier`, `admin`).
2. **`categories`**: Grouping for gifts (e.g., Flowers, Cakes, Chocolate, Perfumes, Hampers).
3. **`products`**: Product inventory containing prices, descriptions, image URLs, and stock levels.
4. **`orders`**: Tracks individual orders, marking them as `online` or `walk-in` (POS) along with order and payment status.
5. **`order_items`**: Line-by-line item details for each order, preserving historical pricing.
6. **`reviews`**: Customer reviews containing star ratings and textual feedback with admin moderation states (`Pending`, `Approved`, `Rejected`).
7. **`wishlists`**: Saved products list curated by online customers.
8. **`sales`**: Financial records of completed orders, storing payment methods and transaction IDs.
9. **`payments`**: Payment processing records connected to order fulfillment.
10. **`notifications`**: User-specific notification logs to alert on orders and system updates.

---

## ⚙️ Quick Installation & Setup

1. **Move Project to Server:**
   Clone or copy the `Orchid-Gift` folder into your local web server root (e.g., `C:/xampp/htdocs/Orchid-Gift`).

2. **Start Web & Database Server:**
   Open XAMPP (or equivalent) and start **Apache** and **MySQL**.

3. **Initialize the Database:**
   - Open your browser and navigate to `http://localhost/Orchid-Gift/index.php`.
   - The application will detect if the database does not exist and **automatically redirect** you to `setup.php`.
   - Alternatively, visit `http://localhost/Orchid-Gift/setup.php` directly.
   - Click setup to create the database (`orchid_db`), create tables, and populate seed data (default categories, premium products, sample orders, reviews, and test users).

---

## 🔑 Default Seeded Accounts

The database setup script pre-populates three roles for testing. All passwords follow the pattern `[username]123`.

| Username | Password | Role | Access Level |
| :--- | :--- | :--- | :--- |
| **`admin`** | `admin123` | Administrator | Full access to `/admin` control panel |
| **`cashier`** | `cashier123` | Cashier | Access to `/cashier` POS system |
| **`customer`** | `customer123` | Customer | Access to `/customer` online portal |

---

## 📁 File Structure

```text
Orchid-Gift/
├── admin/               # Administrative panel dashboards and management
├── assets/              # Premium CSS stylesheets, logos, and placeholders
├── cashier/             # Walk-in POS checkout interface
├── customer/            # Customer shopping cart, checkout, and order history
├── includes/            # Core PHP utility components (auth, db connect, header/footer)
├── database.sql         # Original raw SQL database schema
├── index.php            # Main E-Commerce Landing Page
├── login.php            # Combined authentication login page
├── logout.php           # Session destruction handler
├── register.php         # Customer account creation script
└── setup.php            # Database creation & data seeding wizard
```
