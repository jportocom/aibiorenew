const router = require('express').Router();
const pool = require('../db');
const auth = require('../middleware/auth');

router.get('/', auth, async (req, res) => {
  try {
    const { tipo, max_calorias, tipo_dieta } = req.query;
    let query = 'SELECT * FROM mp_cardapios WHERE 1=1';
    const params = [];

    if (tipo) { params.push(tipo); query += ` AND tipo=$${params.length}`; }
    if (max_calorias) { params.push(parseFloat(max_calorias)); query += ` AND calorias<=$${params.length}`; }
    if (tipo_dieta && tipo_dieta !== 'omnivoro') {
      params.push(tipo_dieta);
      query += ` AND (tipo_dieta=$${params.length} OR tipo_dieta='todos')`;
    }
    query += ' ORDER BY calorias ASC';

    const result = await pool.query(query, params);
    res.json(result.rows);
  } catch (err) {
    res.status(500).json({ erro: 'Erro ao listar cardápios' });
  }
});

module.exports = router;
