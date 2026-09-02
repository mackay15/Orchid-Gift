const express = require('express');
const pool    = require('../db/pool');
const { requireAuth } = require('../middleware/auth.middleware');
const { requireRole } = require('../middleware/role.middleware');

const router = express.Router();

// ── GET /api/products ───────────────────────────────────────
// Public — supports ?category_id=&search=&limit=&offset=
router.get('/', async (req, res, next) => {
  try {
    const { category_id, search, limit = 20, offset = 0 } = req.query;
    let query = `
      SELECT p.*, c.name AS category_name
      FROM products p
      JOIN categories c ON p.category_id = c.category_id
      WHERE 1=1
    `;
    const params = [];

    if (category_id) {
      params.push(category_id);
      query += ` AND p.category_id = $${params.length}`;
    }
    if (search) {
      params.push(`%${search}%`);
      query += ` AND (p.name ILIKE $${params.length} OR p.description ILIKE $${params.length})`;
    }

    query += ` ORDER BY p.created_at DESC`;
    params.push(limit);
    query += ` LIMIT $${params.length}`;
    params.push(offset);
    query += ` OFFSET $${params.length}`;

    const result = await pool.query(query, params);

    // Count total (without limit/offset)
    const countResult = await pool.query(
      `SELECT COUNT(*) FROM products p WHERE 1=1
       ${category_id ? `AND p.category_id = ${category_id}` : ''}
       ${search ? `AND (p.name ILIKE '%${search}%' OR p.description ILIKE '%${search}%')` : ''}`
    );

    res.json({ products: result.rows, total: parseInt(countResult.rows[0].count) });
  } catch (err) {
    next(err);
  }
});

// ── GET /api/products/low-stock ─────────────────────────────
router.get('/low-stock', requireAuth, requireRole('admin', 'cashier'), async (req, res, next) => {
  try {
    const result = await pool.query(
      `SELECT p.*, c.name AS category_name FROM products p
       JOIN categories c ON p.category_id = c.category_id
       WHERE p.stock_quantity <= 3 ORDER BY p.stock_quantity ASC`
    );
    res.json(result.rows);
  } catch (err) {
    next(err);
  }
});

// ── GET /api/products/:id ───────────────────────────────────
router.get('/:id', async (req, res, next) => {
  try {
    const result = await pool.query(
      `SELECT p.*, c.name AS category_name
       FROM products p JOIN categories c ON p.category_id = c.category_id
       WHERE p.product_id = $1`,
      [req.params.id]
    );
    if (!result.rows[0]) return res.status(404).json({ error: 'Product not found.' });

    // Include approved reviews
    const reviews = await pool.query(
      `SELECT r.*, u.full_name FROM reviews r
       JOIN users u ON r.customer_id = u.user_id
       WHERE r.product_id = $1 AND r.status = 'Approved' ORDER BY r.created_at DESC`,
      [req.params.id]
    );

    res.json({ ...result.rows[0], reviews: reviews.rows });
  } catch (err) {
    next(err);
  }
});

// ── POST /api/products ──────────────────────────────────────
router.post('/', requireAuth, requireRole('admin'), async (req, res, next) => {
  try {
    const { category_id, name, description, price, stock_quantity, image_url } = req.body;
    const result = await pool.query(
      `INSERT INTO products (category_id, name, description, price, stock_quantity, image_url)
       VALUES ($1,$2,$3,$4,$5,$6) RETURNING *`,
      [category_id, name, description, price, stock_quantity, image_url]
    );
    res.status(201).json(result.rows[0]);
  } catch (err) {
    next(err);
  }
});

// ── PUT /api/products/:id ───────────────────────────────────
router.put('/:id', requireAuth, requireRole('admin'), async (req, res, next) => {
  try {
    const { category_id, name, description, price, stock_quantity, image_url } = req.body;
    const result = await pool.query(
      `UPDATE products SET category_id=$1, name=$2, description=$3, price=$4,
       stock_quantity=$5, image_url=$6 WHERE product_id=$7 RETURNING *`,
      [category_id, name, description, price, stock_quantity, image_url, req.params.id]
    );
    if (!result.rows[0]) return res.status(404).json({ error: 'Product not found.' });
    res.json(result.rows[0]);
  } catch (err) {
    next(err);
  }
});

// ── DELETE /api/products/:id ────────────────────────────────
router.delete('/:id', requireAuth, requireRole('admin'), async (req, res, next) => {
  try {
    await pool.query('DELETE FROM products WHERE product_id = $1', [req.params.id]);
    res.json({ message: 'Product deleted.' });
  } catch (err) {
    next(err);
  }
});

module.exports = router;
