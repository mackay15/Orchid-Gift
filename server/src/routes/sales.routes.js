const express = require('express');
const pool    = require('../db/pool');
const { requireAuth } = require('../middleware/auth.middleware');
const { requireRole } = require('../middleware/role.middleware');

const router = express.Router();

// GET /api/sales — Sales records + analytics
router.get('/', requireAuth, requireRole('admin', 'cashier'), async (req, res, next) => {
  try {
    const result = await pool.query(
      `SELECT s.*, o.order_type, u.full_name AS cashier_name
       FROM sales s JOIN orders o ON s.order_id=o.order_id
       LEFT JOIN users u ON o.cashier_id=u.user_id
       ORDER BY s.created_at DESC`
    );
    res.json(result.rows);
  } catch (err) { next(err); }
});

// GET /api/sales/chart — Last 7 days daily totals (for dashboard chart)
router.get('/chart', requireAuth, requireRole('admin'), async (_req, res, next) => {
  try {
    const result = await pool.query(`
      SELECT TO_CHAR(created_at, 'Mon DD') AS label,
             SUM(total_amount)::FLOAT       AS total
      FROM sales
      WHERE created_at >= NOW() - INTERVAL '7 days'
      GROUP BY DATE_TRUNC('day', created_at), TO_CHAR(created_at, 'Mon DD')
      ORDER BY DATE_TRUNC('day', created_at) ASC
    `);
    res.json(result.rows);
  } catch (err) { next(err); }
});

// GET /api/sales/summary — Dashboard stat cards
router.get('/summary', requireAuth, requireRole('admin'), async (_req, res, next) => {
  try {
    const [grossSales, totalOrders, lowStock, pendingReviews, totalUsers] = await Promise.all([
      pool.query(`SELECT COALESCE(SUM(total_amount),0)::FLOAT AS value FROM orders WHERE payment_status='Paid'`),
      pool.query(`SELECT COUNT(*)::INT AS value FROM orders`),
      pool.query(`SELECT COUNT(*)::INT AS value FROM products WHERE stock_quantity <= 3`),
      pool.query(`SELECT COUNT(*)::INT AS value FROM reviews WHERE status='Pending'`),
      pool.query(`SELECT COUNT(*)::INT AS value FROM users WHERE role='customer'`),
    ]);
    res.json({
      gross_sales:     grossSales.rows[0].value,
      total_orders:    totalOrders.rows[0].value,
      low_stock:       lowStock.rows[0].value,
      pending_reviews: pendingReviews.rows[0].value,
      total_customers: totalUsers.rows[0].value,
    });
  } catch (err) { next(err); }
});

module.exports = router;
