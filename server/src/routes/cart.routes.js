// Cart is managed client-side (localStorage) for speed.
// This route provides server-side cart validation before checkout.
const express = require('express');
const pool    = require('../db/pool');
const { requireAuth } = require('../middleware/auth.middleware');

const router = express.Router();

// POST /api/cart/validate
// Validates a cart payload against current stock and prices
router.post('/validate', requireAuth, async (req, res, next) => {
  try {
    const { items } = req.body; // [{ product_id, quantity }]
    if (!items || !items.length) {
      return res.status(400).json({ error: 'Cart is empty.' });
    }

    const validated = [];
    const warnings  = [];

    for (const item of items) {
      const result = await pool.query(
        'SELECT product_id, name, price, stock_quantity, image_url FROM products WHERE product_id = $1',
        [item.product_id]
      );
      const product = result.rows[0];
      if (!product) {
        warnings.push(`Product ID ${item.product_id} no longer exists and was removed.`);
        continue;
      }
      if (product.stock_quantity === 0) {
        warnings.push(`"${product.name}" is out of stock and was removed.`);
        continue;
      }
      const qty = Math.min(item.quantity, product.stock_quantity);
      if (qty < item.quantity) {
        warnings.push(`"${product.name}" quantity reduced to ${qty} (max available).`);
      }
      validated.push({ ...product, quantity: qty, line_total: (product.price * qty).toFixed(2) });
    }

    const total = validated.reduce((sum, i) => sum + parseFloat(i.line_total), 0);
    res.json({ items: validated, total: total.toFixed(2), warnings });
  } catch (err) {
    next(err);
  }
});

module.exports = router;
