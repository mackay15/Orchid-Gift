const express = require('express');
const pool    = require('../db/pool');
const { requireAuth } = require('../middleware/auth.middleware');
const { requireRole } = require('../middleware/role.middleware');

const router = express.Router();

// POST /api/cashier/order — Create walk-in POS order
router.post('/order', requireAuth, requireRole('cashier', 'admin'), async (req, res, next) => {
  const client = await pool.connect();
  try {
    await client.query('BEGIN');
    const { items, payment_method, customer_id } = req.body;

    if (!items || !items.length) {
      return res.status(400).json({ error: 'Order must contain at least one item.' });
    }

    let total = 0;
    const enriched = [];
    for (const item of items) {
      const p = await client.query('SELECT * FROM products WHERE product_id=$1 FOR UPDATE', [item.product_id]);
      if (!p.rows[0]) throw { status: 400, message: `Product ${item.product_id} not found.` };
      if (p.rows[0].stock_quantity < item.quantity) {
        throw { status: 400, message: `Insufficient stock for "${p.rows[0].name}".` };
      }
      const lineTotal = parseFloat(p.rows[0].price) * item.quantity;
      total += lineTotal;
      enriched.push({ ...item, unit_price: p.rows[0].price, line_total: lineTotal });
    }

    const orderResult = await client.query(
      `INSERT INTO orders (customer_id, cashier_id, order_type, total_amount, order_status, payment_status)
       VALUES ($1,$2,'walk-in',$3,'Completed','Paid') RETURNING *`,
      [customer_id || null, req.user.user_id, total.toFixed(2)]
    );
    const order = orderResult.rows[0];

    for (const item of enriched) {
      await client.query(
        `INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) VALUES ($1,$2,$3,$4,$5)`,
        [order.order_id, item.product_id, item.quantity, item.unit_price, item.line_total]
      );
      await client.query(
        'UPDATE products SET stock_quantity = stock_quantity - $1 WHERE product_id=$2',
        [item.quantity, item.product_id]
      );
    }

    // Record sale
    const txnId = `TXN${Date.now()}`;
    await client.query(
      `INSERT INTO sales (order_id, total_amount, payment_method, transaction_id) VALUES ($1,$2,$3,$4)`,
      [order.order_id, total.toFixed(2), payment_method || 'Cash', txnId]
    );
    await client.query(
      `INSERT INTO payments (order_id, amount, payment_method, payment_status) VALUES ($1,$2,$3,'Completed')`,
      [order.order_id, total.toFixed(2), payment_method || 'Cash']
    );

    // Log cashier action
    await client.query(
      `INSERT INTO cashier_logs (cashier_id, action, details) VALUES ($1,'CREATE_WALKIN_ORDER',$2)`,
      [req.user.user_id, `Order #${order.order_id} — Total: GH₵${total.toFixed(2)}`]
    );

    await client.query('COMMIT');

    // Return full order with items for receipt
    const items_result = await pool.query(
      `SELECT oi.*, p.name, p.image_url FROM order_items oi
       JOIN products p ON oi.product_id=p.product_id WHERE oi.order_id=$1`,
      [order.order_id]
    );
    res.status(201).json({ ...order, items: items_result.rows, transaction_id: txnId });
  } catch (err) {
    await client.query('ROLLBACK');
    next(err);
  } finally {
    client.release();
  }
});

// GET /api/cashier/logs
router.get('/logs', requireAuth, requireRole('cashier', 'admin'), async (req, res, next) => {
  try {
    const cashier_id = req.user.role === 'cashier' ? req.user.user_id : req.query.cashier_id;
    const params = cashier_id ? [cashier_id] : [];
    const result = await pool.query(
      `SELECT l.*, u.full_name AS cashier_name FROM cashier_logs l
       JOIN users u ON l.cashier_id=u.user_id
       ${cashier_id ? 'WHERE l.cashier_id=$1' : ''}
       ORDER BY l.created_at DESC LIMIT 100`,
      params
    );
    res.json(result.rows);
  } catch (err) { next(err); }
});

// GET /api/cashier/customers — Customers list for POS lookup
router.get('/customers', requireAuth, requireRole('cashier', 'admin'), async (req, res, next) => {
  try {
    const { search } = req.query;
    let query = `SELECT user_id, username, full_name, email FROM users WHERE role='customer'`;
    const params = [];
    if (search) {
      params.push(`%${search}%`);
      query += ` AND (full_name ILIKE $1 OR email ILIKE $1 OR username ILIKE $1)`;
    }
    query += ' ORDER BY full_name ASC LIMIT 20';
    const result = await pool.query(query, params);
    res.json(result.rows);
  } catch (err) { next(err); }
});

module.exports = router;
