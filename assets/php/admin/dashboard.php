<?php
session_start();
require_once '../config/database.php';

// Verificar se o usuário está logado e é admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php?access_denied=true");
    exit();
}

$db = new Database();
$conn = $db->connect();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Painel Admin - Task Environmental</title>
    <style>
        /* Use o mesmo CSS do seu site principal (#085b57 e #2aac3f) */
        .admin-nav { background-color: #085b57; }
        .admin-btn { background-color: #2aac3f; }
    </style>
</head>
<body>
    <nav class="admin-nav">
        <a href="dashboard.php">Home</a>
        <a href="gerenciar_usuarios.php">Usuários</a>
        <a href="servicos.php">Serviços</a>
        <a href="../logout.php">Sair</a>
    </nav>

    <div class="container">
        <h1>Bem-vindo, <?php echo htmlspecialchars($_SESSION['user_name']); ?> (Admin)</h1>
        <!-- Conteúdo exclusivo para administradores -->
    </div>
</body>
</html>