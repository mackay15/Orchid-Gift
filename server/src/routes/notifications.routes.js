const express = require('express');
const pool    = require('../db/pool');
const { requireAuth } = require('../middleware/auth.middleware');

const router = express.Router();

// GET /api/notifications
router.get('/', requireAuth, async (req, res, next) => {
  try {
    const result = await pool.query(
      'SELECT * FROM notifications WHERE user_id=$1 ORDER BY created_at DESC LIMIT 20',
      [req.user.user_id]
    );
    res.json(result.rows);
  } catch (err) { next(err); }
});

// PATCH /api/notifications/:id/read
router.patch('/:id/read', requireAuth, async (req, res, next) => {
  try {
    await pool.query(
      'UPDATE notifications SET is_read=TRUE WHERE notification_id=$1 AND user_id=$2',
      [req.params.id, req.user.user_id]
    );
    res.json({ message: 'Notification marked as read.' });
  } catch (err) { next(err); }
});

// DELETE /api/notifications/:id
router.delete('/:id', requireAuth, async (req, res, next) => {
  try {
    await pool.query(
      'DELETE FROM notifications WHERE notification_id=$1 AND user_id=$2',
      [req.params.id, req.user.user_id]
    );
    res.json({ message: 'Notification dismissed.' });
  } catch (err) { next(err); }
});

module.exports = router;
