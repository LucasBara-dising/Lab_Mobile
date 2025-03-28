create database API_mobile;
use API_mobile;	

CREATE TABLE TB_Usuario (
    nome_user VARCHAR(50) PRIMARY KEY,     -- Nome de usuário (único e não pode ser nulo)
    senha VARCHAR(255) NOT NULL,              -- Hash da senha (nunca a senha em texto puro!)
    energia INT DEFAULT 100,                   -- Energia (valor padrão 100)
    moedas INT DEFAULT 0                       -- Moedas (valor padrão 0)
);

INSERT INTO TB_Usuario (nome_user, senha, moedas) VALUES
('jogador1', '$2y$10$Ul9oQFHZIvQylXkWHQsBxeROaOp/KPCbWTZD5T1FXfe3I6ZQ1XP4e', 50);

INSERT INTO TB_Usuario (nome_user, senha, energia, moedas) VALUES
('admin', '$2y$10$AxmqyIXWAeFZvZobPw1.7.UUsOxMpOTgvDZOHXFa3MavZuppXAU5y', 80, 100);

INSERT INTO TB_Usuario (nome_user, senha, energia, moedas) VALUES
('SuperGamer', '$2b$12$y3k2n0m0p3.q5r.9.8T1xO6.4.X/U9r0.7.J8v7W9/x.V.1.2z0', 150, 200);