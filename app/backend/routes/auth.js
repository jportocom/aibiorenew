const router = require('express').Router();
const pool  = require('../db');
const jwt   = require('jsonwebtoken');
const bcrypt = require('bcrypt');
const config = require('../config');

// Suporta passwords plain text (dados importados) e bcrypt
async function verificarPassword(plain, stored) {
  if (stored.startsWith('$2b$') || stored.startsWith('$2a$')) {
    return bcrypt.compare(plain, stored);
  }
  return plain === stored;
}

// Garante que existe registo em mp_users para o email dado (para FK das tabelas de dados)
async function garantirMpUser(email, password) {
  const existing = await pool.query('SELECT id FROM mp_users WHERE email=$1', [email]);
  if (existing.rows.length > 0) return existing.rows[0].id;

  const hash = await bcrypt.hash(password, 12);
  const result = await pool.query(
    'INSERT INTO mp_users(email, password) VALUES($1,$2) RETURNING id',
    [email, hash]
  );
  return result.rows[0].id;
}

router.post('/login', async (req, res) => {
  try {
    const { email, password } = req.body;
    if (!email || !password) return res.status(400).json({ erro: 'Email e password obrigatórios' });

    const result = await pool.query('SELECT * FROM utilizadores WHERE email=$1', [email]);
    if (result.rows.length === 0) return res.status(401).json({ erro: 'Credenciais inválidas' });

    const utilizador = result.rows[0];
    if (utilizador.status !== '1') return res.status(403).json({ erro: 'Conta inactiva' });

    const valid = await verificarPassword(password, utilizador.password);
    if (!valid) return res.status(401).json({ erro: 'Credenciais inválidas' });

    const mpUserId = await garantirMpUser(utilizador.email, password);

    // Verificar se perfil mp_users está completo
    const mpUser = await pool.query('SELECT perfil_completo FROM mp_users WHERE id=$1', [mpUserId]);

    const token = jwt.sign(
      { id: mpUserId, email: utilizador.email, nome: utilizador.nome },
      config.jwtSecret,
      { expiresIn: config.jwtExpiry }
    );
    res.json({ token, perfil_completo: mpUser.rows[0].perfil_completo });
  } catch (err) {
    console.error('[auth/login]', err.message);
    res.status(500).json({ erro: 'Erro no servidor' });
  }
});

router.post('/register', async (req, res) => {
  try {
    const { email, password, nome } = req.body;
    if (!email || !password) return res.status(400).json({ erro: 'Email e password obrigatórios' });

    const existe = await pool.query('SELECT id FROM utilizadores WHERE email=$1', [email]);
    if (existe.rows.length > 0) return res.status(409).json({ erro: 'Email já registado' });

    // Criar em utilizadores (sem hash para manter compatibilidade com portal)
    await pool.query(
      'INSERT INTO utilizadores(email, nome, password, tipouser, status) VALUES($1,$2,$3,$4,$5)',
      [email, nome || '', password, 'C', '1']
    );

    // Criar em mp_users com hash
    const hash = await bcrypt.hash(password, 12);
    const mpResult = await pool.query(
      'INSERT INTO mp_users(email, password) VALUES($1,$2) RETURNING id',
      [email, hash]
    );
    const mpUserId = mpResult.rows[0].id;

    const token = jwt.sign(
      { id: mpUserId, email, nome: nome || '' },
      config.jwtSecret,
      { expiresIn: config.jwtExpiry }
    );
    res.status(201).json({ token, perfil_completo: false });
  } catch (err) {
    console.error('[auth/register]', err.message);
    res.status(500).json({ erro: 'Erro no servidor' });
  }
});

module.exports = router;
