#!/usr/bin/env node
// Inicializa a base de dados SQLite e cria conta(s) de utilizador.
// Uso: node app/database/init.js

const Database = require('better-sqlite3');
const bcrypt   = require('bcrypt');
const path     = require('path');
const fs       = require('fs');

const dbPath     = path.join(__dirname, 'medepeso.db');
const schemaPath = path.join(__dirname, 'schema-sqlite.sql');

console.log('→ A inicializar base de dados em:', dbPath);

const db = new Database(dbPath);
db.pragma('journal_mode = WAL');
db.pragma('foreign_keys = ON');

const schema = fs.readFileSync(schemaPath, 'utf8');
db.exec(schema);
console.log('✓ Schema aplicado');

// Criar / actualizar utilizadores iniciais
const utilizadores = [
  { email: 'marco.a.porto@gmail.com', password: 'P0rtugal', nome: 'Marco' }
];

const insertUser = db.prepare(
  `INSERT OR IGNORE INTO users(email, password, nome) VALUES(?, ?, ?)`
);
const updatePwd = db.prepare(
  `UPDATE users SET password=? WHERE email=?`
);

(async () => {
  for (const u of utilizadores) {
    const hash = await bcrypt.hash(u.password, 12);
    const info = insertUser.run(u.email, hash, u.nome);
    if (info.changes === 0) {
      // Já existe — actualizar password
      updatePwd.run(hash, u.email);
      console.log(`✓ Password actualizada: ${u.email}`);
    } else {
      console.log(`✓ Utilizador criado: ${u.email}`);
    }
  }
  db.close();
  console.log('✓ Base de dados pronta.');
})();
