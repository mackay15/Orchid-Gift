require('dotenv').config();
const express = require('express');
const cors = require('cors');

// Route imports
const authRoutes         = require('./routes/auth.routes');
const productsRoutes     = require('./routes/products.routes');
const categoriesRoutes   = require('./routes/categories.routes');
const ordersRoutes       = require('./routes/orders.routes');
const cartRoutes         = require('./routes/cart.routes');
const wishlistRoutes     = require('./routes/wishlist.routes');
const reviewsRoutes      = require('./routes/reviews.routes');
const usersRoutes        = require('./routes/users.routes');
const cashierRoutes      = require('./routes/cashier.routes');
const salesRoutes        = require('./routes/sales.routes');
const notificationsRoutes= require('./routes/notifications.routes');
const paymentsRoutes     = require('./routes/payments.routes');

const errorHandler = require('./middleware/error.middleware');

const app = express();
const PORT = process.env.PORT || 5000;

// ─── Middleware ─────────────────────────────────────────────
app.use(cors({
  origin: process.env.CLIENT_URL || 'http://localhost:5173',
  credentials: true,
}));
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Serve uploaded files
app.use('/uploads', express.static('uploads'));

// ─── Health Check ───────────────────────────────────────────
app.get('/api/health', (_req, res) => {
  res.json({ status: 'ok', timestamp: new Date().toISOString() });
});

// ─── API Routes ─────────────────────────────────────────────
app.use('/api/auth',          authRoutes);
app.use('/api/products',      productsRoutes);
app.use('/api/categories',    categoriesRoutes);
app.use('/api/orders',        ordersRoutes);
app.use('/api/cart',          cartRoutes);
app.use('/api/wishlist',      wishlistRoutes);
app.use('/api/reviews',       reviewsRoutes);
app.use('/api/users',         usersRoutes);
app.use('/api/cashier',       cashierRoutes);
app.use('/api/sales',         salesRoutes);
app.use('/api/notifications', notificationsRoutes);
app.use('/api/payments',      paymentsRoutes);

// ─── 404 Handler ────────────────────────────────────────────
app.use((_req, res) => {
  res.status(404).json({ error: 'Route not found' });
});

// ─── Global Error Handler ───────────────────────────────────
app.use(errorHandler);

app.listen(PORT, () => {
  console.log(`🌸 Orchid Gift API running on http://localhost:${PORT}`);
});

module.exports = app;
