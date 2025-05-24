<div class="user-area">
    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="client/dashboard.php">Minha Conta</a>
        <a href="logout.php">Sair</a>
    <?php else: ?>
        <a href="login.php">Login</a>
        <a href="register.php">Cadastre-se</a>
    <?php endif; ?>
</div>