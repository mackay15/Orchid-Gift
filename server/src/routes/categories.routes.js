const express = require('express');
const pool    = require('../db/pool');
const { requireAuth } = require('../middleware/auth.middleware');
const { requireRole } = require('../middleware/role.middleware');

const router = express.Router();

// GET /api/categories
router.get('/', async (_req, res, next) => {
  try {
    const result = await pool.query('SELECT * FROM categories ORDER BY name ASC');
    res.json(result.rows);
  } catch (err) { next(err); }
});

// GET /api/categories/:id
router.get('/:id', async (req, res, next) => {
  try {
    const result = await pool.query('SELECT * FROM categories WHERE category_id = $1', [req.params.id]);
    if (!result.rows[0]) return res.status(404).json({ error: 'Category not found.' });
    res.json(result.rows[0]);
  } catch (err) { next(err); }
});

// POST /api/categories
router.post('/', requireAuth, requireRole('admin'), async (req, res, next) => {
  try {
    const { name, description } = req.body;
    const result = await pool.query(
      'INSERT INTO categories (name, description) VALUES ($1,$2) RETURNING *',
      [name, description]
    );
    res.status(201).json(result.rows[0]);
  } catch (err) { next(err); }
});

// PUT /api/categories/:id
router.put('/:id', requireAuth, requireRole('admin'), async (req, res, next) => {
  try {
    const { name, description } = req.body;
    const result = await pool.query(
      'UPDATE categories SET name=$1, description=$2 WHERE category_id=$3 RETURNING *',
      [name, description, req.params.id]
    );
    if (!result.rows[0]) return res.status(404).json({ error: 'Category not found.' });
    res.json(result.rows[0]);
  } catch (err) { next(err); }
});

// DELETE /api/categories/:id
router.delete('/:id', requireAuth, requireRole('admin'), async (req, res, next) => {
  try {
    await pool.query('DELETE FROM categories WHERE category_id = $1', [req.params.id]);
    res.json({ message: 'Category deleted.' });
  } catch (err) { next(err); }
});

module.exports = router;
