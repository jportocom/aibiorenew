const router = require('express').Router();
const auth = require('../middleware/auth');
const pool = require('../db');
const calcService = require('../services/calcService');
const planService = require('../services/planService');
const avaliacaoService = require('../services/avaliacaoService');

router.get('/hoje', auth, async (req, res) => {
  try {
    const userRes = await pool.query('SELECT * FROM mp_users WHERE id=$1', [req.user.id]);
    const user = userRes.rows[0];
    if (!user.perfil_completo) return res.status(400).json({ erro: 'Perfil incompleto' });

    const metricas = calcService.calcular(user);
    const plano = planService.gerarPlano(metricas.tmb, user);

    // Verificar se já existe plano para hoje
    const hoje = new Date().toISOString().split('T')[0];
    const planoExiste = await pool.query(
      'SELECT * FROM mp_planos_diarios WHERE user_id=$1 AND data=$2',
      [req.user.id, hoje]
    );

    if (planoExiste.rows.length === 0) {
      await pool.query(
        `INSERT INTO mp_planos_diarios(user_id,data,tmb,calorias_pequeno_almoco,calorias_almoco,calorias_jantar,calorias_caminhada,deficit_previsto)
         VALUES($1,$2,$3,$4,$5,$6,$7,$8)`,
        [req.user.id, hoje, metricas.tmb, plano.pequeno_almoco, plano.almoco, plano.jantar, plano.calorias_caminhada, plano.deficit_previsto]
      );
    }

    res.json({ metricas, plano });
  } catch (err) {
    res.status(500).json({ erro: 'Erro ao gerar plano' });
  }
});

router.get('/avaliacao', auth, async (req, res) => {
  try {
    const { data } = req.query;
    const diaAvaliado = data || new Date().toISOString().split('T')[0];

    const refeicoes = await pool.query(
      'SELECT COALESCE(SUM(calorias),0) as total FROM mp_refeicoes WHERE user_id=$1 AND data=$2',
      [req.user.id, diaAvaliado]
    );
    const atividades = await pool.query(
      'SELECT COALESCE(SUM(calorias),0) as total FROM mp_atividades WHERE user_id=$1 AND data=$2',
      [req.user.id, diaAvaliado]
    );
    const userRes = await pool.query('SELECT * FROM mp_users WHERE id=$1', [req.user.id]);

    const user = userRes.rows[0];
    const metricas = calcService.calcular(user);
    const avaliacao = avaliacaoService.avaliarDia(
      parseFloat(refeicoes.rows[0].total),
      parseFloat(atividades.rows[0].total),
      metricas.tmb
    );

    res.json({ ...avaliacao, data: diaAvaliado, calorias_ingeridas: refeicoes.rows[0].total, calorias_gastas: atividades.rows[0].total });
  } catch (err) {
    res.status(500).json({ erro: 'Erro na avaliação' });
  }
});

module.exports = router;
