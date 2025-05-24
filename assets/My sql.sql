# criando a tabela

create table users (
id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    role ENUM('admin', 'client', 'employee') DEFAULT 'client',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    last_login TIMESTAMP NULL,
    reset_token VARCHAR(255) DEFAULT NULL,
    reset_token_expires TIMESTAMP NULL

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

create index idx_email on users(email);
create index idx_status on users(status);

insert into users (name, email, password, role) 
VALUES (
    'Administrador', 
    'admin@taskservices.co.mz', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',  -- senha: password
    'admin'
);

-- Tabela para logs de login
CREATE TABLE login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    email VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    success BOOLEAN NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabela para auditoria de ações
CREATE TABLE user_audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE USER 'task_app'@'localhost' IDENTIFIED BY 'uma_senha_forte_aqui';
GRANT SELECT, INSERT, UPDATE ON task_environmental.users TO 'task_app'@'localhost';
GRANT SELECT, INSERT ON task_environmental.login_attempts TO 'task_app'@'localhost';
GRANT SELECT, INSERT ON task_environmental.user_audit_log TO 'task_app'@'localhost';
FLUSH PRIVILEGES;

CREATE TABLE cotacoes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(255),
  email VARCHAR(255),
  telefone VARCHAR(50),
  localizacao VARCHAR(255),
  servicos TEXT,
  caminho_pdf VARCHAR(255),
  data TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);