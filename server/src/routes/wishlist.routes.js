const express = require('express');
const pool    = require('../db/pool');
const { requireAuth } = require('../middleware/auth.middleware');

const router = express.Router();

// GET /api/wishlist
router.get('/', requireAuth, async (req, res, next) => {
  try {
    const result = await pool.query(
      `SELECT w.wishlist_id, w.created_at, p.product_id, p.name, p.price, p.image_url, p.stock_quantity
       FROM wishlists w JOIN products p ON w.product_id = p.product_id
       WHERE w.customer_id = $1 ORDER BY w.created_at DESC`,
      [req.user.user_id]
    );
    res.json(result.rows);
  } catch (err) { next(err); }
});

// POST /api/wishlist/:product_id — Toggle (add if not present, remove if present)
router.post('/:product_id', requireAuth, async (req, res, next) => {
  try {
    const existing = await pool.query(
      'SELECT wishlist_id FROM wishlists WHERE customer_id=$1 AND product_id=$2',
      [req.user.user_id, req.params.product_id]
    );
    if (existing.rows[0]) {
      await pool.query('DELETE FROM wishlists WHERE wishlist_id=$1', [existing.rows[0].wishlist_id]);
      return res.json({ action: 'removed' });
    }
    await pool.query(
      'INSERT INTO wishlists (customer_id, product_id) VALUES ($1,$2)',
      [req.user.user_id, req.params.product_id]
    );
    res.status(201).json({ action: 'added' });
  } catch (err) { next(err); }
});

// DELETE /api/wishlist/:product_id
router.delete('/:product_id', requireAuth, async (req, res, next) => {
  try {
    await pool.query(
      'DELETE FROM wishlists WHERE customer_id=$1 AND product_id=$2',
      [req.user.user_id, req.params.product_id]
    );
    res.json({ message: 'Removed from wishlist.' });
  } catch (err) { next(err); }
});

module.exports = router;
