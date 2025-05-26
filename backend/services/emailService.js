const nodemailer = require('nodemailer');
const { emailConfig } = require('../config');

// Configurar transporte SMTP
const transporter = nodemailer.createTransport({
  service: 'Gmail', // Pode usar 'Outlook' ou outro
  auth: {
    user: emailConfig.user,
    pass: emailConfig.password
  }
});

module.exports = async (email, nome, servicos, preco) => {
  const mailOptions = {
    from: `Task Environmental <${emailConfig.user}>`,
    to: email,
    subject: 'Sua Cotação - Task Environmental',
    html: `
      <h2>Olá ${nome},</h2>
      <p>Sua cotação foi processada com sucesso!</p>
      <h3>Valor Estimado: ${preco} MT</h3>
      <h4>Serviços Selecionados:</h4>
      <ul>
        ${Object.entries(servicos).map(([key, val]) => 
          `<li><strong>${key}:</strong> ${val}</li>`).join('')}
      </ul>
      <p>Entraremos em contato em breve para confirmar os detalhes.</p>
    `
  };

  await transporter.sendMail(mailOptions);
};