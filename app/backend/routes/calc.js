const router = require('express').Router();
const auth = require('../middleware/auth');
const pool = require('../db');
const calcService = require('../services/calcService');

router.get('/', auth, async (req, res) => {
  try {
    const result = await pool.query('SELECT * FROM mp_users WHERE id=$1', [req.user.id]);
    if (result.rows.length === 0) return res.status(404).json({ erro: 'Utilizador não encontrado' });

    const user = result.rows[0];
    if (!user.perfil_completo) return res.status(400).json({ erro: 'Perfil incompleto' });

    const metricas = calcService.calcular(user);

    // Guardar histórico de métricas
    await pool.query(
      `INSERT INTO mp_metricas(user_id, data, imc, tmb, tdee) VALUES($1, CURRENT_DATE, $2, $3, $4)
       ON CONFLICT(user_id, data) DO UPDATE SET imc=excluded.imc, tmb=excluded.tmb, tdee=excluded.tdee`,
      [req.user.id, metricas.imc, metricas.tmb, metricas.tdee]
    ).catch(() => {});

    res.json(metricas);
  } catch (err) {
    res.status(500).json({ erro: 'Erro no servidor' });
  }
});

module.exports = router;
