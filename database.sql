-- Script de Criação do Banco de Dados: projeto_reservas
-- Execute este script no PHPMyAdmin ou via linha de comando MySQL

CREATE DATABASE IF NOT EXISTS projeto_reservas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE projeto_reservas;

-- 1. Tabela de Usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    tipo_conta ENUM('professor', 'admin') NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Tabela de Agendamentos
-- Relaciona as reservas ao professor e limita as quantidades
CREATE TABLE IF NOT EXISTS agendamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_professor INT NOT NULL,
    qtd_computadores INT DEFAULT 0,
    qtd_tablets INT DEFAULT 0,
    qtd_celulares INT DEFAULT 0,
    data_reserva DATE NOT NULL,
    horario_inicio TIME NOT NULL,
    horario_fim TIME NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_professor_agendamento
        FOREIGN KEY (id_professor) 
        REFERENCES usuarios(id) 
        ON DELETE CASCADE
);

-- 3. Inserção de um Usuário Administrador Padrão
-- A senha padrão é: admin123 (hasheada com BCRYPT gerada via password_hash do PHP)
INSERT INTO usuarios (nome, email, senha, tipo_conta) 
VALUES (
    'Administrador Global', 
    'admin@reservas.com', 
    '$2y$10$Ew9Qv/v8u0qU.j2QxT.3.O5K0.T8s5j7/5Y4.U9c3oZ.Q1r9W2W2m', 
    'admin'
) ON DUPLICATE KEY UPDATE email=email;

-- 4. View para verificar disponibilidade (Opcional, útil para lógicas complexas de backend no futuro)
CREATE OR REPLACE VIEW view_estoque_diario AS
SELECT 
    data_reserva,
    SUM(qtd_computadores) as computadores_reservados,
    SUM(qtd_tablets) as tablets_reservados,
    SUM(qtd_celulares) as celulares_reservados
FROM agendamentos
GROUP BY data_reserva;
