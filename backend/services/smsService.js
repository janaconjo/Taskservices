const axios = require('axios');

module.exports = {
  enviarSMS: async (telefone, mensagem) => {
    if (!telefone || !mensagem) {
      console.warn('SMS não enviado - dados faltando');
      return;
    }

    try {
      const numeroFormatado = `+258${telefone.replace(/\D/g, '')}`;
      const response = await axios.post(
        process.env.SMS_API_URL || 'https://api.mozesms.com/v2/send',
        {
          from: 'TaskEnv',
          to: numeroFormatado,
          text: mensagem.slice(0, 160)
        },
        {
          headers: {
            'Authorization': `Bearer ${process.env.SMS_API_KEY}`,
            'Content-Type': 'application/json'
          },
          timeout: 10000
        }
      );

      console.log(`SMS enviado para ${numeroFormatado}:`, response.data);
      return response.data;
    } catch (error) {
      console.error('Erro ao enviar SMS:', {
        numero: telefone,
        error: error.response?.data || error.message
      });
      throw new Error('Falha ao enviar SMS');
    }
  }
};