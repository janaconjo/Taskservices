<?php
session_start();
require_once '../config/database.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$db = new Database();
$conn = $db->connect();

// Buscar serviços contratados pelo cliente
$stmt = $conn->prepare("SELECT * FROM services WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Minha Conta - Task Environmental</title>
</head>
<body>
    <h1>Olá, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
    <h2>Seus Serviços Contratados:</h2>
    <ul>
        <?php foreach ($services as $service): ?>
            <li><?php echo htmlspecialchars($service['name']); ?></li>
        <?php endforeach; ?>
    </ul>
    <a href="../logout.php">Sair</a>
</body>
</html>