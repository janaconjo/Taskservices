<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'config/database.php';
    $db = new Database();
    $conn = $db->connect();

    $token = $_POST['token'];
    $new_password = password_hash($_POST['new_password'], PASSWORD_BCRYPT);
    
    // Verificar token válido
    $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = :token AND reset_token_expires > NOW()");
    $stmt->bindParam(':token', $token);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Atualizar senha e limpar token
        $update = $conn->prepare("UPDATE users SET password = :password, reset_token = NULL, reset_token_expires = NULL WHERE id = :id");
        $update->bindParam(':password', $new_password);
        $update->bindParam(':id', $user['id']);
        $update->execute();
        
        header("Location: login.php?reset=success");
    } else {
        header("Location: reset-password.php?token=$token&error=invalid_token");
    }
}
?>
<!-- Formulário HTML para nova senha -->