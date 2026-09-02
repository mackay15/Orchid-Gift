-- ============================================================
-- ORCHID GIFT & MORE — PostgreSQL Database Schema
-- Migrated from MySQL (orchid_db) → PostgreSQL 16
-- ============================================================

-- ============================================================
-- EXTENSIONS
-- ============================================================
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- ============================================================
-- CUSTOM ENUM TYPES
-- ============================================================
DO $$ BEGIN
  CREATE TYPE user_role AS ENUM ('customer', 'cashier', 'admin');
EXCEPTION WHEN duplicate_object THEN null; END $$;

DO $$ BEGIN
  CREATE TYPE user_status AS ENUM ('Active', 'Inactive');
EXCEPTION WHEN duplicate_object THEN null; END $$;

DO $$ BEGIN
  CREATE TYPE order_type AS ENUM ('online', 'walk-in');
EXCEPTION WHEN duplicate_object THEN null; END $$;

DO $$ BEGIN
  CREATE TYPE order_status AS ENUM ('Pending', 'Processing', 'Completed', 'Cancelled');
EXCEPTION WHEN duplicate_object THEN null; END $$;

DO $$ BEGIN
  CREATE TYPE payment_status AS ENUM ('Unpaid', 'Paid');
EXCEPTION WHEN duplicate_object THEN null; END $$;

DO $$ BEGIN
  CREATE TYPE payment_proc_status AS ENUM ('Pending', 'Completed', 'Failed');
EXCEPTION WHEN duplicate_object THEN null; END $$;

DO $$ BEGIN
  CREATE TYPE payment_method AS ENUM ('Cash', 'Card', 'Mobile Money');
EXCEPTION WHEN duplicate_object THEN null; END $$;

DO $$ BEGIN
  CREATE TYPE review_status AS ENUM ('Pending', 'Approved', 'Rejected');
EXCEPTION WHEN duplicate_object THEN null; END $$;

-- ============================================================
-- TABLE STRUCTURES
-- ============================================================

-- USERS
CREATE TABLE IF NOT EXISTS users (
  user_id     SERIAL PRIMARY KEY,
  username    VARCHAR(50)  UNIQUE NOT NULL,
  password    VARCHAR(255) NOT NULL,
  email       VARCHAR(100) UNIQUE NOT NULL,
  full_name   VARCHAR(100) NOT NULL,
  role        user_role    NOT NULL DEFAULT 'customer',
  status      user_status  NOT NULL DEFAULT 'Active',
  google_id   VARCHAR(255) UNIQUE DEFAULT NULL,
  created_at  TIMESTAMPTZ  NOT NULL DEFAULT NOW()
);

-- CATEGORIES
CREATE TABLE IF NOT EXISTS categories (
  category_id SERIAL PRIMARY KEY,
  name        VARCHAR(100) UNIQUE NOT NULL,
  description TEXT,
  created_at  TIMESTAMPTZ  NOT NULL DEFAULT NOW()
);

-- PRODUCTS
CREATE TABLE IF NOT EXISTS products (
  product_id      SERIAL PRIMARY KEY,
  category_id     INT          NOT NULL REFERENCES categories(category_id) ON DELETE CASCADE,
  name            VARCHAR(150) NOT NULL,
  description     TEXT,
  price           NUMERIC(10,2) NOT NULL,
  stock_quantity  INT           NOT NULL DEFAULT 0,
  image_url       VARCHAR(255)  DEFAULT NULL,
  created_at      TIMESTAMPTZ   NOT NULL DEFAULT NOW()
);

-- ORDERS
CREATE TABLE IF NOT EXISTS orders (
  order_id       SERIAL PRIMARY KEY,
  customer_id    INT             DEFAULT NULL REFERENCES users(user_id) ON DELETE SET NULL,
  cashier_id     INT             DEFAULT NULL REFERENCES users(user_id) ON DELETE SET NULL,
  order_type     order_type      NOT NULL,
  total_amount   NUMERIC(10,2)   NOT NULL,
  order_status   order_status    NOT NULL DEFAULT 'Pending',
  payment_status payment_status  NOT NULL DEFAULT 'Unpaid',
  created_at     TIMESTAMPTZ     NOT NULL DEFAULT NOW()
);

-- ORDER_ITEMS
CREATE TABLE IF NOT EXISTS order_items (
  item_id     SERIAL PRIMARY KEY,
  order_id    INT           NOT NULL REFERENCES orders(order_id) ON DELETE CASCADE,
  product_id  INT           NOT NULL REFERENCES products(product_id) ON DELETE CASCADE,
  quantity    INT           NOT NULL,
  unit_price  NUMERIC(10,2) NOT NULL,
  total_price NUMERIC(10,2) NOT NULL
);

-- REVIEWS
CREATE TABLE IF NOT EXISTS reviews (
  review_id   SERIAL PRIMARY KEY,
  product_id  INT           NOT NULL REFERENCES products(product_id) ON DELETE CASCADE,
  customer_id INT           NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
  rating      SMALLINT      NOT NULL CHECK (rating BETWEEN 1 AND 5),
  review      TEXT,
  status      review_status NOT NULL DEFAULT 'Pending',
  created_at  TIMESTAMPTZ   NOT NULL DEFAULT NOW()
);

-- WISHLISTS
CREATE TABLE IF NOT EXISTS wishlists (
  wishlist_id SERIAL PRIMARY KEY,
  customer_id INT         NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
  product_id  INT         NOT NULL REFERENCES products(product_id) ON DELETE CASCADE,
  created_at  TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  UNIQUE (customer_id, product_id)
);

-- SALES
CREATE TABLE IF NOT EXISTS sales (
  sales_id       SERIAL PRIMARY KEY,
  order_id       INT            NOT NULL REFERENCES orders(order_id) ON DELETE CASCADE,
  total_amount   NUMERIC(10,2)  NOT NULL,
  payment_method payment_method NOT NULL,
  transaction_id VARCHAR(100)   DEFAULT NULL,
  created_at     TIMESTAMPTZ    NOT NULL DEFAULT NOW()
);

-- PAYMENTS
CREATE TABLE IF NOT EXISTS payments (
  payment_id      SERIAL PRIMARY KEY,
  order_id        INT                  NOT NULL REFERENCES orders(order_id) ON DELETE CASCADE,
  amount          NUMERIC(10,2)        NOT NULL,
  payment_method  VARCHAR(50)          NOT NULL,
  payment_status  payment_proc_status  NOT NULL DEFAULT 'Pending',
  created_at      TIMESTAMPTZ          NOT NULL DEFAULT NOW()
);

-- NOTIFICATIONS
CREATE TABLE IF NOT EXISTS notifications (
  notification_id SERIAL PRIMARY KEY,
  user_id         INT         NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
  message         TEXT        NOT NULL,
  is_read         BOOLEAN     NOT NULL DEFAULT FALSE,
  created_at      TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- CASHIER_LOGS
CREATE TABLE IF NOT EXISTS cashier_logs (
  log_id     SERIAL PRIMARY KEY,
  cashier_id INT         NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
  action     VARCHAR(100) NOT NULL,
  details    TEXT,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- ============================================================
-- INDEXES
-- ============================================================
CREATE INDEX IF NOT EXISTS idx_products_category ON products(category_id);
CREATE INDEX IF NOT EXISTS idx_orders_customer   ON orders(customer_id);
CREATE INDEX IF NOT EXISTS idx_orders_cashier    ON orders(cashier_id);
CREATE INDEX IF NOT EXISTS idx_order_items_order ON order_items(order_id);
CREATE INDEX IF NOT EXISTS idx_reviews_product   ON reviews(product_id);
CREATE INDEX IF NOT EXISTS idx_notifications_user ON notifications(user_id);

-- ============================================================
-- SEED DATA
-- ============================================================

-- Default Accounts (Passwords: username + '123', hashed with bcrypt cost 10)
-- admin123   → $2b$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
-- cashier123 → $2b$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi (placeholder — will be re-hashed at runtime)
-- We use the same placeholder hash here. The server's bcrypt compare will work with real hashes.

-- NOTE: Real bcrypt hashes for seeding:
-- 'admin123'    → $2b$10$5c9H5.K7bVOYpYdM4lO3du3nLO3TGvLfB9BW1OI4D0cR5lVQsHGfS (example)
-- In production, run: node -e "const b=require('bcryptjs');console.log(b.hashSync('admin123',10))"

INSERT INTO users (username, password, email, full_name, role, status) VALUES
  ('admin',    '$2a$10$iLcQ767KAbD8CrXfdHu2IOKML4a0K7oj5cD6B14GVXNmFr4pDGHOy', 'admin@orchid.com',    'Orchid Admin',      'admin',    'Active'),
  ('cashier',  '$2a$10$ld0hgj8Wm0ZlkNxIjpGJyu19zsUDPNzFFFcduHzaHeoxkIzlebPA6', 'cashier@orchid.com',  'Sarah Cashier',     'cashier',  'Active'),
  ('customer', '$2a$10$PKWWtWI4QNdCxU/1upGgUecigH9rJfyPDRejylL5BhWJ02qF4BzSe', 'customer@orchid.com', 'John Doe Customer', 'customer', 'Active')
ON CONFLICT (username) DO NOTHING;

-- Default Categories
INSERT INTO categories (category_id, name, description) OVERRIDING SYSTEM VALUE VALUES
  (1, 'Flowers',          'Fresh cut roses, mixed bouquets, floral baskets, and custom arrangements.'),
  (2, 'Chocolate Gifts',  'Imported artisanal chocolates, premium truffles, and sweet combination boxes.'),
  (3, 'Cakes',            'Freshly baked designer cakes, custom message cupcakes, and celebration treats.'),
  (4, 'Hampers',          'Curated luxury hampers containing selection of gourmet foods, wine, and keepsakes.'),
  (5, 'Customized Gifts', 'Personalized photo frames, engraved mugs, custom jewelry boxes, and keychains.'),
  (6, 'Perfumes',         'Luxurious original designer fragrances for men and women.'),
  (7, 'Jewelry',          'Elegant necklaces, sterling silver bracelets, earrings, and fashion statement pieces.'),
  (8, 'Greeting Cards',   'Beautiful pop-up greeting cards, handcrafted letters, and warm wishes cards.')
ON CONFLICT (name) DO NOTHING;

-- Sync category sequence
SELECT setval('categories_category_id_seq', (SELECT MAX(category_id) FROM categories));

-- Seed Premium Products
INSERT INTO products (category_id, name, description, price, stock_quantity, image_url) VALUES
  (1, 'Midnight Rose Bouquet',       'A stunning arrangement of 12 premium dark red roses wrapped in black silk paper with a lavender ribbon.',           45.00, 15, 'https://images.unsplash.com/photo-1561181286-d3fee7d55364?auto=format&fit=crop&w=600&q=80'),
  (1, 'Orchid Meadow Vase',          'Elegant violet and purple orchids in a premium glass bowl vase.',                                                    60.00,  8, 'https://images.unsplash.com/photo-1526047932273-341f2a7631f9?auto=format&fit=crop&w=600&q=80'),
  (2, 'Gourmet Gold Truffles',       'A premium golden box of 24 handcrafted Belgian dark and milk chocolate truffles.',                                   32.50, 25, 'https://images.unsplash.com/photo-1548907040-4d42b52145ca?auto=format&fit=crop&w=600&q=80'),
  (2, 'Chocolate Lover Hamper',      'Assorted Lindt bars, dark cocoa nibs, and chocolate-dipped almonds in a lavender basket.',                           45.00, 10, 'https://images.unsplash.com/photo-1511381939415-e44015466834?auto=format&fit=crop&w=600&q=80'),
  (3, 'Velvet Orchid Cake',          'A delicious 1kg Red Velvet cake layered with premium cream cheese frosting and edible flower decoration.',            35.00,  5, 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=600&q=80'),
  (4, 'Luxury Spa & Tea Hamper',     'Relaxation hamper with lavender essential oils, floral tea leaves, mug, and organic honey.',                         75.00, 12, 'https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=600&q=80'),
  (5, 'Custom Engraved Wooden Frame','Premium oak wood photo frame with personalized engraving at the bottom.',                                            20.00, 50, 'https://images.unsplash.com/photo-1534447677768-be436bb09401?auto=format&fit=crop&w=600&q=80'),
  (6, 'Orchid Noir Parfum',          'A deep floral woodsy signature parfum spray for women (100ml).',                                                     85.00, 10, 'https://images.unsplash.com/photo-1541643600914-78b084683601?auto=format&fit=crop&w=600&q=80'),
  (7, 'Sterling Silver Rose Pendant','Elegant handcrafted 925 sterling silver necklace with a rose pendant in a soft violet box.',                         55.00, 15, 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?auto=format&fit=crop&w=600&q=80'),
  (8, '3D Pop-Up Orchid Card',       'Laser-cut pop-up card displaying a beautiful blooming orchid garden.',                                                7.50, 40, 'https://images.unsplash.com/photo-1513151233558-d860c5398176?auto=format&fit=crop&w=600&q=80')
ON CONFLICT DO NOTHING;

-- Sample Reviews
INSERT INTO reviews (product_id, customer_id, rating, review, status) VALUES
  (1, 3, 5, 'Absolutely gorgeous! The roses were fresh and smelled amazing. Worth every penny.', 'Approved'),
  (1, 3, 4, 'Very nice presentation, but delivery was delayed by about 20 minutes.',             'Approved')
ON CONFLICT DO NOTHING;

-- Mock Orders
INSERT INTO orders (order_id, customer_id, order_type, total_amount, order_status, payment_status, created_at) OVERRIDING SYSTEM VALUE VALUES
  (1, 3, 'online', 45.00, 'Completed', 'Paid', NOW() - INTERVAL '3 days'),
  (2, 3, 'online', 45.00, 'Completed', 'Paid', NOW() - INTERVAL '2 days'),
  (3, 3, 'online', 45.00, 'Completed', 'Paid', NOW() - INTERVAL '1 day')
ON CONFLICT DO NOTHING;

SELECT setval('orders_order_id_seq', (SELECT MAX(order_id) FROM orders));

INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) VALUES
  (1, 1, 1, 45.00, 45.00),
  (2, 1, 1, 45.00, 45.00),
  (3, 1, 1, 45.00, 45.00)
ON CONFLICT DO NOTHING;

INSERT INTO payments (order_id, amount, payment_method, payment_status, created_at) VALUES
  (1, 45.00, 'Card', 'Completed', NOW() - INTERVAL '3 days'),
  (2, 45.00, 'Card', 'Completed', NOW() - INTERVAL '2 days'),
  (3, 45.00, 'Card', 'Completed', NOW() - INTERVAL '1 day')
ON CONFLICT DO NOTHING;

INSERT INTO sales (order_id, total_amount, payment_method, transaction_id, created_at) VALUES
  (1, 45.00, 'Card', 'TXN581290', NOW() - INTERVAL '3 days'),
  (2, 45.00, 'Card', 'TXN918342', NOW() - INTERVAL '2 days'),
  (3, 45.00, 'Card', 'TXN419357', NOW() - INTERVAL '1 day')
ON CONFLICT DO NOTHING;
