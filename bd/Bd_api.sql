create database API_mobile;
use API_mobile;	

CREATE TABLE tb_usuario (
    id_user INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nome_usuario VARCHAR(50),              -- Nome de usuário
    senha VARCHAR(255) NOT NULL,            -- Senha de acesso
    email VARCHAR(255) NOT NULL,            -- Email para comunicação
    energia INT DEFAULT 100,               -- Energia do usuário (valor padrão 100)
    moedas INT DEFAULT 100,                  -- Moedas do usuário (valor padrão 100)
    avatar_id INT DEFAULT NULL             -- ID do avatar do usuário (valor padrão NULL)
);

CREATE TABLE tb_colecao (
    id_colecao INT PRIMARY KEY AUTO_INCREMENT,
    nome_colecao VARCHAR(255) NOT NULL,
    descricao_colecao TEXT,
    tipo_colecao VARCHAR(255) NOT NULL
);

CREATE TABLE tb_carta (
    id_carta INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(255) NOT NULL,
    raridade VARCHAR(100),
    tipo VARCHAR(100),
    vida INT,
    mana INT,
    energia INT,
    imagem VARCHAR(255) NOT NULL,
    descricao VARCHAR(255) NOT NULL,
	id_colecao INT,
    FOREIGN KEY (id_colecao) REFERENCES tb_colecao(id_colecao)
);

CREATE TABLE tb_itens_loja (
    id INT NOT NULL AUTO_INCREMENT,        -- ID do item
    nome VARCHAR(100) NOT NULL,            -- Nome do item
    descricao TEXT,                        -- Descrição detalhada do item
    preco INT NOT NULL,                    -- Preço do item
    tipo VARCHAR(50) NOT NULL,             -- Tipo do item (ex: carta, roleta, etc.)
    imagem VARCHAR(255) DEFAULT NULL,      -- URL ou caminho da imagem do item (pode ser NULL)
    PRIMARY KEY (id)
);


CREATE TABLE tb_logs_jogos (
    id INT NOT NULL AUTO_INCREMENT,              -- ID do log
    user_id INT NOT NULL,                        -- ID do usuário que fez a aposta
    aposta INT NOT NULL,                         -- Valor da aposta
    ganhou TINYINT(1) NOT NULL,                  -- Indica se o usuário ganhou (1 para sim, 0 para não)
    valor_ganho INT NOT NULL,                    -- Valor ganho pelo usuário (se ganhou)
    timestamp TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,  -- Data e hora do log (padrão para o momento da inserção)
    linha_ganhadora INT NOT NULL DEFAULT 0,      -- Linha ganhadora (0 por padrão se não houver)
    PRIMARY KEY (id),
    KEY user_id (user_id),                      -- Índice para o campo user_id
    CONSTRAINT fk_usuario FOREIGN KEY (user_id) REFERENCES tb_usuario (id_user)  -- Chave estrangeira para a tabela de usuários
);

CREATE TABLE tb_premios (
    id_premio INT NOT NULL AUTO_INCREMENT,    -- ID único do prêmio
    nome VARCHAR(255) NOT NULL,               -- Nome do prêmio
    descricao TEXT,                           -- Descrição detalhada do prêmio
    tipo VARCHAR(100) NOT NULL,               -- Tipo de prêmio (ex: "Item", "Moeda", "Benefício")
    valor INT NOT NULL,                       -- Valor do prêmio (ex: valor em moeda, ou id do item)
    quantidade INT NOT NULL,                  -- Quantidade disponível do prêmio
    imagem VARCHAR(255) DEFAULT NULL,         -- URL ou caminho da imagem representando o prêmio
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,  -- Data de criação do prêmio
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',   -- Status do prêmio (se está ativo ou não)
    PRIMARY KEY (id_premio)                   -- Chave primária
);

CREATE  TABLE tb_usuarios_itens (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,                  -- ID do usuário
    item_id INT NOT NULL,                  -- ID do item comprado
    tipo_item VARCHAR(50) NOT NULL,        -- Tipo do item
    data_compra TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP, -- Data da compra (padrão para o momento da inserção)
--     KEY user_id (user_id),                 -- Índice para o campo user_id
--     KEY item_id (item_id),                -- Índice para o campo item_id
    CONSTRAINT fk_item FOREIGN KEY (item_id) REFERENCES tb_itens_loja (id),    -- Chave estrangeira para a tabela itens_loja
    CONSTRAINT fk_item_user FOREIGN KEY (user_id) REFERENCES tb_usuario (id_user)    -- Chave estrangeira para a tabela itens_loja
);


SELECT u.nome_usuario, u.energia, u.moedas, u.avatar_id, i.user_id,  i.item_id, i.tipo_item  FROM tb_usuario as u inner join tb_usuarios_itens as i WHERE u.nome_usuario =  'jogador01'
#user
INSERT INTO tb_usuario (nome_usuario, senha, email, energia, moedas, avatar_id)
VALUES ('jogador01', 'senha_segura', 'jogador01@email.com', 100, 50, NULL);

select * from tb_usuario
###Coleçõa
INSERT INTO tb_colecao (nome_colecao, descricao_colecao, tipo_colecao)
VALUES ('Natureza', 'Coleção de cartas relacionadas ao mundo animal e criaturas místicas da natureza.', 'Animal e Místico');

#Carta 
-- Inserindo as cartas da coleção "Natureza"
INSERT INTO tb_carta (nome, raridade, tipo, vida, mana, energia, imagem, descricao, id_colecao)
VALUES
('Macaco', 'Comum', 'Animal', 50, 30, 20, 'macaco.png', 'Carta que representa o macaco, um animal ágil e inteligente.', 1),
('Gorila', 'Raro', 'Animal', 100, 50, 30, 'gorila.png', 'Gorila, um animal forte e imponente.', 1),
('Mago', 'Épico', 'Mágico', 70, 100, 50, 'mago.png', 'Mago que controla elementos da natureza para atacar seus inimigos.', 1),
('Mosca', 'Comum', 'Insecto', 10, 10, 5, 'mosca.png', 'Carta que representa uma mosca, pequena, mas ágil.', 1),
('Capivara', 'Comum', 'Animal', 60, 20, 10, 'capivara.png', 'A capivara, um animal tranquilo e amigável.', 1);

##Loja
INSERT INTO tb_itens_loja (nome, descricao, preco, tipo, imagem)
VALUES
('Mais Energia', 'Adiciona 50 pontos de energia ao jogador.', 30, 'Consumível', 'mais_energia.png'),
('Comprar Moeda', 'Permite ao jogador ganhar 100 moedas.', 50, 'Consumível', 'comprar_dinheiro.png'),
('Pacote de Cartas Comum', 'Pacote contendo 3 cartas comuns aleatórias.', 150, 'Pacote', 'pacote_comum.png'),
('Pacote de Cartas Raro', 'Pacote contendo 3 cartas raras aleatórias.', 500, 'Pacote', 'pacote_raro.png'),
('Carta Macaco', 'Uma carta do Macaco, da coleção Natureza.', 100, 'Carta', 'carta_macaco.png');


-- Associando a carta "Macaco" (id_item = 1) à coleção do usuário (id_user = 1)
INSERT INTO tb_usuarios_itens (user_id, item_id, tipo_item)
VALUES (1, 1, 'Carta');

-- Associando a carta "Gorila" (id_item = 2) à coleção do usuário (id_user = 1)
INSERT INTO tb_usuarios_itens (user_id, item_id, tipo_item)
VALUES (1, 2, 'Carta');

-- Associando o pacote de cartas comum (id_item = 3) ao usuário (id_user = 1)
INSERT INTO tb_usuarios_itens (user_id, item_id, tipo_item)
VALUES (1, 3, 'Pacote de Cartas');



select card.nome, card.raridade, card.tipo, card.vida, card.mana, card.energia, card.imagem, card.descricao, col.nome from tb_carta as card inner join	tb_colecao as col ON card.id_colecao = col.id_colecao where 


select card.nome, card.raridade, card.tipo, col.nome_colecao, itens_user.item_id, itens_user.user_id, itens_user.tipo_item  from tb_carta as card 
	inner join tb_colecao as col ON card.id_colecao = col.id_colecao 
	inner join tb_usuarios_itens as itens_user ON card.id_carta = itens_user.item_id 
    where itens_user.tipo_item = 'Carta'
    
   
    
select card.id_carta, card.nome, card.raridade, card.tipo, card.vida, card.mana, card.energia, card.imagem, card.descricao, 
	col.nome_colecao, col.tipo_colecao, 
    IF(itens_user.tipo_item='carta' AND itens_user.item_id = card.id_carta AND itens_user.user_id = (Select id_user from tb_usuario where nome_usuario="luchas"), TRUE, FALSE) AS 'hasCard' from tb_carta as card 
left join tb_usuarios_itens as itens_user ON card.id_carta = itens_user.item_id 	
inner join tb_colecao as col ON card.id_colecao = col.id_colecao; 



