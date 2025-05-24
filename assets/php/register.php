<?php
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new Database();
    $conn = $db->connect();
    
    // Receber dados do formulário
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $phone = trim($_POST['phone'] ?? '');
    
    // Validações
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "O nome é obrigatório.";
    }
    
    if (empty($email)) {
        $errors[] = "O email é obrigatório.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "O email informado não é válido.";
    }
    
    if (empty($password)) {
        $errors[] = "A senha é obrigatória.";
    } elseif (strlen($password) < 8) {
        $errors[] = "A senha deve ter pelo menos 8 caracteres.";
    } elseif ($password !== $confirm_password) {
        $errors[] = "As senhas não coincidem.";
    }
    
    // Verificar se email já existe
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $errors[] = "Este email já está cadastrado.";
    }
    
    // Se não houver erros, inserir no banco
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        
        try {
            $stmt = $conn->prepare("
                INSERT INTO users (name, email, password, phone, role) 
                VALUES (:name, :email, :password, :phone, 'client')
            ");
            
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':phone', $phone);
            
            if ($stmt->execute()) {
                // Registrar ação na tabela de auditoria
                $user_id = $conn->lastInsertId();
                $action = "register";
                $ip_address = $_SERVER['REMOTE_ADDR'];
                
                $audit_stmt = $conn->prepare("
                    INSERT INTO user_audit_log (user_id, action, ip_address)
                    VALUES (:user_id, :action, :ip_address)
                ");
                $audit_stmt->bindParam(':user_id', $user_id);
                $audit_stmt->bindParam(':action', $action);
                $audit_stmt->bindParam(':ip_address', $ip_address);
                $audit_stmt->execute();
                
                // Redirecionar para login com mensagem de sucesso
                header("Location: login.php?registered=1");
                exit();
            }
        } catch(PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            $errors[] = "Ocorreu um erro ao registrar. Por favor, tente novamente.";
        }
    }
}
?>

<!-- HTML do formulário de registro -->
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Cadastro - Task Environmental</title>
    <!-- Inclua seus estilos CSS aqui -->
</head>
<body>
    <div class="container">
        <h2>Criar Nova Conta</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="name">Nome Completo</label>
                <input type="text" id="name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="phone">Telefone</label>
                <input type="tel" id="phone" name="phone">
            </div>
            
            <div class="form-group">
                <label for="password">Senha (mínimo 8 caracteres)</label>
                <input type="password" id="password" name="password" minlength="8" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirme sua Senha</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <button type="submit" class="btn">Registrar</button>
        </form>
        
        <p>Já tem uma conta? <a href="login.php">Faça login aqui</a></p>
    </div>
</body>
</html>