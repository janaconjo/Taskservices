const db = require('../database/db');
const { calcularPreco } = require('../../services/priceCalculator');
const enviarEmail = require('../services/emailService');
const enviarSMS = require('../services/smsService');

exports.criarCotacao = async (req, res) => {
  try {
    const { nome, email, telefone, servicos } = req.body;
    
    // Calcular preço
    const preco = calcularPreco(servicos);
    
    // Salvar no banco
    db.run(
      `INSERT INTO cotacoes (nome, email, telefone, servicos, preco) 
       VALUES (?, ?, ?, ?, ?)`,
      [nome, email, telefone, JSON.stringify(servicos), preco],
      (err) => {
        if (err) throw err;
      }
    );

    // Enviar comunicações
    await enviarEmail(email, nome, servicos, preco);
    await enviarSMS(telefone, preco);

    res.status(201).json({ success: true, preco });
    
  } catch (error) {
    console.error(error);
    res.status(500).json({ error: 'Erro interno do servidor' });
  }
};