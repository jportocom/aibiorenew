const router = require('express').Router();
const auth = require('../middleware/auth');
const pool = require('../db');

router.post('/', auth, async (req, res) => {
  try {
    const { duracao_dias } = req.body;
    if (![30,90,180].includes(parseInt(duracao_dias))) {
      return res.status(400).json({ erro: 'Duração deve ser 30, 90 ou 180 dias' });
    }

    const userRes = await pool.query('SELECT peso FROM mp_users WHERE id=$1', [req.user.id]);
    const peso = userRes.rows[0].peso;

    const hoje = new Date();
    const fim = new Date();
    fim.setDate(hoje.getDate() + parseInt(duracao_dias));

    // 50g/dia mínimo, 150g/dia máximo, média 100g/dia
    const pesoAlvoMin = peso - (duracao_dias * 0.05);
    const pesoAlvoMax = peso - (duracao_dias * 0.15);

    // Desactivar objectivos anteriores
    await pool.query('UPDATE mp_objetivos SET ativo=false WHERE user_id=$1', [req.user.id]);

    const result = await pool.query(
      `INSERT INTO mp_objetivos(user_id,duracao_dias,data_inicio,data_fim,peso_inicio,peso_alvo_min,peso_alvo_max)
       VALUES($1,$2,$3,$4,$5,$6,$7) RETURNING *`,
      [req.user.id, duracao_dias, hoje, fim, peso, pesoAlvoMin, pesoAlvoMax]
    );

    res.status(201).json(result.rows[0]);
  } catch (err) {
    res.status(500).json({ erro: 'Erro ao criar objectivo' });
  }
});

router.get('/activo', auth, async (req, res) => {
  try {
    const result = await pool.query(
      'SELECT * FROM mp_objetivos WHERE user_id=$1 AND ativo=true ORDER BY created_at DESC LIMIT 1',
      [req.user.id]
    );
    res.json(result.rows[0] || null);
  } catch (err) {
    res.status(500).json({ erro: 'Erro ao obter objectivo' });
  }
});

router.get('/', auth, async (req, res) => {
  try {
    const result = await pool.query(
      'SELECT * FROM mp_objetivos WHERE user_id=$1 ORDER BY created_at DESC',
      [req.user.id]
    );
    res.json(result.rows);
  } catch (err) {
    res.status(500).json({ erro: 'Erro ao listar objectivos' });
  }
});

router.put('/:id', auth, async (req, res) => {
  try {
    const { data_inicio, data_fim, peso_inicio, peso_alvo } = req.body;
    if (!data_inicio || !data_fim || peso_inicio == null) {
      return res.status(400).json({ erro: 'data_inicio, data_fim e peso_inicio são obrigatórios' });
    }
    const pi = parseFloat(peso_inicio);
    if (isNaN(pi) || pi < 20 || pi > 300) return res.status(400).json({ erro: 'Peso inicial inválido' });
    if (data_fim <= data_inicio) return res.status(400).json({ erro: 'Data fim deve ser após data início' });

    const d1 = new Date(data_inicio + 'T00:00:00');
    const d2 = new Date(data_fim    + 'T00:00:00');
    const duracao_dias = Math.round((d2 - d1) / 86400000);

    let alvoMin, alvoMax;
    if (peso_alvo != null && !isNaN(parseFloat(peso_alvo))) {
      alvoMin = alvoMax = parseFloat(peso_alvo);
    } else {
      alvoMin = pi - duracao_dias * 0.05;
      alvoMax = pi - duracao_dias * 0.15;
    }

    const result = await pool.query(
      `UPDATE mp_objetivos
       SET data_inicio=$1, data_fim=$2, peso_inicio=$3, duracao_dias=$4, peso_alvo_min=$5, peso_alvo_max=$6
       WHERE id=$7 AND user_id=$8 RETURNING *`,
      [data_inicio, data_fim, pi, duracao_dias, alvoMin, alvoMax, req.params.id, req.user.id]
    );
    if (result.rowCount === 0) return res.status(404).json({ erro: 'Objectivo não encontrado' });
    res.json(result.rows[0]);
  } catch (err) {
    res.status(500).json({ erro: 'Erro ao editar objectivo' });
  }
});

module.exports = router;
