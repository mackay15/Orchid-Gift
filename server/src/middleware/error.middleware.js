// eslint-disable-next-line no-unused-vars
const errorHandler = (err, _req, res, _next) => {
  console.error('❌ Error:', err);
  const status  = err.status || err.statusCode || 500;
  const message = err.message || 'Internal Server Error';
  res.status(status).json({ error: message });
};

module.exports = errorHandler;
