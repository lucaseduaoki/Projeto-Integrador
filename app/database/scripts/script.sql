-- ============================================================================
-- PLATAFORMA FREELAJA - BANCO DE DADOS
-- ============================================================================

CREATE TABLE IF NOT EXISTS usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(20),
    tipo_usuario ENUM('ADMIN', 'TRABALHADOR', 'CONTRATANTE') NOT NULL,
    foto_perfil VARCHAR(255),
    descricao TEXT,
    documento VARCHAR(25),
    ativo TINYINT(1) DEFAULT 1,
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS empresa (
    id_perfil INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    nome_fantasia VARCHAR(100),
    localizacao VARCHAR(150),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS habilidade (
    id_habilidade INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS usuario_habilidade (
    id_usuario INT,
    id_habilidade INT,
    PRIMARY KEY (id_usuario, id_habilidade),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_habilidade) REFERENCES habilidade(id_habilidade) ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS anuncio (
    id_anuncio INT AUTO_INCREMENT PRIMARY KEY,
    id_contratante INT NOT NULL,
    titulo VARCHAR(100) NOT NULL,
    descricao TEXT NOT NULL,
    localizacao VARCHAR(150),
    data DATETIME,
    remuneracao DECIMAL(10,2),
    tipo_servico ENUM('TEMPORARIO', 'FIXO'),
    duracao VARCHAR(50),
    observacoes TEXT,
    prazo_candidatura DATE,
    status ENUM('ABERTO', 'ENCERRADO') DEFAULT 'ABERTO',
    FOREIGN KEY (id_contratante) REFERENCES usuario(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS candidatura (
    id_candidatura INT AUTO_INCREMENT PRIMARY KEY,
    id_anuncio INT NOT NULL,
    id_trabalhador INT NOT NULL,
    data_candidatura DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('PENDENTE', 'ACEITO', 'RECUSADO') DEFAULT 'PENDENTE',
    data_selecao DATETIME NULL,
    FOREIGN KEY (id_anuncio) REFERENCES anuncio(id_anuncio) ON DELETE CASCADE,
    FOREIGN KEY (id_trabalhador) REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    UNIQUE (id_anuncio, id_trabalhador)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS avaliacao (
    id_avaliacao INT AUTO_INCREMENT PRIMARY KEY,
    id_avaliador INT NOT NULL,
    id_avaliado INT NOT NULL,
    id_anuncio INT NOT NULL,
    nota INT CHECK (nota BETWEEN 1 AND 5),
    comentario TEXT,
    data DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_avaliador) REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_avaliado) REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_anuncio) REFERENCES anuncio(id_anuncio) ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS denuncia (
    id_denuncia INT AUTO_INCREMENT PRIMARY KEY,
    id_denunciante INT NOT NULL,
    id_denunciado INT NOT NULL,
    id_anuncio INT NULL,
    motivo VARCHAR(255) NOT NULL,
    descricao TEXT,
    data DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('PENDENTE', 'ANALISADO') DEFAULT 'PENDENTE',
    FOREIGN KEY (id_denunciante) REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_denunciado) REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_anuncio) REFERENCES anuncio(id_anuncio) ON DELETE SET NULL
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ============================================================================
-- USUARIOS PADRAO (SEED)
-- ============================================================================
INSERT INTO usuario (nome, email, senha, tipo_usuario, ativo)
VALUES
    ('trabalhador', 'trabalhador@gmail.com', '$2y$12$ntZsjgr5ay7klFmPYV2MW.qfTIkCoTLgpCPkDAYzeLteFLo4FoNN6', 'TRABALHADOR', 1),
    ('contratante', 'contratante@gmail.com', '$2y$12$NQVvBL.bKV8ayaZBvZKEwueIjHeGntGYNvV66qE9lPfOuEBwXCTw6', 'CONTRATANTE', 1),
    ('admin', 'admin@gmail.com', '$2y$12$b2l.DF2X/7IHHSErEAZd2.E126Srxh0OItJWd4HUvl.8JPyQu7S9u', 'ADMIN', 1)
ON DUPLICATE KEY UPDATE
    nome = VALUES(nome),
    senha = VALUES(senha),
    tipo_usuario = VALUES(tipo_usuario),
    ativo = VALUES(ativo);

-- ============================================================================
-- DADOS DE EXEMPLO
-- ============================================================================
INSERT INTO habilidade (nome)
SELECT 'PHP' WHERE NOT EXISTS (SELECT 1 FROM habilidade WHERE nome = 'PHP');

INSERT INTO habilidade (nome)
SELECT 'MySQL' WHERE NOT EXISTS (SELECT 1 FROM habilidade WHERE nome = 'MySQL');

INSERT INTO habilidade (nome)
SELECT 'Docker' WHERE NOT EXISTS (SELECT 1 FROM habilidade WHERE nome = 'Docker');

INSERT INTO habilidade (nome)
SELECT 'HTML/CSS' WHERE NOT EXISTS (SELECT 1 FROM habilidade WHERE nome = 'HTML/CSS');

INSERT INTO empresa (id_usuario, nome_fantasia, localizacao)
SELECT u.id_usuario, 'FreelaJA Solucoes', 'São Paulo - SP'
FROM usuario u
WHERE u.email = 'contratante@gmail.com'
    AND NOT EXISTS (
            SELECT 1
            FROM empresa e
            WHERE e.id_usuario = u.id_usuario
    );

INSERT INTO usuario_habilidade (id_usuario, id_habilidade)
SELECT u.id_usuario, h.id_habilidade
FROM usuario u
JOIN habilidade h ON h.nome = 'PHP'
WHERE u.email = 'trabalhador@gmail.com'
    AND NOT EXISTS (
            SELECT 1
            FROM usuario_habilidade uh
            WHERE uh.id_usuario = u.id_usuario AND uh.id_habilidade = h.id_habilidade
    );

INSERT INTO usuario_habilidade (id_usuario, id_habilidade)
SELECT u.id_usuario, h.id_habilidade
FROM usuario u
JOIN habilidade h ON h.nome = 'MySQL'
WHERE u.email = 'trabalhador@gmail.com'
    AND NOT EXISTS (
            SELECT 1
            FROM usuario_habilidade uh
            WHERE uh.id_usuario = u.id_usuario AND uh.id_habilidade = h.id_habilidade
    );

INSERT INTO usuario_habilidade (id_usuario, id_habilidade)
SELECT u.id_usuario, h.id_habilidade
FROM usuario u
JOIN habilidade h ON h.nome = 'Docker'
WHERE u.email = 'trabalhador@gmail.com'
    AND NOT EXISTS (
            SELECT 1
            FROM usuario_habilidade uh
            WHERE uh.id_usuario = u.id_usuario AND uh.id_habilidade = h.id_habilidade
    );

INSERT INTO anuncio (id_contratante, titulo, descricao, localizacao, data, remuneracao, tipo_servico, duracao, observacoes, prazo_candidatura, status)
SELECT
        u.id_usuario,
        'Desenvolvedor PHP Junior',
        'Projeto para manutenção de sistema web em PHP com MVC e MySQL.',
        'São Paulo - SP',
        '2026-05-10 09:00:00',
        2500.00,
        'FIXO',
        '3 meses',
        'Conhecimento básico em Git é um diferencial.',
        '2026-06-15',
        'ABERTO'
FROM usuario u
WHERE u.email = 'contratante@gmail.com'
    AND NOT EXISTS (
            SELECT 1
            FROM anuncio a
            WHERE a.titulo = 'Desenvolvedor PHP Junior' AND a.id_contratante = u.id_usuario
    );

INSERT INTO anuncio (id_contratante, titulo, descricao, localizacao, data, remuneracao, tipo_servico, duracao, observacoes, prazo_candidatura, status)
SELECT
        u.id_usuario,
        'Suporte para landing page',
        'Ajustes visuais e responsividade em uma landing page institucional.',
        'Remoto',
        '2026-05-11 14:30:00',
        1200.00,
        'TEMPORARIO',
        '2 semanas',
        'Entrega rápida e comunicação assíncrona.',
        '2026-06-01',
        'ABERTO'
FROM usuario u
WHERE u.email = 'contratante@gmail.com'
    AND NOT EXISTS (
            SELECT 1
            FROM anuncio a
            WHERE a.titulo = 'Suporte para landing page' AND a.id_contratante = u.id_usuario
    );

INSERT INTO candidatura (id_anuncio, id_trabalhador, data_candidatura, status, data_selecao)
SELECT a.id_anuncio, u.id_usuario, '2026-05-12 10:00:00', 'PENDENTE', NULL
FROM anuncio a
JOIN usuario u ON u.email = 'trabalhador@gmail.com'
WHERE a.titulo = 'Desenvolvedor PHP Junior'
    AND NOT EXISTS (
            SELECT 1
            FROM candidatura c
            WHERE c.id_anuncio = a.id_anuncio AND c.id_trabalhador = u.id_usuario
    );

INSERT INTO candidatura (id_anuncio, id_trabalhador, data_candidatura, status, data_selecao)
SELECT a.id_anuncio, u.id_usuario, '2026-05-12 11:00:00', 'ACEITO', '2026-05-12 12:00:00'
FROM anuncio a
JOIN usuario u ON u.email = 'trabalhador@gmail.com'
WHERE a.titulo = 'Suporte para landing page'
    AND NOT EXISTS (
            SELECT 1
            FROM candidatura c
            WHERE c.id_anuncio = a.id_anuncio AND c.id_trabalhador = u.id_usuario
    );

INSERT INTO avaliacao (id_avaliador, id_avaliado, id_anuncio, nota, comentario, data)
SELECT avaliado.id_usuario, trabalhador.id_usuario, a.id_anuncio, 5,
             'Ótimo trabalho, entrega organizada e dentro do prazo.',
             '2026-05-12 12:30:00'
FROM usuario avaliado
JOIN usuario trabalhador ON trabalhador.email = 'trabalhador@gmail.com'
JOIN anuncio a ON a.titulo = 'Suporte para landing page'
WHERE avaliado.email = 'contratante@gmail.com'
    AND NOT EXISTS (
            SELECT 1
            FROM avaliacao av
            WHERE av.id_avaliador = avaliado.id_usuario
                AND av.id_avaliado = trabalhador.id_usuario
                AND av.id_anuncio = a.id_anuncio
    );

INSERT INTO denuncia (id_denunciante, id_denunciado, id_anuncio, motivo, descricao, data, status)
SELECT denunciante.id_usuario, denunciado.id_usuario, NULL,
             'Informações falsas',
             'Dados de contato divergentes no perfil.',
             '2026-05-12 13:00:00',
             'PENDENTE'
FROM usuario denunciante
JOIN usuario denunciado ON denunciado.email = 'trabalhador@gmail.com'
WHERE denunciante.email = 'admin@gmail.com'
    AND NOT EXISTS (
            SELECT 1
            FROM denuncia d
            WHERE d.id_denunciante = denunciante.id_usuario
                AND d.id_denunciado = denunciado.id_usuario
                AND d.motivo = 'Informações falsas'
    );