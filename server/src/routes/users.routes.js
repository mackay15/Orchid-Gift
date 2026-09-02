const express = require('express');
const bcrypt  = require('bcryptjs');
const pool    = require('../db/pool');
const { requireAuth } = require('../middleware/auth.middleware');
const { requireRole } = require('../middleware/role.middleware');

const router = express.Router();

// POST /api/users — Admin create new user
router.post('/', requireAuth, requireRole('admin'), async (req, res, next) => {
  try {
    const { username, email, password, full_name, role = 'customer', status = 'Active' } = req.body;
    if (!username || !email || !password || !full_name) {
      return res.status(400).json({ error: 'Username, email, password, and full name are required.' });
    }

    if (!['customer', 'cashier', 'admin'].includes(role)) {
      return res.status(400).json({ error: 'Invalid role. Allowed roles: customer, cashier, admin.' });
    }

    const existing = await pool.query(
      'SELECT user_id FROM users WHERE username = $1 OR email = $2',
      [username.trim(), email.trim()]
    );
    if (existing.rows.length > 0) {
      return res.status(409).json({ error: 'Username or email already exists.' });
    }

    const hashed = await bcrypt.hash(password, 10);
    const result = await pool.query(
      `INSERT INTO users (username, email, password, full_name, role, status)
       VALUES ($1, $2, $3, $4, $5, $6)
       RETURNING user_id, username, email, full_name, role, status, created_at`,
      [username.trim(), email.trim().toLowerCase(), hashed, full_name.trim(), role, status]
    );

    res.status(201).json(result.rows[0]);
  } catch (err) {
    next(err);
  }
});

// GET /api/users — Admin only
router.get('/', requireAuth, requireRole('admin'), async (_req, res, next) => {
  try {
    const result = await pool.query(
      'SELECT user_id, username, email, full_name, role, status, created_at FROM users ORDER BY created_at DESC'
    );
    res.json(result.rows);
  } catch (err) { next(err); }
});

// GET /api/users/:id
router.get('/:id', requireAuth, requireRole('admin'), async (req, res, next) => {
  try {
    const result = await pool.query(
      'SELECT user_id, username, email, full_name, role, status, created_at FROM users WHERE user_id=$1',
      [req.params.id]
    );
    if (!result.rows[0]) return res.status(404).json({ error: 'User not found.' });
    res.json(result.rows[0]);
  } catch (err) { next(err); }
});

// PATCH /api/users/:id/status — Toggle Active/Inactive
router.patch('/:id/status', requireAuth, requireRole('admin'), async (req, res, next) => {
  try {
    const { status } = req.body;
    const result = await pool.query(
      'UPDATE users SET status=$1 WHERE user_id=$2 RETURNING user_id, username, email, role, status',
      [status, req.params.id]
    );
    if (!result.rows[0]) return res.status(404).json({ error: 'User not found.' });
    res.json(result.rows[0]);
  } catch (err) { next(err); }
});

// PATCH /api/users/:id/role — Admin assign user role (e.g. cashier, customer, admin)
router.patch('/:id/role', requireAuth, requireRole('admin'), async (req, res, next) => {
  try {
    const { role } = req.body;
    if (!['customer', 'cashier', 'admin'].includes(role)) {
      return res.status(400).json({ error: 'Invalid role. Allowed roles: customer, cashier, admin.' });
    }
    const result = await pool.query(
      'UPDATE users SET role=$1 WHERE user_id=$2 RETURNING user_id, username, email, full_name, role, status',
      [role, req.params.id]
    );
    if (!result.rows[0]) return res.status(404).json({ error: 'User not found.' });
    res.json(result.rows[0]);
  } catch (err) { next(err); }
});

module.exports = router;
