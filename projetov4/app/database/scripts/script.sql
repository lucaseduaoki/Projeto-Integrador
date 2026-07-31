DROP DATABASE IF EXISTS freelajav4;
CREATE DATABASE freelajav4;
USE freelajav4;

-- ============================================================================
-- ESTRUTURA
-- ============================================================================

CREATE TABLE usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,

    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,

    telefone VARCHAR(20),
    cidade VARCHAR(100),

    foto_perfil VARCHAR(255),
    descricao TEXT,

    documento VARCHAR(20),

    tipo_usuario ENUM(
        'ADMIN',
        'TRABALHADOR',
        'CONTRATANTE'
    ) NOT NULL,

    ativo BOOLEAN DEFAULT TRUE,

    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE categoria (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE habilidade (
    id_habilidade INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE usuario_habilidade (

    id_usuario INT NOT NULL,
    id_habilidade INT NOT NULL,

    PRIMARY KEY (id_usuario, id_habilidade),

    CONSTRAINT fk_usuario_habilidade_usuario
        FOREIGN KEY (id_usuario)
        REFERENCES usuario(id_usuario)
        ON DELETE CASCADE,

    CONSTRAINT fk_usuario_habilidade_habilidade
        FOREIGN KEY (id_habilidade)
        REFERENCES habilidade(id_habilidade)
        ON DELETE CASCADE
);

CREATE TABLE vaga (

    id_vaga INT AUTO_INCREMENT PRIMARY KEY,

    id_contratante INT NOT NULL,
    id_categoria INT NOT NULL,

    titulo VARCHAR(150) NOT NULL,
    descricao TEXT NOT NULL,

    localizacao VARCHAR(100),

    remuneracao DECIMAL(10,2),

    data_publicacao DATETIME DEFAULT CURRENT_TIMESTAMP,

    data_limite DATE,

    trabalhadores_limite INT NOT NULL DEFAULT 1,

    status ENUM(
        'ATIVA',
        'ENCERRADA'
    ) DEFAULT 'ATIVA',

    CONSTRAINT fk_vaga_contratante
        FOREIGN KEY (id_contratante)
        REFERENCES usuario(id_usuario)
        ON DELETE CASCADE,

    CONSTRAINT fk_vaga_categoria
        FOREIGN KEY (id_categoria)
        REFERENCES categoria(id_categoria)
);

CREATE TABLE interesse (

    id_interesse INT AUTO_INCREMENT PRIMARY KEY,

    id_vaga INT NOT NULL,
    id_trabalhador INT NOT NULL,

    status ENUM(
        'PENDENTE',
        'ACEITO'
    ) DEFAULT 'PENDENTE',

    data_interesse DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_interesse_vaga
        FOREIGN KEY (id_vaga)
        REFERENCES vaga(id_vaga)
        ON DELETE CASCADE,

    CONSTRAINT fk_interesse_trabalhador
        FOREIGN KEY (id_trabalhador)
        REFERENCES usuario(id_usuario)
        ON DELETE CASCADE,

    CONSTRAINT uk_interesse
        UNIQUE(id_vaga, id_trabalhador)
);

CREATE TABLE denuncia (

    id_denuncia INT AUTO_INCREMENT PRIMARY KEY,

    id_denunciante INT NOT NULL,

    id_usuario_denunciado INT NULL,
    id_vaga_denunciada INT NULL,

    motivo VARCHAR(255) NOT NULL,
    descricao TEXT,

    status ENUM(
        'PENDENTE',
        'APROVADA',
        'REJEITADA'
    ) DEFAULT 'PENDENTE',

    data_denuncia DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_denuncia_denunciante
        FOREIGN KEY (id_denunciante)
        REFERENCES usuario(id_usuario)
        ON DELETE CASCADE,

    CONSTRAINT fk_denuncia_usuario
        FOREIGN KEY (id_usuario_denunciado)
        REFERENCES usuario(id_usuario)
        ON DELETE SET NULL,

    CONSTRAINT fk_denuncia_vaga
        FOREIGN KEY (id_vaga_denunciada)
        REFERENCES vaga(id_vaga)
        ON DELETE SET NULL
);

CREATE INDEX idx_vaga_status
ON vaga(status);

CREATE INDEX idx_vaga_contratante
ON vaga(id_contratante);

CREATE INDEX idx_interesse_vaga
ON interesse(id_vaga);

CREATE INDEX idx_interesse_trabalhador
ON interesse(id_trabalhador);

-- ============================================================================
-- DADOS DE APOIO
-- ============================================================================

INSERT INTO categoria (nome) VALUES
('Faxina'),
('Garçom'),
('Construção Civil'),
('Jardinagem'),
('Entregas'),
('Tecnologia'),
('Eventos'),
('Atendimento'),
('Manutenção'),
('Outros');

INSERT INTO habilidade (nome) VALUES
('Limpeza'),
('Atendimento ao Cliente'),
('Informática'),
('Pintura'),
('Jardinagem'),
('Entrega'),
('Digitação'),
('Manutenção'),
('Organização'),
('Comunicação');

-- ============================================================================
-- USUÁRIOS
-- senha para todos os usuários abaixo: "senha123"
-- hash gerado com password_hash('senha123', PASSWORD_BCRYPT)
-- ============================================================================

INSERT INTO usuario (
    nome, email, senha, telefone, cidade, tipo_usuario
)
VALUES
-- Admin
(
    'Administrador',
    'admin@freelaja.com',
    '$2y$10$RRt2sklIYVmb5/3eIxWZteqfQw1u4nK1GRD6vBX5PpN3QQY2Y8NJu',
    '(46)99999-0001',
    'Dois Vizinhos',
    'ADMIN'
),
-- Trabalhadores (id 2 a 6)
(
    'João Trabalhador',
    'trabalhador@freelaja.com',
    '$2y$10$SqYEb0QT6mZq2B8gRNjBwu0mVv6KmCTFt0SHBYvKDqLHB4ElIwP1K',
    '(46)99999-0002',
    'Dois Vizinhos',
    'TRABALHADOR'
),
(
    'Maria Souza',
    'maria.souza@freelaja.com',
    '$2y$10$SqYEb0QT6mZq2B8gRNjBwu0mVv6KmCTFt0SHBYvKDqLHB4ElIwP1K',
    '(46)99999-0004',
    'Dois Vizinhos',
    'TRABALHADOR'
),
(
    'Carlos Mendes',
    'carlos.mendes@freelaja.com',
    '$2y$10$SqYEb0QT6mZq2B8gRNjBwu0mVv6KmCTFt0SHBYvKDqLHB4ElIwP1K',
    '(46)99999-0005',
    'Pato Branco',
    'TRABALHADOR'
),
(
    'Fernanda Lima',
    'fernanda.lima@freelaja.com',
    '$2y$10$SqYEb0QT6mZq2B8gRNjBwu0mVv6KmCTFt0SHBYvKDqLHB4ElIwP1K',
    '(46)99999-0006',
    'Dois Vizinhos',
    'TRABALHADOR'
),
(
    'Ricardo Alves',
    'ricardo.alves@freelaja.com',
    '$2y$10$SqYEb0QT6mZq2B8gRNjBwu0mVv6KmCTFt0SHBYvKDqLHB4ElIwP1K',
    '(46)99999-0007',
    'Dois Vizinhos',
    'TRABALHADOR'
),
-- Contratantes (id 7 a 9)
(
    'Empresa Exemplo',
    'contratante@freelaja.com',
    '$2y$10$BiGy7skgADaQQedBYLQjUO/cWqrnqxtXVPDBqgWgKWQR32RYa0OB6',
    '(46)99999-0003',
    'Dois Vizinhos',
    'CONTRATANTE'
),
(
    'Restaurante Sabor Real',
    'contato@saborreal.com',
    '$2y$10$BiGy7skgADaQQedBYLQjUO/cWqrnqxtXVPDBqgWgKWQR32RYa0OB6',
    '(46)99999-0008',
    'Dois Vizinhos',
    'CONTRATANTE'
),
(
    'Condomínio Jardim das Flores',
    'sindico@jardimdasflores.com',
    '$2y$10$BiGy7skgADaQQedBYLQjUO/cWqrnqxtXVPDBqgWgKWQR32RYa0OB6',
    '(46)99999-0009',
    'Dois Vizinhos',
    'CONTRATANTE'
);

-- ============================================================================
-- HABILIDADES DOS TRABALHADORES
-- ============================================================================

INSERT INTO usuario_habilidade (id_usuario, id_habilidade) VALUES
(2, 1), (2, 2), (2, 5),   -- João: Limpeza, Atendimento, Jardinagem
(3, 1), (3, 9),           -- Maria: Limpeza, Organização
(4, 4), (4, 8),           -- Carlos: Pintura, Manutenção
(5, 2), (5, 10),          -- Fernanda: Atendimento, Comunicação
(6, 6), (6, 3);           -- Ricardo: Entrega, Informática

-- ============================================================================
-- VAGAS
-- ============================================================================

INSERT INTO vaga (
    id_contratante, id_categoria, titulo, descricao,
    localizacao, remuneracao, data_limite, trabalhadores_limite, status
)
VALUES
-- Vaga 1: precisa de 2 garçons, ainda ATIVA com 1 aceito e 1 pendente
(
    8, 2,
    'Garçom para Evento',
    'Necessário atuar em evento no sábado à noite.',
    'Dois Vizinhos', 250.00,
    DATE_ADD(CURDATE(), INTERVAL 15 DAY),
    2, 'ATIVA'
),
-- Vaga 2: precisava de 1 faxineira, já ENCERRADA (aceito atingiu o limite)
(
    9, 1,
    'Faxina Pós-Obra',
    'Limpeza pesada em apartamento recém reformado.',
    'Dois Vizinhos', 180.00,
    DATE_ADD(CURDATE(), INTERVAL 5 DAY),
    1, 'ENCERRADA'
),
-- Vaga 3: precisa de 3 pintores, ATIVA, só interesses pendentes ainda
(
    7, 3,
    'Pintura de Muro Comercial',
    'Pintura externa de muro de aproximadamente 40 metros.',
    'Dois Vizinhos', 600.00,
    DATE_ADD(CURDATE(), INTERVAL 10 DAY),
    3, 'ATIVA'
),
-- Vaga 4: vaga de entrega, ATIVA, sem nenhum interesse ainda
(
    7, 5,
    'Entregador para Fim de Semana',
    'Entregas de pequeno porte na região central.',
    'Dois Vizinhos', 150.00,
    DATE_ADD(CURDATE(), INTERVAL 20 DAY),
    1, 'ATIVA'
),
-- Vaga 5: sem prazo definido (data_limite NULL), ATIVA
(
    8, 8,
    'Atendente de Balcão',
    'Cobertura de folga de atendente por alguns dias.',
    'Dois Vizinhos', 200.00,
    NULL,
    1, 'ATIVA'
);

-- ============================================================================
-- INTERESSES
-- ============================================================================

INSERT INTO interesse (id_vaga, id_trabalhador, status) VALUES
-- Vaga 1 (limite 2): 1 aceito + 1 pendente -> continua ATIVA até aceitar mais 1
(1, 2, 'ACEITO'),
(1, 5, 'PENDENTE'),
-- Vaga 2 (limite 1): 1 aceito -> bateu o limite, por isso já está ENCERRADA
(2, 3, 'ACEITO'),
-- Vaga 3 (limite 3): só pendentes ainda, nenhum aceito
(3, 4, 'PENDENTE'),
(3, 6, 'PENDENTE'),
-- Vaga 5 (limite 1): 1 pendente, aguardando aceite do contratante
(5, 2, 'PENDENTE');

-- ============================================================================
-- DENÚNCIAS
-- ============================================================================

INSERT INTO denuncia (
    id_denunciante, id_vaga_denunciada, motivo, descricao, status
)
VALUES
(
    2, 1,
    'Informação incorreta',
    'A descrição da vaga contém informações inconsistentes.',
    'PENDENTE'
),
(
    4, 3,
    'Vaga suspeita',
    'Remuneração parece incompatível com o serviço descrito.',
    'REJEITADA'
);