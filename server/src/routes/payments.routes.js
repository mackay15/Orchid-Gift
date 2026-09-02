const express = require('express');
const axios   = require('axios');
const pool    = require('../db/pool');
const { requireAuth } = require('../middleware/auth.middleware');

const router = express.Router();

// POST /api/payments/verify — Verify Paystack transaction and mark order paid
router.post('/verify', requireAuth, async (req, res, next) => {
  try {
    const { reference, order_id } = req.body;
    if (!reference || !order_id) {
      return res.status(400).json({ error: 'Reference and order_id are required.' });
    }

    // Server-side verification with Paystack
    const paystackRes = await axios.get(
      `https://api.paystack.co/transaction/verify/${reference}`,
      { headers: { Authorization: `Bearer ${process.env.PAYSTACK_SECRET_KEY}` } }
    );

    const { status, data } = paystackRes.data;
    if (!status || data.status !== 'success') {
      return res.status(400).json({ error: 'Payment verification failed.' });
    }

    // Cross-check amount (Paystack amounts are in kobo/pesewas × 100)
    const orderResult = await pool.query('SELECT * FROM orders WHERE order_id=$1', [order_id]);
    const order = orderResult.rows[0];
    if (!order) return res.status(404).json({ error: 'Order not found.' });

    const expectedAmount = Math.round(parseFloat(order.total_amount) * 100);
    if (data.amount !== expectedAmount) {
      return res.status(400).json({ error: 'Amount mismatch. Payment rejected.' });
    }

    // Update order and payment status
    await pool.query(
      `UPDATE orders SET payment_status='Paid', order_status='Processing' WHERE order_id=$1`,
      [order_id]
    );
    await pool.query(
      `UPDATE payments SET payment_status='Completed' WHERE order_id=$1`,
      [order_id]
    );

    // Record sale
    await pool.query(
      `INSERT INTO sales (order_id, total_amount, payment_method, transaction_id)
       VALUES ($1,$2,$3,$4) ON CONFLICT DO NOTHING`,
      [order_id, order.total_amount, data.channel === 'mobile_money' ? 'Mobile Money' : 'Card', reference]
    );

    // Notify customer
    await pool.query(
      `INSERT INTO notifications (user_id, message) VALUES ($1,$2)`,
      [req.user.user_id, `Your order #${order_id} payment was successful! We're processing it now.`]
    );

    res.json({ message: 'Payment verified and order confirmed.', order_id });
  } catch (err) {
    next(err);
  }
});

module.exports = router;
