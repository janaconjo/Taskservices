require('dotenv').config();
const nodemailer = require('nodemailer');

module.exports = {
  enviarEmail: async (email, nome, servicos, precoTotal) => {
    if (!email || !nome) {
      console.warn('E-mail não enviado - dados faltando');
      return;
    }

    // 1. Configurar transporter
    const transporter = nodemailer.createTransport({
  host: process.env.EMAIL_HOST,
  port: parseInt(process.env.EMAIL_PORT),
  secure: process.env.EMAIL_SECURE === 'true',
  auth: {
    user: process.env.EMAIL_USER,
    pass: process.env.EMAIL_PASSWORD
  },
  tls: {
    rejectUnauthorized: process.env.NODE_ENV === 'production'
  }
});


    const mailOptions = {
    from: `"Task Services" <${process.env.EMAIL_USER}>`,
      to: email,
      subject: `📋 Cotação #${Date.now().toString().slice(-6)}`,
      html: `
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
          <h2 style="color: #2e7d32;">Task Environmental Services</h2>
          <p>Olá <strong>${nome}</strong>,</p>
          <p>Agradecemos seu interesse em nossos serviços. Aqui está o resumo da sua cotação:</p>
          
          <h3 style="color: #2e7d32;">Serviços Solicitados</h3>
          <ul>
            ${servicos.map(s => `
              <li>
                <strong>${s.tipo}</strong>
                ${s.plano ? `(Plano ${s.plano})` : ''}
              </li>
            `).join('')}
          </ul>
          
          <h3 style="color: #2e7d32;">Valor Total Estimado</h3>
          <p style="font-size: 1.2em; color: #2e7d32;">
            <strong>${precoTotal} MT</strong>
          </p>  
          
          <p>Nossa equipe entrará em contato em breve para confirmar os detalhes.</p>
          <hr>
          <p style="font-size: 0.8em; color: #666;">
            Este é um e-mail automático. Por favor não responda.
          </p>
        </div>
      `
    };

    // 3. Enviar
    try {
      const info = await transporter.sendMail(mailOptions);
      console.log(`E-mail enviado para ${email}:`, info.messageId);
      return info;
    } catch (error) {
      console.error('Erro ao enviar e-mail:', {
        email,
        error: error.response || error.message
      });
      throw new Error('Falha ao enviar e-mail');
    }
  }
};