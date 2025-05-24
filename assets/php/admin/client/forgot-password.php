<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'config/database.php';
    $db = new Database();
    $conn = $db->connect();

    $email = trim($_POST['email']);
    
    // Verificar se o email existe
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));
        
        // Salvar token no banco
        $update = $conn->prepare("UPDATE users SET reset_token = :token, reset_token_expires = :expires WHERE email = :email");
        $update->bindParam(':token', $token);
        $update->bindParam(':expires', $expires);
        $update->bindParam(':email', $email);
        $update->execute();

        // Enviar email (simplificado)
        $reset_link = "https://seusite.com/reset-password.php?token=$token";
        mail($email, "Redefinir Senha", "Acesse: $reset_link");
        
        header("Location: login.php?reset=email_sent");
    } else {
        header("Location: forgot-password.php?error=email_not_found");
    }
}
?>
<!-- Formulário HTML para solicitar recuperação -->