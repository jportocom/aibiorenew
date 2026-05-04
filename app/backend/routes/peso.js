const router = require('express').Router();
const auth = require('../middleware/auth');
const pool = require('../db');

router.get('/', auth, async (req, res) => {
  try {
    const data = await pool.query(
      'SELECT * FROM mp_pesos WHERE user_id=$1 ORDER BY data ASC',
      [req.user.id]
    );
    res.json(data.rows);
  } catch (err) {
    res.status(500).json({ erro: 'Erro ao listar pesos' });
  }
});

router.post('/', auth, async (req, res) => {
  try {
    const { peso, data } = req.body;
    if (!peso) return res.status(400).json({ erro: 'Peso obrigatório' });

    const result = await pool.query(
      'INSERT INTO mp_pesos(user_id, data, peso) VALUES($1, $2, $3) RETURNING *',
      [req.user.id, data || new Date().toISOString().split('T')[0], peso]
    );

    // Actualizar peso actual do utilizador
    await pool.query('UPDATE mp_users SET peso=$1 WHERE id=$2', [peso, req.user.id]);

    res.status(201).json(result.rows[0]);
  } catch (err) {
    res.status(500).json({ erro: 'Erro ao inserir peso' });
  }
});

router.put('/:id', auth, async (req, res) => {
  try {
    const { peso, data } = req.body;
    if (!peso) return res.status(400).json({ erro: 'Peso obrigatório' });
    const result = await pool.query(
      'UPDATE mp_pesos SET peso=$1, data=$2 WHERE id=$3 AND user_id=$4 RETURNING *',
      [peso, data || new Date().toISOString().split('T')[0], req.params.id, req.user.id]
    );
    if (result.rowCount === 0) return res.status(404).json({ erro: 'Registo não encontrado' });
    res.json(result.rows[0]);
  } catch (err) {
    res.status(500).json({ erro: 'Erro ao editar peso' });
  }
});

router.delete('/:id', auth, async (req, res) => {
  try {
    await pool.query('DELETE FROM mp_pesos WHERE id=$1 AND user_id=$2', [req.params.id, req.user.id]);
    res.json({ sucesso: true });
  } catch (err) {
    res.status(500).json({ erro: 'Erro ao apagar peso' });
  }
});

module.exports = router;
