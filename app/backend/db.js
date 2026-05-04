require('dotenv').config();
const { Pool } = require('pg');

const pool = new Pool({
  host:     process.env.DB_HOST     || '127.0.0.1',
  user:     process.env.DB_USER     || 'mpo',
  password: process.env.DB_PASSWORD || 'P0rtugal',
  database: process.env.DB_NAME     || 'metabolic',
  port:     parseInt(process.env.DB_PORT || '5432'),
  max: 10
});

pool.on('error', (err) => {
  console.error('[db] Erro inesperado no pool:', err.message);
});

module.exports = {
  query: (sql, params = []) => pool.query(sql, params),
  pool
};
