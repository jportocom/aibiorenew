const router = require('express').Router();
const auth = require('../middleware/auth');
const pool = require('../db');

router.get('/profile', auth, async (req, res) => {
  try {
    const result = await pool.query(
      'SELECT id,email,nome,sexo,idade,altura,peso,atividade,tipo_dieta,come_ovos,perfil_completo,created_at FROM mp_users WHERE id=$1',
      [req.user.id]
    );
    if (result.rows.length === 0) return res.status(404).json({ erro: 'Utilizador não encontrado' });
    res.json(result.rows[0]);
  } catch (err) {
    res.status(500).json({ erro: 'Erro no servidor' });
  }
});

router.put('/profile', auth, async (req, res) => {
  try {
    const { nome, sexo, idade, altura, peso, atividade, tipo_dieta, come_ovos } = req.body;

    await pool.query(
      `UPDATE mp_users SET nome=$1, sexo=$2, idade=$3, altura=$4, peso=$5,
       atividade=$6, tipo_dieta=$7, come_ovos=$8, perfil_completo=true
       WHERE id=$9`,
      [nome, sexo, idade, altura, peso, atividade, tipo_dieta, come_ovos ?? true, req.user.id]
    );

    // Registar peso inicial na tabela pesos (INSERT OR IGNORE para evitar duplicados)
    await pool.query(
      `INSERT INTO mp_pesos(user_id, data, peso) VALUES($1, CURRENT_DATE, $2) ON CONFLICT DO NOTHING`,
      [req.user.id, peso]
    );

    res.json({ sucesso: true });
  } catch (err) {
    res.status(500).json({ erro: 'Erro ao actualizar perfil' });
  }
});

module.exports = router;
