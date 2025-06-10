CREATE DATABASE IF NOT EXISTS advocacia_db;
USE advocacia_db;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    perfil ENUM('admin','usuario') DEFAULT 'usuario',
    precisa_alterar_senha BOOLEAN DEFAULT 1,
    foto_perfil VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    cpf_cnpj VARCHAR(20),
    contato VARCHAR(100),
    email VARCHAR(100),
    endereco VARCHAR(255),
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS processos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_processo VARCHAR(100) NOT NULL UNIQUE,
    titulo VARCHAR(255) NOT NULL,
    cliente_id INT DEFAULT NULL,
    tipo_acao VARCHAR(100) NOT NULL,
    objeto TEXT NOT NULL,
    status VARCHAR(50) DEFAULT 'Em andamento',
    data_abertura DATE NOT NULL DEFAULT (CURRENT_DATE),
    juizo VARCHAR(255),
    foro VARCHAR(255),
    link_tribunal VARCHAR(255),
    valor_causa DECIMAL(15,2),
    valor_condenacao DECIMAL(15,2),
    distribuido_em DATE,
    requerente VARCHAR(255),
    requerido VARCHAR(255),
    observacoes TEXT,
    advogado_responsavel VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS andamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    processo_id INT NOT NULL,
    data_andamento DATE NOT NULL,
    descricao TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (processo_id) REFERENCES processos(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS tarefas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT,
    data_prazo DATE,
    status VARCHAR(20) DEFAULT 'Pendente',
    processo_id INT DEFAULT NULL,
    usuario VARCHAR(50) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (processo_id) REFERENCES processos(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS documentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    processo_id INT NOT NULL,
    nome_arquivo VARCHAR(255),
    caminho VARCHAR(255),
    data_upload DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (processo_id) REFERENCES processos(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS etiquetas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL UNIQUE,
    cor VARCHAR(20) DEFAULT '#8e24aa'
);

CREATE TABLE IF NOT EXISTS processo_etiqueta (
    processo_id INT NOT NULL,
    etiqueta_id INT NOT NULL,
    PRIMARY KEY (processo_id, etiqueta_id),
    FOREIGN KEY (processo_id) REFERENCES processos(id) ON DELETE CASCADE,
    FOREIGN KEY (etiqueta_id) REFERENCES etiquetas(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS logs_auditoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    acao VARCHAR(255) NOT NULL,
    tabela_afetada VARCHAR(100),
    registro_id INT,
    dados_anteriores TEXT,
    dados_novos TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Atualizando o usuário admin com senha bcrypt (admin123)
DELETE FROM usuarios WHERE username = 'admin';
INSERT INTO usuarios (username, password, email, perfil)
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@advocacia.com', 'admin');

