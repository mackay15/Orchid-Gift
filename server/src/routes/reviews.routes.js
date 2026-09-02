const express = require('express');
const pool    = require('../db/pool');
const { requireAuth } = require('../middleware/auth.middleware');
const { requireRole } = require('../middleware/role.middleware');

const router = express.Router();

// GET /api/reviews/product/:product_id — Public: approved reviews only
router.get('/product/:product_id', async (req, res, next) => {
  try {
    const result = await pool.query(
      `SELECT r.*, u.full_name FROM reviews r
       JOIN users u ON r.customer_id = u.user_id
       WHERE r.product_id = $1 AND r.status = 'Approved' ORDER BY r.created_at DESC`,
      [req.params.product_id]
    );
    res.json(result.rows);
  } catch (err) { next(err); }
});

// GET /api/reviews — Admin: all reviews with filter
router.get('/', requireAuth, requireRole('admin'), async (req, res, next) => {
  try {
    const { status } = req.query;
    const params = [];
    let query = `SELECT r.*, u.full_name AS customer_name, p.name AS product_name FROM reviews r
                 JOIN users u ON r.customer_id = u.user_id JOIN products p ON r.product_id = p.product_id WHERE 1=1`;
    if (status) { params.push(status); query += ` AND r.status = $${params.length}`; }
    query += ' ORDER BY r.created_at DESC';
    const result = await pool.query(query, params);
    res.json(result.rows);
  } catch (err) { next(err); }
});

// POST /api/reviews — Customer submits review
router.post('/', requireAuth, requireRole('customer'), async (req, res, next) => {
  try {
    const { product_id, rating, review } = req.body;
    // Only allow review if customer has a completed order for this product
    const purchased = await pool.query(
      `SELECT 1 FROM order_items oi JOIN orders o ON oi.order_id = o.order_id
       WHERE o.customer_id=$1 AND oi.product_id=$2 AND o.order_status='Completed' LIMIT 1`,
      [req.user.user_id, product_id]
    );
    if (!purchased.rows[0]) {
      return res.status(403).json({ error: 'You can only review products from completed orders.' });
    }
    const result = await pool.query(
      `INSERT INTO reviews (product_id, customer_id, rating, review) VALUES ($1,$2,$3,$4) RETURNING *`,
      [product_id, req.user.user_id, rating, review]
    );
    res.status(201).json(result.rows[0]);
  } catch (err) { next(err); }
});

// PATCH /api/reviews/:id/status — Admin moderates
router.patch('/:id/status', requireAuth, requireRole('admin'), async (req, res, next) => {
  try {
    const { status } = req.body; // 'Approved' | 'Rejected'
    const result = await pool.query(
      'UPDATE reviews SET status=$1 WHERE review_id=$2 RETURNING *',
      [status, req.params.id]
    );
    if (!result.rows[0]) return res.status(404).json({ error: 'Review not found.' });
    res.json(result.rows[0]);
  } catch (err) { next(err); }
});

module.exports = router;
