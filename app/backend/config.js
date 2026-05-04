require('dotenv').config();

module.exports = {
  jwtSecret: process.env.JWT_SECRET || 'medepeso_dev_secret_2026',
  jwtExpiry: '7d',
  port:      process.env.PORT || 3000
};
