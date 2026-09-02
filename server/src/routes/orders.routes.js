const express = require('express');
const pool    = require('../db/pool');
const { requireAuth } = require('../middleware/auth.middleware');
const { requireRole } = require('../middleware/role.middleware');

const router = express.Router();

// GET /api/orders — List orders (admin sees all, customer sees own)
router.get('/', requireAuth, async (req, res, next) => {
  try {
    let query, params;
    if (req.user.role === 'admin' || req.user.role === 'cashier') {
      query  = `SELECT o.*, u.full_name AS customer_name FROM orders o
                LEFT JOIN users u ON o.customer_id = u.user_id ORDER BY o.created_at DESC`;
      params = [];
    } else {
      query  = `SELECT o.* FROM orders o WHERE o.customer_id = $1 ORDER BY o.created_at DESC`;
      params = [req.user.user_id];
    }
    const result = await pool.query(query, params);
    res.json(result.rows);
  } catch (err) { next(err); }
});

// GET /api/orders/:id — Order detail with items
router.get('/:id', requireAuth, async (req, res, next) => {
  try {
    const orderResult = await pool.query(
      `SELECT o.*, u.full_name AS customer_name FROM orders o
       LEFT JOIN users u ON o.customer_id = u.user_id WHERE o.order_id = $1`,
      [req.params.id]
    );
    const order = orderResult.rows[0];
    if (!order) return res.status(404).json({ error: 'Order not found.' });

    // Only owner, cashier or admin can view
    if (req.user.role === 'customer' && order.customer_id !== req.user.user_id) {
      return res.status(403).json({ error: 'Forbidden.' });
    }

    const items = await pool.query(
      `SELECT oi.*, p.name, p.image_url FROM order_items oi
       JOIN products p ON oi.product_id = p.product_id WHERE oi.order_id = $1`,
      [req.params.id]
    );

    res.json({ ...order, items: items.rows });
  } catch (err) { next(err); }
});

// POST /api/orders — Place a new order
router.post('/', requireAuth, async (req, res, next) => {
  const client = await pool.connect();
  try {
    await client.query('BEGIN');
    const { items, payment_method, order_type = 'online' } = req.body;
    // items = [{ product_id, quantity }]

    if (!items || !items.length) {
      return res.status(400).json({ error: 'Order must contain at least one item.' });
    }

    // Validate stock and calculate total
    let total = 0;
    const enriched = [];
    for (const item of items) {
      const p = await client.query('SELECT * FROM products WHERE product_id = $1 FOR UPDATE', [item.product_id]);
      if (!p.rows[0]) throw { status: 400, message: `Product ${item.product_id} not found.` };
      if (p.rows[0].stock_quantity < item.quantity) {
        throw { status: 400, message: `Insufficient stock for "${p.rows[0].name}".` };
      }
      const lineTotal = parseFloat(p.rows[0].price) * item.quantity;
      total += lineTotal;
      enriched.push({ ...item, unit_price: p.rows[0].price, line_total: lineTotal, product: p.rows[0] });
    }

    // Create order
    const orderResult = await client.query(
      `INSERT INTO orders (customer_id, order_type, total_amount, order_status, payment_status)
       VALUES ($1,$2,$3,'Pending','Unpaid') RETURNING *`,
      [req.user.user_id, order_type, total.toFixed(2)]
    );
    const order = orderResult.rows[0];

    // Insert items & reduce stock
    for (const item of enriched) {
      await client.query(
        `INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) VALUES ($1,$2,$3,$4,$5)`,
        [order.order_id, item.product_id, item.quantity, item.unit_price, item.line_total]
      );
      await client.query(
        'UPDATE products SET stock_quantity = stock_quantity - $1 WHERE product_id = $2',
        [item.quantity, item.product_id]
      );
    }

    // Create payment record
    await client.query(
      `INSERT INTO payments (order_id, amount, payment_method, payment_status) VALUES ($1,$2,$3,'Pending')`,
      [order.order_id, total.toFixed(2), payment_method || 'Cash']
    );

    await client.query('COMMIT');
    res.status(201).json(order);
  } catch (err) {
    await client.query('ROLLBACK');
    next(err);
  } finally {
    client.release();
  }
});

// PATCH /api/orders/:id/status — Admin/Cashier update status
router.patch('/:id/status', requireAuth, requireRole('admin', 'cashier'), async (req, res, next) => {
  try {
    const { order_status, payment_status } = req.body;
    const fields = [];
    const params = [];
    if (order_status)   { params.push(order_status);   fields.push(`order_status=$${params.length}`); }
    if (payment_status) { params.push(payment_status); fields.push(`payment_status=$${params.length}`); }
    if (!fields.length) return res.status(400).json({ error: 'No fields to update.' });

    params.push(req.params.id);
    const result = await pool.query(
      `UPDATE orders SET ${fields.join(',')} WHERE order_id=$${params.length} RETURNING *`,
      params
    );
    if (!result.rows[0]) return res.status(404).json({ error: 'Order not found.' });
    res.json(result.rows[0]);
  } catch (err) { next(err); }
});

module.exports = router;
