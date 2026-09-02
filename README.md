# Orchid Gift & More Management System

A modern, glassmorphic E-Commerce and POS management system designed for boutique florists, custom gift shops, and premium bakeries. Built with **React 18, Tailwind CSS, Node.js (Express), PostgreSQL, and Docker Compose**.

---

## ✨ Key Features & MVP Modules

The application is structured around three distinct user roles, each tailored to streamline specific areas of business operations:

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
- **Inventory Control:** Full CRUD operations for Categories and Products (manage names, descriptions, pricing, image URLs, and stock levels).
- **Order Processing:** Update order fulfillment stages (Pending, Processing, Completed, Cancelled) and payment statuses.
- **User Management:** Monitor, activate, and deactivate registered users (Customers, Cashiers, Admins).
- **Review Moderation:** Approve or reject customer reviews before they display on the public storefront.
- **Sales Analytics Reports:** Data tables documenting transaction history, payment breakdown, and total revenue.

---

## 🗄️ Database Schema & Architecture

The database consists of 10 interconnected tables designed to support both online retail and point of sales:

1. **`users`**: Manages credentials, contact info, status (Active/Inactive), and access roles (`customer`, `cashier`, `admin`).
2. **`categories`**: Grouping for gifts (e.g., Flowers, Cakes, Chocolate, Perfumes, Hampers).
3. **`products`**: Product inventory containing prices, descriptions, image URLs, and stock levels.
4. **`orders`**: Tracks individual orders, marking them as online or walk-in (POS) along with order and payment status.
5. **`order_items`**: Line-by-line item details for each order, preserving historical pricing.
6. **`reviews`**: Customer reviews containing star ratings and textual feedback with admin moderation states (`Pending`, `Approved`, `Rejected`).
7. **`wishlists`**: Saved products list curated by online customers.
8. **`sales`**: Financial records of completed orders, storing payment methods and transaction IDs.
9. **`payments`**: Payment processing records connected to order fulfillment.
10. **`notifications`**: User-specific notification logs to alert on orders and system updates.

---

## 🚀 Architecture & Tech Stack

- **Frontend:** React 18, Vite, Tailwind CSS v3 (custom glassmorphism design system)
- **Backend:** Node.js, Express REST API with JWT authentication & role-based middleware
- **Database:** PostgreSQL 16
- **Containerization:** Docker Compose

---

## 📦 Getting Started with Docker

```bash
# Start all services (PostgreSQL, Express Server, Vite React Client, pgAdmin)
docker compose up --build -d
```

### Endpoints:
- **React Frontend:** http://localhost:5173
- **Node.js REST API:** http://localhost:5000/api
- **pgAdmin DB GUI:** http://localhost:5050 (login: `admin@orchid.com` / `admin123`)

---

## 🔑 Demo Seed Accounts

| Role | Username | Password |
|---|---|---|
| **Admin** | `admin` | `admin123` |
| **Cashier** | `cashier` | `cashier123` |
| **Customer** | `customer` | `customer123` |

