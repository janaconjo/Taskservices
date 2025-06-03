const express = require('express');
const bodyParser = require('body-parser');
const cors= require('cors');
const smsService = require('./services/smsService');
const emailService = require('./services/emailService');

const app = express();

app.use(cors());
app.use(bodyParser.json({ limit: '10mb' }));
app.use(bodyParser.urlencoded({ extended: true, limit: '10mb' }));

const database = {
  cotacoes: []
};

app.post('/api/cotacoes', async (req, res) => {
  console.log('Recebendo cotação:', req.body);

  try {
    const { contato, tipoServico = [], planoServico = [] } = req.body;
    
    if (!contato || !tipoServico || !planoServico) {
      return res.status(400).json({ 
        success: false,
        message: 'Estrutura de dados inválida'
      });
    }

    if (!contato.nome || !contato.telefone || !contato.localizacao) {
      return res.status(400).json({
        success: false,
        message: 'Nome, telefone e localização são obrigatórios'
      });
    }

if (!tipoServico.length && !req.body.planoData) {
  return res.status(400).json({
    success: false,
    message: 'Nenhum serviço fornecido (nem no formulário nem no localStorage)'
  });
}


    const servicos = tipoServico.map((tipo, index) => ({
      tipo,
      plano: planoServico[index] || 'Não especificado'
    }));

    const precoTotal = calcularPreco(servicos);

    const cotacao = {
      id: Date.now(),
      contato,
      servicos,
      precoTotal,
      data: new Date().toISOString(),
      status: 'pendente'
    };
    
    database.cotacoes.push(cotacao);

    const resultados = await Promise.allSettled([
      enviarSMS(contato.telefone, servicos, precoTotal),
      contato.email && enviarEmail(contato.email, contato.nome, servicos, precoTotal)
    ]);

    const erros = resultados.filter(r => r.status === 'rejected');
    if (erros.length > 0) {
      console.error('Erros nas notificações:', erros);
    }

    res.status(200).json({
      success: true,
      cotacaoId: cotacao.id,
      message: 'Cotação registrada com sucesso'
    });

  } catch (error) {
    console.error('Erro interno:', error);
    res.status(500).json({
      success: false,
      message: 'Erro interno no servidor'
    });
  }
});

function calcularPreco(servicos) {
  const tabelaPrecos = {
    'Residencial': { 'Básico': 1999, 'Standard': 2999, 'Premium': 4999 },
    'Condominio': { 'Básico': 1200, 'Standard': 1000, 'Premium': 999 },
    'Empresa': { 'Básico': 9999, 'Standard': 15999, 'Premium': 20000 },
    'Banheiros Móveis': {'Media':4000},
    'Contentores De Lixo': {'400 Litros': 2000, '70 Litros': 700},
    'Limpeza De Fossas': {'Media': 4500},
    'Limpeza Pós-Obra': {'M2':8500}
  };

  return servicos.reduce((total, servico) => {
    const preco = tabelaPrecos[servico.tipo]?.[servico.plano] || 0;
    return total + preco;
  }, 0);
}

async function enviarSMS(telefone, servicos, precoTotal) {
  if (!telefone) return;
  
  try {
    await smsService.enviarSMS(
      telefone,
      `TaskServices: Recebemos sua cotação para ${servicos.map(s => s.tipo).join(', ')}. ` +
      `Valor estimado: ${precoTotal} MT. Obrigado!`
    );
  } catch (error) {
    console.error('Falha no SMS:', error);
    throw error;
  }
}

async function enviarEmail(email, nome, servicos, precoTotal) {
  if (!email) return;
  
  try {
    await emailService.enviarEmail(
      email,
      nome,
      servicos,
      precoTotal
    );
  } catch (error) {
    console.error('Falha no e-mail:', error);
    throw error;
  }
}
function getDescricaoServico(tipo) {
  const descricoes = {
    'Residencial': 'Recolha de Lixo em Residências',
    'Condominio': 'Recolha de Lixo em Condomínios',
    'Empresa': 'Recolha de Lixo em Empresas'
  };
  return descricoes[tipo] || tipo;
}
const PORT = process.env.PORT || 3000;

app.listen(PORT, () => {
  console.log(`✅ Servidor em execução: http://localhost:${PORT}`);
});
