DROP DATABASE IF EXISTS freelaja;

CREATE DATABASE freelaja
USE freelaja;

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

) ;

CREATE TABLE categoria (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,

    nome VARCHAR(100) NOT NULL UNIQUE

) ;

CREATE TABLE habilidade (
    id_habilidade INT AUTO_INCREMENT PRIMARY KEY,

    nome VARCHAR(100) NOT NULL UNIQUE

) ;

CREATE TABLE usuario_habilidade (

    id_usuario INT NOT NULL,
    id_habilidade INT NOT NULL,

    PRIMARY KEY (
        id_usuario,
        id_habilidade
    ),

    CONSTRAINT fk_usuario_habilidade_usuario
        FOREIGN KEY (id_usuario)
        REFERENCES usuario(id_usuario)
        ON DELETE CASCADE,

    CONSTRAINT fk_usuario_habilidade_habilidade
        FOREIGN KEY (id_habilidade)
        REFERENCES habilidade(id_habilidade)
        ON DELETE CASCADE

) ;

CREATE TABLE vaga (

    id_vaga INT AUTO_INCREMENT PRIMARY KEY,
    id_contratante INT NOT NULL,
    id_categoria INT NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    descricao TEXT NOT NULL,
    cidade VARCHAR(100) NOT NULL,
    remuneracao DECIMAL(10,2) NULL,
    data_publicacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_limite DATE NULL,
    trabalhadores_limite INT NULL,

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

) ;

CREATE TABLE interesse (

    id_interesse INT AUTO_INCREMENT PRIMARY KEY,
    id_vaga INT NOT NULL,
    id_trabalhador INT NOT NULL,
    data_interesse DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_interesse_vaga
        FOREIGN KEY (id_vaga)
        REFERENCES vaga(id_vaga)
        ON DELETE CASCADE,

    CONSTRAINT fk_interesse_trabalhador
        FOREIGN KEY (id_trabalhador)
        REFERENCES usuario(id_usuario)
        ON DELETE CASCADE,

    UNIQUE KEY uk_interesse (
        id_vaga,
        id_trabalhador
    )

) ;

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

) ;

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

INSERT INTO usuario (
    nome,
    email,
    senha,
    telefone,
    cidade,
    tipo_usuario,
    ativo
)
VALUES
(
    'Administrador',
    'admin@freelaja.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    '(46)99999-0001',
    'Dois Vizinhos',
    'ADMIN',
    1
),
(
    'João Trabalhador',
    'trabalhador@freelaja.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    '(46)99999-0002',
    'Dois Vizinhos',
    'TRABALHADOR',
    1
),
(
    'Empresa Exemplo',
    'contratante@freelaja.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    '(46)99999-0003',
    'Dois Vizinhos',
    'CONTRATANTE',
    1
);

INSERT INTO usuario_habilidade (
    id_usuario,
    id_habilidade
)
VALUES
(2,1),
(2,2),
(2,5);

INSERT INTO vaga (
    id_contratante,
    id_categoria,
    titulo,
    descricao,
    cidade,
    remuneracao,
    data_limite
)
VALUES
(
    3,
    2,
    'Garçom para Evento',
    'Necessário atuar em evento no sábado à noite.',
    'Dois Vizinhos',
    250.00,
    DATE_ADD(CURDATE(), INTERVAL 15 DAY)
);

INSERT INTO interesse (
    id_vaga,
    id_trabalhador
)
VALUES
(
    1,
    2
);

INSERT INTO denuncia (
    id_denunciante,
    id_vaga_denunciada,
    motivo,
    descricao
)
VALUES
(
    2,
    1,
    'Informação incorreta',
    'A descrição da vaga contém informações inconsistentes.'
);