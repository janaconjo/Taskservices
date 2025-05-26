const twilio = require('twilio');
const { twilio: config } = require('../config');

const client = twilio(config.accountSid, config.authToken);

module.exports = async (numero, preco) => {
  await client.messages.create({
    body: `Task Environmental: Cotação gerada - MT ${preco}. Detalhes enviados por email.`,
    from: config.fromNumber,
    to: numero
  });
};