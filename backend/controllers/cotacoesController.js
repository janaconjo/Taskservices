const enviarEmail = require('../services/emailService');
const enviarSMS = require('../services/smsService');
const { v4: uuidv4 } = require('uuid');

exports.enviarCotacao = async (req, res) => {
  const { nome, email, telefone, servicos } = req.body;

  // Validação reforçada
  if (!nome?.trim() || !telefone?.trim() || !Array.isArray(servicos)) {
    return res.status(400).json({
      success: false,
      error: "Dados inválidos. Verifique nome, telefone e serviços."
    });
  }

  try {
    // Gera ID único
    const cotacaoId = `COT-${uuidv4().slice(0, 8).toUpperCase()}`;
    
    // Calcula preço (exemplo: 1500 MT por serviço + 500 MT por plano premium)
    const preco = servicos.reduce((total, servico) => {
      return total + 1500 + (servico.plano === 'Premium' ? 500 : 0);
    }, 0);

    // Envia comunicações (em paralelo)
    await Promise.all([
      enviarEmail(email || 'clientes@taskservices.co.mz', nome, servicos, preco),
      enviarSMS(telefone, `Task: Cotação ${cotacaoId} recebida! Valor: ${preco}MT`)
    ]);

    // Resposta de sucesso
    res.json({
      success: true,
      id: cotacaoId,
      preco: preco,
      message: "Cotação processada com sucesso"
    });

  } catch (error) {
    console.error("Erro no processamento:", error);
    res.status(500).json({
      success: false,
      error: "Erro interno. Por favor, tente novamente mais tarde."
    });
  }
};