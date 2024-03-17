const { Pool } = require('pg');

const pool = new Pool({
  user: 'yardley',
  host: 'localhost',
  database: 'user',
  password: 'yourPassword', // replace with your actual password
  port: 15672, // the default port for PostgreSQL
});

module.exports = pool;
