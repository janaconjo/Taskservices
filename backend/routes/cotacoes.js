const express = require('express');
const router = express.Router();
const cotacaoController = require('../controllers/cotacoesController');

// Rota principal
router.post('/', cotacaoController.enviarCotacao);

// Rota de teste
router.get('/teste', (req, res) => {
  res.json({ 
    status: 'success',
    message: 'API de cotações operacional',
    timestamp: new Date().toISOString()
  });
});

module.exports = router;