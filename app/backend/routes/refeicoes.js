const router = require('express').Router();
const auth = require('../middleware/auth');
const pool = require('../db');

router.get('/', auth, async (req, res) => {
  try {
    const { data } = req.query;
    const filtroData = data || new Date().toISOString().split('T')[0];
    const result = await pool.query(
      'SELECT * FROM mp_refeicoes WHERE user_id=$1 AND data=$2 ORDER BY created_at',
      [req.user.id, filtroData]
    );
    res.json(result.rows);
  } catch (err) {
    res.status(500).json({ erro: 'Erro ao listar refeições' });
  }
});

router.post('/', auth, async (req, res) => {
  try {
    const { tipo, calorias, proteina, hidratos, gordura, fibra, descricao, data } = req.body;
    const dataRefeicao = data || new Date().toISOString().split('T')[0];

    const result = await pool.query(
      `INSERT INTO mp_refeicoes(user_id,data,tipo,calorias,proteina,hidratos,gordura,fibra,descricao)
       VALUES($1,$2,$3,$4,$5,$6,$7,$8,$9) RETURNING *`,
      [req.user.id, dataRefeicao, tipo, calorias, proteina||0, hidratos||0, gordura||0, fibra||0, descricao||'']
    );
    res.status(201).json(result.rows[0]);
  } catch (err) {
    res.status(500).json({ erro: 'Erro ao registar refeição' });
  }
});

// Hook para IA — análise de foto (preparado para integração futura)
router.post('/foto', auth, async (req, res) => {
  try {
    // TODO: integrar com API de visão computacional (ex: Google Vision, OpenAI GPT-4V)
    // Por agora retorna dados de exemplo
    res.json({
      alimentos_detectados: ['aveia', 'leite vegetal', 'banana', 'nozes'],
      calorias_estimadas: 420,
      proteina: 15,
      hidratos: 58,
      gordura: 16,
      fibra: 9,
      nota: 'Análise por IA — em desenvolvimento'
    });
  } catch (err) {
    res.status(500).json({ erro: 'Erro na análise' });
  }
});

module.exports = router;
