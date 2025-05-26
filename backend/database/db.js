const sqlite3 = require('sqlite3').verbose();
const { db } = require('../config');

const connection = new sqlite3.Database(db.filename);

// Criar tabela de cotações
connection.serialize(() => {
  connection.run(`
    CREATE TABLE IF NOT EXISTS cotacoes (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      nome TEXT,
      email TEXT,
      telefone TEXT,
      servicos TEXT,
      preco REAL,
      data TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
  `);
});

module.exports = connection;