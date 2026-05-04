require('dotenv').config();
const express = require('express');
const cors = require('cors');
const config = require('./config');

const app = express();

app.use(cors());
app.use(express.json({ limit: '10mb' }));
app.use(express.urlencoded({ extended: true }));

// Rotas
app.use('/auth',       require('./routes/auth'));
app.use('/user',       require('./routes/user'));
app.use('/calc',       require('./routes/calc'));
app.use('/peso',       require('./routes/peso'));
app.use('/objetivos',  require('./routes/objetivos'));
app.use('/refeicoes',  require('./routes/refeicoes'));
app.use('/atividades', require('./routes/atividades'));
app.use('/cardapios',  require('./routes/cardapios'));
app.use('/plano',      require('./routes/plano'));

// Health check
app.get('/health', (req, res) => res.json({ status: 'ok', timestamp: new Date() }));

app.listen(config.port, () => {
  console.log(`MedePeso API a correr em http://localhost:${config.port}`);
});

module.exports = app;
