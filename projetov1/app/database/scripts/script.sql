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

-- ==========================================================================
-- Seed adicional completo (consolidado)
-- Usa os mesmos 3 usuários padrão e popula demais tabelas com dados coerentes
-- ==========================================================================

SET @now := NOW();

-- Habilidades adicionais (idempotente)
INSERT INTO habilidade (nome)
SELECT 'JavaScript' WHERE NOT EXISTS (SELECT 1 FROM habilidade WHERE nome = 'JavaScript');
INSERT INTO habilidade (nome)
SELECT 'React' WHERE NOT EXISTS (SELECT 1 FROM habilidade WHERE nome = 'React');
INSERT INTO habilidade (nome)
SELECT 'Node.js' WHERE NOT EXISTS (SELECT 1 FROM habilidade WHERE nome = 'Node.js');
INSERT INTO habilidade (nome)
SELECT 'Python' WHERE NOT EXISTS (SELECT 1 FROM habilidade WHERE nome = 'Python');
INSERT INTO habilidade (nome)
SELECT 'Django' WHERE NOT EXISTS (SELECT 1 FROM habilidade WHERE nome = 'Django');
INSERT INTO habilidade (nome)
SELECT 'Flask' WHERE NOT EXISTS (SELECT 1 FROM habilidade WHERE nome = 'Flask');
INSERT INTO habilidade (nome)
SELECT 'DevOps' WHERE NOT EXISTS (SELECT 1 FROM habilidade WHERE nome = 'DevOps');
INSERT INTO habilidade (nome)
SELECT 'AWS' WHERE NOT EXISTS (SELECT 1 FROM habilidade WHERE nome = 'AWS');
INSERT INTO habilidade (nome)
SELECT 'Azure' WHERE NOT EXISTS (SELECT 1 FROM habilidade WHERE nome = 'Azure');
INSERT INTO habilidade (nome)
SELECT 'Android' WHERE NOT EXISTS (SELECT 1 FROM habilidade WHERE nome = 'Android');
INSERT INTO habilidade (nome)
SELECT 'iOS' WHERE NOT EXISTS (SELECT 1 FROM habilidade WHERE nome = 'iOS');
INSERT INTO habilidade (nome)
SELECT 'UI/UX' WHERE NOT EXISTS (SELECT 1 FROM habilidade WHERE nome = 'UI/UX');
INSERT INTO habilidade (nome)
SELECT 'Figma' WHERE NOT EXISTS (SELECT 1 FROM habilidade WHERE nome = 'Figma');
INSERT INTO habilidade (nome)
SELECT 'Marketing Digital' WHERE NOT EXISTS (SELECT 1 FROM habilidade WHERE nome = 'Marketing Digital');
INSERT INTO habilidade (nome)
SELECT 'SEO' WHERE NOT EXISTS (SELECT 1 FROM habilidade WHERE nome = 'SEO');
INSERT INTO habilidade (nome)
SELECT 'Atendimento ao Cliente' WHERE NOT EXISTS (SELECT 1 FROM habilidade WHERE nome = 'Atendimento ao Cliente');
INSERT INTO habilidade (nome)
SELECT 'Git' WHERE NOT EXISTS (SELECT 1 FROM habilidade WHERE nome = 'Git');

-- Mapear múltiplas habilidades para o TRABALHADOR (garantir >=10)
INSERT INTO usuario_habilidade (id_usuario, id_habilidade)
SELECT u.id_usuario, h.id_habilidade
FROM usuario u
JOIN habilidade h ON h.nome IN ('PHP','MySQL','Docker','HTML/CSS','JavaScript','React','Node.js','Git','DevOps','AWS','Figma')
WHERE u.email = 'trabalhador@gmail.com'
    AND NOT EXISTS (
        SELECT 1 FROM usuario_habilidade uh WHERE uh.id_usuario = u.id_usuario AND uh.id_habilidade = h.id_habilidade
    );

-- Criar anúncios adicionais (se faltarem) pelo CONTRATANTE — 20 itens no total
INSERT INTO anuncio (id_contratante, titulo, descricao, localizacao, data, remuneracao, tipo_servico, duracao, observacoes, prazo_candidatura, status)
SELECT u.id_usuario, t.titulo, t.descricao, t.localizacao, t.data, t.remuneracao, t.tipo_servico, t.duracao, t.observacoes, t.prazo, t.status
FROM usuario u
JOIN (
    SELECT 'Projeto de integração API' AS titulo, 'Integração entre serviços internos e externos.' AS descricao, 'Remoto' AS localizacao, DATE_SUB(@now, INTERVAL 13 DAY) AS data, 1800.00 AS remuneracao, 'FIXO' AS tipo_servico, '4 semanas' AS duracao, 'Boas práticas de segurança.' AS observacoes, DATE_ADD(DATE_SUB(@now, INTERVAL 13 DAY), INTERVAL 20 DAY) AS prazo, 'ABERTO' AS status
    UNION ALL SELECT 'Correção layout e acessibilidade', 'Ajustes de acessibilidade e SEO.', 'Remoto', DATE_SUB(@now, INTERVAL 12 DAY), 700.00, 'TEMPORARIO', '1 semana', 'Testes com leitores de tela.', DATE_ADD(DATE_SUB(@now, INTERVAL 12 DAY), INTERVAL 14 DAY), 'ABERTO'
    UNION ALL SELECT 'Campanha de e-mail marketing', 'Criação de sequências e templates.', 'Remoto', DATE_SUB(@now, INTERVAL 11 DAY), 900.00, 'TEMPORARIO', '2 semanas', 'Segmentação por público.', DATE_ADD(DATE_SUB(@now, INTERVAL 11 DAY), INTERVAL 18 DAY), 'ABERTO'
    UNION ALL SELECT 'Suporte técnico backend', 'Atendimento a chamados técnicos.', 'Remoto', DATE_SUB(@now, INTERVAL 9 DAY), 1200.00, 'TEMPORARIO', '1 mês', 'Prioridade em SLA.', DATE_ADD(DATE_SUB(@now, INTERVAL 9 DAY), INTERVAL 25 DAY), 'ABERTO'
    UNION ALL SELECT 'Animações SVG', 'Criar micro-interações em SVG para site.', 'Remoto', DATE_SUB(@now, INTERVAL 7 DAY), 800.00, 'TEMPORARIO', '1 semana', 'Entregar código limpo.', DATE_ADD(DATE_SUB(@now, INTERVAL 7 DAY), INTERVAL 14 DAY), 'ABERTO'
    UNION ALL SELECT 'Validação e testes', 'Escrever testes automatizados para API.', 'Remoto', DATE_SUB(@now, INTERVAL 6 DAY), 1600.00, 'FIXO', '3 semanas', 'Cobertura mínima 70%.', DATE_ADD(DATE_SUB(@now, INTERVAL 6 DAY), INTERVAL 30 DAY), 'ABERTO'
    UNION ALL SELECT 'Design de newsletter', 'Layout e templates responsivos para newsletter.', 'Remoto', DATE_SUB(@now, INTERVAL 4 DAY), 500.00, 'TEMPORARIO', '1 semana', 'Testar em principais clientes de e-mail.', DATE_ADD(DATE_SUB(@now, INTERVAL 4 DAY), INTERVAL 14 DAY), 'ABERTO'
    UNION ALL SELECT 'SEO técnico', 'Auditoria técnica de SEO e correções.', 'Remoto', DATE_SUB(@now, INTERVAL 3 DAY), 1100.00, 'TEMPORARIO', '2 semanas', 'Relatório e plano de ação.', DATE_ADD(DATE_SUB(@now, INTERVAL 3 DAY), INTERVAL 16 DAY), 'ABERTO'
    UNION ALL SELECT 'Prototype rápido', 'Protótipo navegável em Figma.', 'Remoto', DATE_SUB(@now, INTERVAL 2 DAY), 1300.00, 'TEMPORARIO', '2 semanas', 'Entregar protótipo interativo.', DATE_ADD(DATE_SUB(@now, INTERVAL 2 DAY), INTERVAL 18 DAY), 'ABERTO'
    UNION ALL SELECT 'Consultoria de produto', 'Sessões de descoberta e roadmap.', 'Remoto', DATE_SUB(@now, INTERVAL 1 DAY), 2000.00, 'TEMPORARIO', '2 semanas', 'Entregar apresentação executiva.', DATE_ADD(DATE_SUB(@now, INTERVAL 1 DAY), INTERVAL 20 DAY), 'ABERTO'
) t ON 1=1
WHERE u.email = 'contratante@gmail.com'
    AND NOT EXISTS (
        SELECT 1 FROM anuncio a WHERE a.titulo = t.titulo AND a.id_contratante = u.id_usuario
    );

-- Candidaturas adicionais (assegurar >=20) pelo TRABALHADOR
INSERT INTO candidatura (id_anuncio, id_trabalhador, data_candidatura, status, data_selecao)
SELECT a.id_anuncio, tr.id_usuario,
             DATE_ADD(a.data, INTERVAL (4 + (a.id_anuncio % 72)) HOUR) AS data_candidatura,
             CASE WHEN MOD(a.id_anuncio,3)=0 THEN 'ACEITO' WHEN MOD(a.id_anuncio,3)=1 THEN 'RECUSADO' ELSE 'PENDENTE' END AS status,
             CASE WHEN MOD(a.id_anuncio,3)=0 THEN DATE_ADD(a.data, INTERVAL (6 + (a.id_anuncio % 24)) HOUR) ELSE NULL END AS data_selecao
FROM anuncio a
JOIN usuario tr ON tr.email = 'trabalhador@gmail.com'
WHERE NOT EXISTS (
        SELECT 1 FROM candidatura c WHERE c.id_anuncio = a.id_anuncio AND c.id_trabalhador = tr.id_usuario
    )
LIMIT 20;

-- Avaliações adicionais para atingir >=20
INSERT INTO avaliacao (id_avaliador, id_avaliado, id_anuncio, nota, comentario, data)
SELECT c.id_trabalhador AS id_avaliador, c_anuncio.id_contratante AS id_avaliado, c.id_anuncio,
             ((MOD(c.id_anuncio,5) + 3) % 5) + 1 AS nota,
             'Avaliação complementar de rotina.', DATE_ADD(c.data_candidatura, INTERVAL 36 HOUR)
FROM candidatura c
JOIN anuncio c_anuncio ON c.id_anuncio = c_anuncio.id_anuncio
WHERE NOT EXISTS (
    SELECT 1 FROM avaliacao av WHERE av.id_avaliador = c.id_trabalhador AND av.id_avaliado = c_anuncio.id_contratante AND av.id_anuncio = c.id_anuncio
)
LIMIT 20;

-- Denúncias extras (admin) para completar >=20 caso necessário
INSERT INTO denuncia (id_denunciante, id_denunciado, id_anuncio, motivo, descricao, data, status)
SELECT adm.id_usuario, alvo.id_usuario, a.id_anuncio,
             CONCAT('Relato adicional ', a.id_anuncio), 'Denúncia automática para enriquecimento de dados.', DATE_ADD(a.data, INTERVAL 5 HOUR), 'PENDENTE'
FROM usuario adm
JOIN anuncio a ON a.id_contratante = (SELECT id_usuario FROM usuario WHERE email = 'contratante@gmail.com')
JOIN usuario alvo ON alvo.email IN ('trabalhador@gmail.com','contratante@gmail.com')
WHERE adm.email = 'admin@gmail.com'
    AND NOT EXISTS (
        SELECT 1 FROM denuncia d WHERE d.id_denunciante = adm.id_usuario AND d.id_denunciado = alvo.id_usuario AND d.id_anuncio = a.id_anuncio AND d.motivo = CONCAT('Relato adicional ', a.id_anuncio)
    )
LIMIT 20;

-- Observação: as inserções usam NOT EXISTS e LIMITs para evitar duplicação e garantir quantidades mínimas.

-- ============================================================================
-- NOVOS USUARIOS, EMPRESAS E DADOS RELACIONADOS
-- ============================================================================

-- Novos TRABALHADORES com perfis variados
INSERT INTO usuario (nome, email, senha, tipo_usuario, telefone, descricao, ativo)
SELECT 'João Silva', 'joao.silva@gmail.com', '$2y$12$ntZsjgr5ay7klFmPYV2MW.qfTIkCoTLgpCPkDAYzeLteFLo4FoNN6', 'TRABALHADOR', '11987654321', 'Desenvolvedor full-stack com 5 anos de experiência em PHP e JavaScript', 1
WHERE NOT EXISTS (SELECT 1 FROM usuario WHERE email = 'joao.silva@gmail.com');

INSERT INTO usuario (nome, email, senha, tipo_usuario, telefone, descricao, ativo)
SELECT 'Maria Santos', 'maria.santos@gmail.com', '$2y$12$ntZsjgr5ay7klFmPYV2MW.qfTIkCoTLgpCPkDAYzeLteFLo4FoNN6', 'TRABALHADOR', '11998765432', 'Designer UX/UI com expertise em Figma e prototipagem', 1
WHERE NOT EXISTS (SELECT 1 FROM usuario WHERE email = 'maria.santos@gmail.com');

INSERT INTO usuario (nome, email, senha, tipo_usuario, telefone, descricao, ativo)
SELECT 'Carlos Oliveira', 'carlos.oliveira@gmail.com', '$2y$12$ntZsjgr5ay7klFmPYV2MW.qfTIkCoTLgpCPkDAYzeLteFLo4FoNN6', 'TRABALHADOR', '11991234567', 'Especialista em DevOps e infraestrutura cloud AWS', 1
WHERE NOT EXISTS (SELECT 1 FROM usuario WHERE email = 'carlos.oliveira@gmail.com');

INSERT INTO usuario (nome, email, senha, tipo_usuario, telefone, descricao, ativo)
SELECT 'Ana Costa', 'ana.costa@gmail.com', '$2y$12$ntZsjgr5ay7klFmPYV2MW.qfTIkCoTLgpCPkDAYzeLteFLo4FoNN6', 'TRABALHADOR', '11992345678', 'Especialista em marketing digital e SEO com 3 anos de experiência', 1
WHERE NOT EXISTS (SELECT 1 FROM usuario WHERE email = 'ana.costa@gmail.com');

INSERT INTO usuario (nome, email, senha, tipo_usuario, telefone, descricao, ativo)
SELECT 'Roberto Mendes', 'roberto.mendes@gmail.com', '$2y$12$ntZsjgr5ay7klFmPYV2MW.qfTIkCoTLgpCPkDAYzeLteFLo4FoNN6', 'TRABALHADOR', '11993456789', 'Desenvolvedor backend em Python e Django com 4 anos no mercado', 1
WHERE NOT EXISTS (SELECT 1 FROM usuario WHERE email = 'roberto.mendes@gmail.com');

INSERT INTO usuario (nome, email, senha, tipo_usuario, telefone, descricao, ativo)
SELECT 'Fernanda Lima', 'fernanda.lima@gmail.com', '$2y$12$ntZsjgr5ay7klFmPYV2MW.qfTIkCoTLgpCPkDAYzeLteFLo4FoNN6', 'TRABALHADOR', '11994567890', 'Desenvolvedora frontend em React e TypeScript', 1
WHERE NOT EXISTS (SELECT 1 FROM usuario WHERE email = 'fernanda.lima@gmail.com');

INSERT INTO usuario (nome, email, senha, tipo_usuario, telefone, descricao, ativo)
SELECT 'Lucas Gomes', 'lucas.gomes@gmail.com', '$2y$12$ntZsjgr5ay7klFmPYV2MW.qfTIkCoTLgpCPkDAYzeLteFLo4FoNN6', 'TRABALHADOR', '11995678901', 'QA e Testes automatizados com conhecimento em Selenium', 1
WHERE NOT EXISTS (SELECT 1 FROM usuario WHERE email = 'lucas.gomes@gmail.com');

-- Novos CONTRATANTES (Empresas)
INSERT INTO usuario (nome, email, senha, tipo_usuario, telefone, descricao, ativo)
SELECT 'Tech Solutions', 'tech.solutions@empresa.com', '$2y$12$NQVvBL.bKV8ayaZBvZKEwueIjHeGntGYNvV66qE9lPfOuEBwXCTw6', 'CONTRATANTE', '1133334444', 'Empresa de desenvolvimento de software especializada em soluções web', 1
WHERE NOT EXISTS (SELECT 1 FROM usuario WHERE email = 'tech.solutions@empresa.com');

INSERT INTO usuario (nome, email, senha, tipo_usuario, telefone, descricao, ativo)
SELECT 'Digital Marketing Pro', 'digital.marketing.pro@empresa.com', '$2y$12$NQVvBL.bKV8ayaZBvZKEwueIjHeGntGYNvV66qE9lPfOuEBwXCTw6', 'CONTRATANTE', '1133335555', 'Agência especializada em campanhas digitais e marketing', 1
WHERE NOT EXISTS (SELECT 1 FROM usuario WHERE email = 'digital.marketing.pro@empresa.com');

INSERT INTO usuario (nome, email, senha, tipo_usuario, telefone, descricao, ativo)
SELECT 'Cloud Innovators', 'cloud.innovators@empresa.com', '$2y$12$NQVvBL.bKV8ayaZBvZKEwueIjHeGntGYNvV66qE9lPfOuEBwXCTw6', 'CONTRATANTE', '1133336666', 'Empresa focada em infraestrutura cloud e transformação digital', 1
WHERE NOT EXISTS (SELECT 1 FROM usuario WHERE email = 'cloud.innovators@empresa.com');

INSERT INTO usuario (nome, email, senha, tipo_usuario, telefone, descricao, ativo)
SELECT 'Creative Design Studio', 'creative.design@empresa.com', '$2y$12$NQVvBL.bKV8ayaZBvZKEwueIjHeGntGYNvV66qE9lPfOuEBwXCTw6', 'CONTRATANTE', '1133337777', 'Studio de design e criação de experiências digitais', 1
WHERE NOT EXISTS (SELECT 1 FROM usuario WHERE email = 'creative.design@empresa.com');

-- Empresas dos novos CONTRATANTES
INSERT INTO empresa (id_usuario, nome_fantasia, localizacao)
SELECT u.id_usuario, 'Tech Solutions Ltda', 'São Paulo - SP'
FROM usuario u
WHERE u.email = 'tech.solutions@empresa.com'
    AND NOT EXISTS (SELECT 1 FROM empresa e WHERE e.id_usuario = u.id_usuario);

INSERT INTO empresa (id_usuario, nome_fantasia, localizacao)
SELECT u.id_usuario, 'Digital Marketing Pro', 'Rio de Janeiro - RJ'
FROM usuario u
WHERE u.email = 'digital.marketing.pro@empresa.com'
    AND NOT EXISTS (SELECT 1 FROM empresa e WHERE e.id_usuario = u.id_usuario);

INSERT INTO empresa (id_usuario, nome_fantasia, localizacao)
SELECT u.id_usuario, 'Cloud Innovators Brasil', 'Curitiba - PR'
FROM usuario u
WHERE u.email = 'cloud.innovators@empresa.com'
    AND NOT EXISTS (SELECT 1 FROM empresa e WHERE e.id_usuario = u.id_usuario);

INSERT INTO empresa (id_usuario, nome_fantasia, localizacao)
SELECT u.id_usuario, 'Creative Design Studio', 'Belo Horizonte - MG'
FROM usuario u
WHERE u.email = 'creative.design@empresa.com'
    AND NOT EXISTS (SELECT 1 FROM empresa e WHERE e.id_usuario = u.id_usuario);

-- Atribuir habilidades aos novos TRABALHADORES
-- João Silva: PHP, MySQL, Node.js, JavaScript, Docker, Git, HTML/CSS
INSERT INTO usuario_habilidade (id_usuario, id_habilidade)
SELECT u.id_usuario, h.id_habilidade
FROM usuario u
JOIN habilidade h ON h.nome IN ('PHP', 'MySQL', 'Node.js', 'JavaScript', 'Docker', 'Git', 'HTML/CSS')
WHERE u.email = 'joao.silva@gmail.com'
    AND NOT EXISTS (SELECT 1 FROM usuario_habilidade uh WHERE uh.id_usuario = u.id_usuario AND uh.id_habilidade = h.id_habilidade);

-- Maria Santos: UI/UX, Figma, HTML/CSS, JavaScript, Atendimento ao Cliente
INSERT INTO usuario_habilidade (id_usuario, id_habilidade)
SELECT u.id_usuario, h.id_habilidade
FROM usuario u
JOIN habilidade h ON h.nome IN ('UI/UX', 'Figma', 'HTML/CSS', 'JavaScript', 'Atendimento ao Cliente')
WHERE u.email = 'maria.santos@gmail.com'
    AND NOT EXISTS (SELECT 1 FROM usuario_habilidade uh WHERE uh.id_usuario = u.id_usuario AND uh.id_habilidade = h.id_habilidade);

-- Carlos Oliveira: DevOps, AWS, Docker, Linux, Git, Python
INSERT INTO usuario_habilidade (id_usuario, id_habilidade)
SELECT u.id_usuario, h.id_habilidade
FROM usuario u
JOIN habilidade h ON h.nome IN ('DevOps', 'AWS', 'Docker', 'Git', 'Python')
WHERE u.email = 'carlos.oliveira@gmail.com'
    AND NOT EXISTS (SELECT 1 FROM usuario_habilidade uh WHERE uh.id_usuario = u.id_usuario AND uh.id_habilidade = h.id_habilidade);

-- Ana Costa: Marketing Digital, SEO, HTML/CSS, JavaScript
INSERT INTO usuario_habilidade (id_usuario, id_habilidade)
SELECT u.id_usuario, h.id_habilidade
FROM usuario u
JOIN habilidade h ON h.nome IN ('Marketing Digital', 'SEO', 'HTML/CSS', 'JavaScript')
WHERE u.email = 'ana.costa@gmail.com'
    AND NOT EXISTS (SELECT 1 FROM usuario_habilidade uh WHERE uh.id_usuario = u.id_usuario AND uh.id_habilidade = h.id_habilidade);

-- Roberto Mendes: Python, Django, Flask, PostgreSQL, Git
INSERT INTO usuario_habilidade (id_usuario, id_habilidade)
SELECT u.id_usuario, h.id_habilidade
FROM usuario u
JOIN habilidade h ON h.nome IN ('Python', 'Django', 'Flask', 'Git')
WHERE u.email = 'roberto.mendes@gmail.com'
    AND NOT EXISTS (SELECT 1 FROM usuario_habilidade uh WHERE uh.id_usuario = u.id_usuario AND uh.id_habilidade = h.id_habilidade);

-- Fernanda Lima: React, JavaScript, Node.js, Git, HTML/CSS
INSERT INTO usuario_habilidade (id_usuario, id_habilidade)
SELECT u.id_usuario, h.id_habilidade
FROM usuario u
JOIN habilidade h ON h.nome IN ('React', 'JavaScript', 'Node.js', 'Git', 'HTML/CSS')
WHERE u.email = 'fernanda.lima@gmail.com'
    AND NOT EXISTS (SELECT 1 FROM usuario_habilidade uh WHERE uh.id_usuario = u.id_usuario AND uh.id_habilidade = h.id_habilidade);

-- Lucas Gomes: PHP, MySQL, Git, JavaScript, HTML/CSS
INSERT INTO usuario_habilidade (id_usuario, id_habilidade)
SELECT u.id_usuario, h.id_habilidade
FROM usuario u
JOIN habilidade h ON h.nome IN ('PHP', 'MySQL', 'Git', 'JavaScript', 'HTML/CSS')
WHERE u.email = 'lucas.gomes@gmail.com'
    AND NOT EXISTS (SELECT 1 FROM usuario_habilidade uh WHERE uh.id_usuario = u.id_usuario AND uh.id_habilidade = h.id_habilidade);

-- Novos anúncios de Tech Solutions
INSERT INTO anuncio (id_contratante, titulo, descricao, localizacao, data, remuneracao, tipo_servico, duracao, observacoes, prazo_candidatura, status)
SELECT u.id_usuario, 'Desenvolvedor Full Stack Senior', 'Procuramos desenvolvedor experiente em PHP e JavaScript para projeto de longa duração.', 'São Paulo - SP', DATE_SUB(@now, INTERVAL 5 DAY), 3500.00, 'FIXO', '6 meses', 'Experiência com arquitetura de software é diferencial.', DATE_ADD(DATE_SUB(@now, INTERVAL 5 DAY), INTERVAL 20 DAY), 'ABERTO'
FROM usuario u WHERE u.email = 'tech.solutions@empresa.com'
AND NOT EXISTS (SELECT 1 FROM anuncio a WHERE a.titulo = 'Desenvolvedor Full Stack Senior' AND a.id_contratante = u.id_usuario);

INSERT INTO anuncio (id_contratante, titulo, descricao, localizacao, data, remuneracao, tipo_servico, duracao, observacoes, prazo_candidatura, status)
SELECT u.id_usuario, 'Especialista em Banco de Dados', 'Otimização e manutenção de bases de dados MySQL e PostgreSQL.', 'Remoto', DATE_SUB(@now, INTERVAL 4 DAY), 2800.00, 'FIXO', '3 meses', 'Experiência com replicação e backup essencial.', DATE_ADD(DATE_SUB(@now, INTERVAL 4 DAY), INTERVAL 20 DAY), 'ABERTO'
FROM usuario u WHERE u.email = 'tech.solutions@empresa.com'
AND NOT EXISTS (SELECT 1 FROM anuncio a WHERE a.titulo = 'Especialista em Banco de Dados' AND a.id_contratante = u.id_usuario);

INSERT INTO anuncio (id_contratante, titulo, descricao, localizacao, data, remuneracao, tipo_servico, duracao, observacoes, prazo_candidatura, status)
SELECT u.id_usuario, 'Técnico de Suporte Nível 2', 'Suporte técnico remoto para clientes de software.', 'Remoto', DATE_SUB(@now, INTERVAL 3 DAY), 2000.00, 'FIXO', '4 meses', 'Turno: 14h às 22h, disponibilidade para escalações.', DATE_ADD(DATE_SUB(@now, INTERVAL 3 DAY), INTERVAL 18 DAY), 'ABERTO'
FROM usuario u WHERE u.email = 'tech.solutions@empresa.com'
AND NOT EXISTS (SELECT 1 FROM anuncio a WHERE a.titulo = 'Técnico de Suporte Nível 2' AND a.id_contratante = u.id_usuario);

-- Novos anúncios de Digital Marketing Pro
INSERT INTO anuncio (id_contratante, titulo, descricao, localizacao, data, remuneracao, tipo_servico, duracao, observacoes, prazo_candidatura, status)
SELECT u.id_usuario, 'Especialista em Google Ads', 'Gestão de campanhas Google Ads e analytics.', 'Remoto', DATE_SUB(@now, INTERVAL 6 DAY), 2200.00, 'FIXO', '2 meses', 'Certificação Google Ads desejável.', DATE_ADD(DATE_SUB(@now, INTERVAL 6 DAY), INTERVAL 20 DAY), 'ABERTO'
FROM usuario u WHERE u.email = 'digital.marketing.pro@empresa.com'
AND NOT EXISTS (SELECT 1 FROM anuncio a WHERE a.titulo = 'Especialista em Google Ads' AND a.id_contratante = u.id_usuario);

INSERT INTO anuncio (id_contratante, titulo, descricao, localizacao, data, remuneracao, tipo_servico, duracao, observacoes, prazo_candidatura, status)
SELECT u.id_usuario, 'Produtor de Conteúdo Criativo', 'Criação de conteúdo para redes sociais e campanhas.', 'Remoto', DATE_SUB(@now, INTERVAL 2 DAY), 1600.00, 'TEMPORARIO', '1 mês', 'Roteiros, copywriting e edição básica.', DATE_ADD(DATE_SUB(@now, INTERVAL 2 DAY), INTERVAL 15 DAY), 'ABERTO'
FROM usuario u WHERE u.email = 'digital.marketing.pro@empresa.com'
AND NOT EXISTS (SELECT 1 FROM anuncio a WHERE a.titulo = 'Produtor de Conteúdo Criativo' AND a.id_contratante = u.id_usuario);

INSERT INTO anuncio (id_contratante, titulo, descricao, localizacao, data, remuneracao, tipo_servico, duracao, observacoes, prazo_candidatura, status)
SELECT u.id_usuario, 'Analista de Redes Sociais', 'Análise, planejamento e execução de estratégia em redes.', 'Remoto', DATE_SUB(@now, INTERVAL 1 DAY), 1800.00, 'FIXO', '3 meses', 'Conhecimento em Instagram, Facebook e TikTok.', DATE_ADD(DATE_SUB(@now, INTERVAL 1 DAY), INTERVAL 20 DAY), 'ABERTO'
FROM usuario u WHERE u.email = 'digital.marketing.pro@empresa.com'
AND NOT EXISTS (SELECT 1 FROM anuncio a WHERE a.titulo = 'Analista de Redes Sociais' AND a.id_contratante = u.id_usuario);

-- Novos anúncios de Cloud Innovators
INSERT INTO anuncio (id_contratante, titulo, descricao, localizacao, data, remuneracao, tipo_servico, duracao, observacoes, prazo_candidatura, status)
SELECT u.id_usuario, 'Arquiteto de Soluções AWS', 'Design e implementação de arquiteturas em AWS.', 'Remoto', DATE_SUB(@now, INTERVAL 5 DAY), 4000.00, 'FIXO', '5 meses', 'Certificação AWS Solutions Architect essencial.', DATE_ADD(DATE_SUB(@now, INTERVAL 5 DAY), INTERVAL 20 DAY), 'ABERTO'
FROM usuario u WHERE u.email = 'cloud.innovators@empresa.com'
AND NOT EXISTS (SELECT 1 FROM anuncio a WHERE a.titulo = 'Arquiteto de Soluções AWS' AND a.id_contratante = u.id_usuario);

INSERT INTO anuncio (id_contratante, titulo, descricao, localizacao, data, remuneracao, tipo_servico, duracao, observacoes, prazo_candidatura, status)
SELECT u.id_usuario, 'Engenheiro DevOps', 'Implementação de pipelines CI/CD e containerização.', 'Remoto', DATE_SUB(@now, INTERVAL 4 DAY), 3200.00, 'FIXO', '4 meses', 'Experiência com Docker, Kubernetes e Jenkins.', DATE_ADD(DATE_SUB(@now, INTERVAL 4 DAY), INTERVAL 20 DAY), 'ABERTO'
FROM usuario u WHERE u.email = 'cloud.innovators@empresa.com'
AND NOT EXISTS (SELECT 1 FROM anuncio a WHERE a.titulo = 'Engenheiro DevOps' AND a.id_contratante = u.id_usuario);

INSERT INTO anuncio (id_contratante, titulo, descricao, localizacao, data, remuneracao, tipo_servico, duracao, observacoes, prazo_candidatura, status)
SELECT u.id_usuario, 'Consultor de Transformação Digital', 'Consultoria em transformação digital para empresas.', 'Remoto', DATE_SUB(@now, INTERVAL 3 DAY), 2500.00, 'TEMPORARIO', '3 semanas', 'Presencial em Curitiba é um diferencial.', DATE_ADD(DATE_SUB(@now, INTERVAL 3 DAY), INTERVAL 18 DAY), 'ABERTO'
FROM usuario u WHERE u.email = 'cloud.innovators@empresa.com'
AND NOT EXISTS (SELECT 1 FROM anuncio a WHERE a.titulo = 'Consultor de Transformação Digital' AND a.id_contratante = u.id_usuario);

-- Novos anúncios de Creative Design Studio
INSERT INTO anuncio (id_contratante, titulo, descricao, localizacao, data, remuneracao, tipo_servico, duracao, observacoes, prazo_candidatura, status)
SELECT u.id_usuario, 'Designer Gráfico Sênior', 'Criação de materiais gráficos e branding para clientes.', 'Belo Horizonte - MG', DATE_SUB(@now, INTERVAL 5 DAY), 2600.00, 'FIXO', '3 meses', 'Portfolio com no mínimo 5 projetos relevantes.', DATE_ADD(DATE_SUB(@now, INTERVAL 5 DAY), INTERVAL 20 DAY), 'ABERTO'
FROM usuario u WHERE u.email = 'creative.design@empresa.com'
AND NOT EXISTS (SELECT 1 FROM anuncio a WHERE a.titulo = 'Designer Gráfico Sênior' AND a.id_contratante = u.id_usuario);

INSERT INTO anuncio (id_contratante, titulo, descricao, localizacao, data, remuneracao, tipo_servico, duracao, observacoes, prazo_candidatura, status)
SELECT u.id_usuario, 'Designer de Interface', 'Criação de interfaces modernas e responsivas em Figma.', 'Remoto', DATE_SUB(@now, INTERVAL 2 DAY), 2300.00, 'FIXO', '2 meses', 'Conhecimento em design system.', DATE_ADD(DATE_SUB(@now, INTERVAL 2 DAY), INTERVAL 18 DAY), 'ABERTO'
FROM usuario u WHERE u.email = 'creative.design@empresa.com'
AND NOT EXISTS (SELECT 1 FROM anuncio a WHERE a.titulo = 'Designer de Interface' AND a.id_contratante = u.id_usuario);

INSERT INTO anuncio (id_contratante, titulo, descricao, localizacao, data, remuneracao, tipo_servico, duracao, observacoes, prazo_candidatura, status)
SELECT u.id_usuario, 'Ilustrador Digital', 'Criação de ilustrações customizadas para campanhas.', 'Remoto', DATE_SUB(@now, INTERVAL 1 DAY), 1400.00, 'TEMPORARIO', '2 semanas', 'Estilos variados: realista, cartoon, abstrato.', DATE_ADD(DATE_SUB(@now, INTERVAL 1 DAY), INTERVAL 16 DAY), 'ABERTO'
FROM usuario u WHERE u.email = 'creative.design@empresa.com'
AND NOT EXISTS (SELECT 1 FROM anuncio a WHERE a.titulo = 'Ilustrador Digital' AND a.id_contratante = u.id_usuario);

-- Novas candidaturas dos trabalhadores aos anúncios
INSERT INTO candidatura (id_anuncio, id_trabalhador, data_candidatura, status, data_selecao)
SELECT a.id_anuncio, u.id_usuario, DATE_ADD(a.data, INTERVAL 12 HOUR), 'PENDENTE', NULL
FROM anuncio a
JOIN usuario u ON u.email = 'joao.silva@gmail.com'
WHERE a.id_contratante = (SELECT id_usuario FROM usuario WHERE email = 'tech.solutions@empresa.com')
    AND NOT EXISTS (SELECT 1 FROM candidatura c WHERE c.id_anuncio = a.id_anuncio AND c.id_trabalhador = u.id_usuario);

INSERT INTO candidatura (id_anuncio, id_trabalhador, data_candidatura, status, data_selecao)
SELECT a.id_anuncio, u.id_usuario, DATE_ADD(a.data, INTERVAL 8 HOUR), 'ACEITO', DATE_ADD(a.data, INTERVAL 24 HOUR)
FROM anuncio a
JOIN usuario u ON u.email = 'maria.santos@gmail.com'
WHERE a.id_contratante = (SELECT id_usuario FROM usuario WHERE email = 'creative.design@empresa.com')
    AND a.titulo = 'Designer de Interface'
    AND NOT EXISTS (SELECT 1 FROM candidatura c WHERE c.id_anuncio = a.id_anuncio AND c.id_trabalhador = u.id_usuario);

INSERT INTO candidatura (id_anuncio, id_trabalhador, data_candidatura, status, data_selecao)
SELECT a.id_anuncio, u.id_usuario, DATE_ADD(a.data, INTERVAL 6 HOUR), 'ACEITO', DATE_ADD(a.data, INTERVAL 18 HOUR)
FROM anuncio a
JOIN usuario u ON u.email = 'carlos.oliveira@gmail.com'
WHERE a.id_contratante = (SELECT id_usuario FROM usuario WHERE email = 'cloud.innovators@empresa.com')
    AND a.titulo = 'Engenheiro DevOps'
    AND NOT EXISTS (SELECT 1 FROM candidatura c WHERE c.id_anuncio = a.id_anuncio AND c.id_trabalhador = u.id_usuario);

INSERT INTO candidatura (id_anuncio, id_trabalhador, data_candidatura, status, data_selecao)
SELECT a.id_anuncio, u.id_usuario, DATE_ADD(a.data, INTERVAL 10 HOUR), 'PENDENTE', NULL
FROM anuncio a
JOIN usuario u ON u.email = 'ana.costa@gmail.com'
WHERE a.id_contratante = (SELECT id_usuario FROM usuario WHERE email = 'digital.marketing.pro@empresa.com')
    AND a.titulo = 'Especialista em Google Ads'
    AND NOT EXISTS (SELECT 1 FROM candidatura c WHERE c.id_anuncio = a.id_anuncio AND c.id_trabalhador = u.id_usuario);

INSERT INTO candidatura (id_anuncio, id_trabalhador, data_candidatura, status, data_selecao)
SELECT a.id_anuncio, u.id_usuario, DATE_ADD(a.data, INTERVAL 5 HOUR), 'ACEITO', DATE_ADD(a.data, INTERVAL 20 HOUR)
FROM anuncio a
JOIN usuario u ON u.email = 'roberto.mendes@gmail.com'
WHERE a.id_contratante = (SELECT id_usuario FROM usuario WHERE email = 'tech.solutions@empresa.com')
    AND a.titulo = 'Especialista em Banco de Dados'
    AND NOT EXISTS (SELECT 1 FROM candidatura c WHERE c.id_anuncio = a.id_anuncio AND c.id_trabalhador = u.id_usuario);

INSERT INTO candidatura (id_anuncio, id_trabalhador, data_candidatura, status, data_selecao)
SELECT a.id_anuncio, u.id_usuario, DATE_ADD(a.data, INTERVAL 9 HOUR), 'RECUSADO', NULL
FROM anuncio a
JOIN usuario u ON u.email = 'fernanda.lima@gmail.com'
WHERE a.id_contratante = (SELECT id_usuario FROM usuario WHERE email = 'tech.solutions@empresa.com')
    AND a.titulo = 'Desenvolvedor Full Stack Senior'
    AND NOT EXISTS (SELECT 1 FROM candidatura c WHERE c.id_anuncio = a.id_anuncio AND c.id_trabalhador = u.id_usuario);

INSERT INTO candidatura (id_anuncio, id_trabalhador, data_candidatura, status, data_selecao)
SELECT a.id_anuncio, u.id_usuario, DATE_ADD(a.data, INTERVAL 7 HOUR), 'ACEITO', DATE_ADD(a.data, INTERVAL 22 HOUR)
FROM anuncio a
JOIN usuario u ON u.email = 'lucas.gomes@gmail.com'
WHERE a.id_contratante = (SELECT id_usuario FROM usuario WHERE email = 'tech.solutions@empresa.com')
    AND a.titulo = 'Técnico de Suporte Nível 2'
    AND NOT EXISTS (SELECT 1 FROM candidatura c WHERE c.id_anuncio = a.id_anuncio AND c.id_trabalhador = u.id_usuario);

-- Novos anúncios adicionais para Digital Marketing Pro
INSERT INTO anuncio (id_contratante, titulo, descricao, localizacao, data, remuneracao, tipo_servico, duracao, observacoes, prazo_candidatura, status)
SELECT u.id_usuario, 'Community Manager', 'Gestão de comunidades online e suporte ao cliente.', 'Remoto', DATE_SUB(@now, INTERVAL 7 DAY), 1500.00, 'FIXO', '3 meses', 'Responsivo e comunicativo.', DATE_ADD(DATE_SUB(@now, INTERVAL 7 DAY), INTERVAL 20 DAY), 'ABERTO'
FROM usuario u WHERE u.email = 'digital.marketing.pro@empresa.com'
AND NOT EXISTS (SELECT 1 FROM anuncio a WHERE a.titulo = 'Community Manager' AND a.id_contratante = u.id_usuario);

-- Avaliações adicionais para trabalhadores que aceitaram propostas
INSERT INTO avaliacao (id_avaliador, id_avaliado, id_anuncio, nota, comentario, data)
SELECT cont.id_usuario, trab.id_usuario, c.id_anuncio, 5, 'Excelente qualidade de trabalho, pontual e atencioso.', DATE_ADD(c.data_selecao, INTERVAL 5 HOUR)
FROM candidatura c
JOIN usuario cont ON cont.email = 'creative.design@empresa.com'
JOIN usuario trab ON trab.email = 'maria.santos@gmail.com'
WHERE c.status = 'ACEITO'
    AND NOT EXISTS (SELECT 1 FROM avaliacao av WHERE av.id_avaliador = cont.id_usuario AND av.id_avaliado = trab.id_usuario AND av.id_anuncio = c.id_anuncio);

INSERT INTO avaliacao (id_avaliador, id_avaliado, id_anuncio, nota, comentario, data)
SELECT cont.id_usuario, trab.id_usuario, c.id_anuncio, 5, 'Profissional muito competente em infraestrutura cloud.', DATE_ADD(c.data_selecao, INTERVAL 3 HOUR)
FROM candidatura c
JOIN usuario cont ON cont.email = 'cloud.innovators@empresa.com'
JOIN usuario trab ON trab.email = 'carlos.oliveira@gmail.com'
WHERE c.status = 'ACEITO'
    AND NOT EXISTS (SELECT 1 FROM avaliacao av WHERE av.id_avaliador = cont.id_usuario AND av.id_avaliado = trab.id_usuario AND av.id_anuncio = c.id_anuncio);

INSERT INTO avaliacao (id_avaliador, id_avaliado, id_anuncio, nota, comentario, data)
SELECT trab.id_usuario, cont.id_usuario, c.id_anuncio, 4, 'Boa comunicação e feedback durante o projeto.', DATE_ADD(c.data_selecao, INTERVAL 4 HOUR)
FROM candidatura c
JOIN usuario trab ON trab.email = 'roberto.mendes@gmail.com'
JOIN usuario cont ON cont.email = 'tech.solutions@empresa.com'
WHERE c.status = 'ACEITO'
    AND NOT EXISTS (SELECT 1 FROM avaliacao av WHERE av.id_avaliador = trab.id_usuario AND av.id_avaliado = cont.id_usuario AND av.id_anuncio = c.id_anuncio);

INSERT INTO avaliacao (id_avaliador, id_avaliado, id_anuncio, nota, comentario, data)
SELECT trab.id_usuario, cont.id_usuario, c.id_anuncio, 5, 'Empresa muito profissional, adorei trabalhar com vocês.', DATE_ADD(c.data_selecao, INTERVAL 6 HOUR)
FROM candidatura c
JOIN usuario trab ON trab.email = 'lucas.gomes@gmail.com'
JOIN usuario cont ON cont.email = 'tech.solutions@empresa.com'
WHERE c.status = 'ACEITO'
    AND NOT EXISTS (SELECT 1 FROM avaliacao av WHERE av.id_avaliador = trab.id_usuario AND av.id_avaliado = cont.id_usuario AND av.id_anuncio = c.id_anuncio);

-- Denúncias diversas
INSERT INTO denuncia (id_denunciante, id_denunciado, id_anuncio, motivo, descricao, data, status)
SELECT adm.id_usuario, trab.id_usuario, NULL, 'Comportamento inapropriado', 'Usuário com linguagem ofensiva em perfil.', DATE_SUB(@now, INTERVAL 3 DAY), 'PENDENTE'
FROM usuario adm
JOIN usuario trab ON trab.email = 'joao.silva@gmail.com'
WHERE adm.email = 'admin@gmail.com'
    AND NOT EXISTS (SELECT 1 FROM denuncia d WHERE d.motivo = 'Comportamento inapropriado' AND d.id_denunciado = trab.id_usuario);

INSERT INTO denuncia (id_denunciante, id_denunciado, id_anuncio, motivo, descricao, data, status)
SELECT adm.id_usuario, cont.id_usuario, NULL, 'Conta suspeita', 'Múltiplas postagens com conteúdo spam.', DATE_SUB(@now, INTERVAL 2 DAY), 'PENDENTE'
FROM usuario adm
JOIN usuario cont ON cont.email = 'digital.marketing.pro@empresa.com'
WHERE adm.email = 'admin@gmail.com'
    AND NOT EXISTS (SELECT 1 FROM denuncia d WHERE d.motivo = 'Conta suspeita' AND d.id_denunciado = cont.id_usuario);
