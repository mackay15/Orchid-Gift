const { Pool } = require('pg');

const pool = new Pool({
  host:     process.env.POSTGRES_HOST     || 'localhost',
  port:     parseInt(process.env.POSTGRES_PORT || '5432'),
  database: process.env.POSTGRES_DB       || 'orchid_db',
  user:     process.env.POSTGRES_USER     || 'orchid_user',
  password: process.env.POSTGRES_PASSWORD || 'orchid_secret_2024',
});

pool.on('connect', () => {
  console.log('🐘 PostgreSQL connected');
});

pool.on('error', (err) => {
  console.error('PostgreSQL pool error:', err);
  process.exit(-1);
});

module.exports = pool;
