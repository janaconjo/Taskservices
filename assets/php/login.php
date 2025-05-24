<?php
session_start();

// Configurações do banco de dados
$host = 'localhost';
$dbname = 'task_environmental';
$username = 'root';
$password = '';
// Conexão com o banco de dados
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die(json_encode(['success' => false, 'message' => 'Erro na conexão com o banco de dados']));
}

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    die(json_encode(['success' => false, 'message' => 'Por favor, preencha todos os campos']));
}

// Buscar usuário no banco de dados
$stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = :email");
$stmt->bindParam(':email', $email);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        echo json_encode(['success' => true, 'redirect' => 'dashboard.html']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Email ou senha incorretos']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Email ou senha incorretos']);
}

// Processar o formulário de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    // Validar entrada
    if (empty($email) || empty($password)) {
        $error = "Por favor, preencha todos os campos.";
    } else {
        // Buscar usuário no banco de dados
        $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verificar a senha
            if (password_verify($password, $user['password'])) {
                // Login bem-sucedido
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                // Redirecionar para a página apropriada
                if ($user['role'] === 'admin') {
                    header("Location: admin/dashboard.php");
                } else {
                    header("Location: client/dashboard.php");
                }
                exit();
            } else {
                $error = "Email ou senha incorretos.";
            }
        } else {
            $error = "Email ou senha incorretos.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Task Environmental Service</title>
  <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
  <div class="login-container">
    <div class="login-left">
      <h1>Bem-vindo de volta!</h1>
      <p>Acesse sua conta para gerenciar seus serviços de gestão de resíduos e acompanhar suas solicitações.</p>
      <img src="assets/images/logo/Task logo-white.svg" alt="Task Environmental Service" style="max-width: 200px;">
    </div>
    
    <div class="login-right">
      <div class="login-form-container">
        <div class="logo">
          <img src="assets/images/logo/Task logo.svg" alt="Task Environmental Service">
        </div>
        
        <?php if (isset($error)): ?>
          <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form class="login-form" action="login.php" method="POST">
          <h2>Login</h2>
          
          <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" class="form-control" placeholder="Digite seu email" required>
          </div>
          
          <div class="form-group">
            <label for="password">Senha</label>
            <input type="password" id="password" name="password" class="form-control" placeholder="Digite sua senha" required>
            <a href="forgot-password.php" class="forgot-password">Esqueceu a senha?</a>
          </div>
          
          <button type="submit" class="btn">Entrar</button>
          
          <div class="divider"><span>ou</span></div>
          
          <div class="social-login">
            <a href="#" class="social-btn"><i class="fab fa-facebook-f"></i></a>
            <a href="#" class="social-btn"><i class="fab fa-google"></i></a>
            <a href="#" class="social-btn"><i class="fab fa-linkedin-in"></i></a>
          </div>
          
          <div class="register-link">
            Não tem uma conta? <a href="register.php">Cadastre-se</a>
          </div>
        </form>
      </div>
    </div>
  </div>
  <script>
    fetch('login.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: `email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        showMessage('Login bem-sucedido! Redirecionando...', 'success');
        setTimeout(() => {
            window.location.href = data.redirect;
        }, 1000);
    } else {
        showMessage(data.message, 'danger');
    }
    loginBtn.disabled = false;
    loginBtn.textContent = 'Entrar';
})
.catch(error => {
    showMessage('Erro ao conectar com o servidor', 'danger');
    loginBtn.disabled = false;
    loginBtn.textContent = 'Entrar';
});
  </script>


  
</body>
</html>