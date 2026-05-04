const router = require('express').Router();
const auth = require('../middleware/auth');
const pool = require('../db');

// Calorias gastas por minuto de caminhada (aprox. 70kg, ritmo moderado)
const CAL_POR_MIN_CAMINHADA = 4.5;

router.get('/', auth, async (req, res) => {
  try {
    const { data } = req.query;
    const filtroData = data || new Date().toISOString().split('T')[0];
    const result = await pool.query(
      'SELECT * FROM mp_atividades WHERE user_id=$1 AND data=$2 ORDER BY created_at',
      [req.user.id, filtroData]
    );
    res.json(result.rows);
  } catch (err) {
    res.status(500).json({ erro: 'Erro ao listar actividades' });
  }
});

router.post('/', auth, async (req, res) => {
  try {
    const { tipo, duracao_min, passos, data } = req.body;
    const dataAtiv = data || new Date().toISOString().split('T')[0];

    const userRes = await pool.query('SELECT peso FROM mp_users WHERE id=$1', [req.user.id]);
    const pesoKg = userRes.rows[0]?.peso || 70;
    const fatorPeso = pesoKg / 70;
    const calorias = duracao_min ? Math.round(duracao_min * CAL_POR_MIN_CAMINHADA * fatorPeso) : 0;

    const result = await pool.query(
      `INSERT INTO mp_atividades(user_id,data,tipo,duracao_min,calorias,passos)
       VALUES($1,$2,$3,$4,$5,$6) RETURNING *`,
      [req.user.id, dataAtiv, tipo||'caminhada', duracao_min||0, calorias, passos||0]
    );
    res.status(201).json(result.rows[0]);
  } catch (err) {
    res.status(500).json({ erro: 'Erro ao registar actividade' });
  }
});

module.exports = router;
