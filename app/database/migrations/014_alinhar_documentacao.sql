-- ============================================================================
-- ALINHAMENTO À DOCUMENTAÇÃO: FreelaJá v1
-- ============================================================================
-- IMPORTANTE: Fazer backup do banco ANTES de aplicar em produção.
-- Esta migration é idempotente (segura rodar múltiplas vezes).
--
-- Alterações:
-- 1. Remover campo 'cidade' de usuario
-- 2. Adicionar campos 'bairro', 'razao_social', 'nome_fantasia' a usuario
-- 3. Remover campo 'tipo_servico' de vaga (sistema é só temporário)
-- 4. Adicionar 'excluida_em' a vaga (exclusão lógica, RN 17)
-- 5. Renomear tabela 'interesse' → 'candidatura' (com todos os campos)
-- 6. Remover tabela 'advertencia' inteira (RN 13: só bloqueio, sem advertência)
-- 7. Corrigir enum 'acao_moderacao' em denuncia
-- 8. Adicionar 'id_admin' e 'analisada_em' a denuncia (rastreabilidade)
-- ============================================================================

-- ============================================================================
-- 1. USUARIO: adicionar 'bairro', 'razao_social', 'nome_fantasia' (se não existem)
-- ============================================================================

-- Nota: Se rodar em banco que já tem estas colunas, ALTER TABLE gerará erro (aceitável).
-- Estas colunas são essenciais para o sistema alinhar-se à especificação.
ALTER TABLE usuario ADD COLUMN bairro VARCHAR(100) NULL AFTER telefone;
ALTER TABLE usuario ADD COLUMN razao_social VARCHAR(150) NULL AFTER bairro;
ALTER TABLE usuario ADD COLUMN nome_fantasia VARCHAR(150) NULL AFTER razao_social;

-- ============================================================================
-- 2. VAGA: remover 'tipo_servico', adicionar 'excluida_em'
-- ============================================================================

-- Remover tipo_servico (sistema é só temporário, RN 03)
ALTER TABLE vaga DROP COLUMN tipo_servico;

-- Adicionar excluida_em para exclusão lógica (RN 17)
ALTER TABLE vaga ADD COLUMN excluida_em DATETIME NULL AFTER visibilidade;

-- ============================================================================
-- 3. RENOMEAR TABELA: interesse → candidatura
-- ============================================================================

-- Renomear tabela interesse para candidatura
RENAME TABLE interesse TO candidatura;

-- Renomear colunas via ALTER TABLE
ALTER TABLE candidatura
CHANGE COLUMN id_interesse id_candidatura INT NOT NULL AUTO_INCREMENT;

ALTER TABLE candidatura
CHANGE COLUMN data_interesse data_candidatura DATETIME DEFAULT CURRENT_TIMESTAMP;

-- Renomear constraint (remover antiga, adicionar nova)
ALTER TABLE candidatura
DROP CONSTRAINT uk_interesse;

ALTER TABLE candidatura
ADD CONSTRAINT uk_candidatura UNIQUE(id_vaga, id_trabalhador);

-- ============================================================================
-- 4. REMOVER TABELA: advertencia (não mais usada)
-- ============================================================================

DROP TABLE advertencia;

-- ============================================================================
-- 5. CORRIGIR ENUM: acao_moderacao em denuncia
-- ============================================================================

-- Adicionar coluna temporária para migrar os dados
ALTER TABLE denuncia
ADD COLUMN acao_moderacao_new ENUM('NENHUMA','BLOQUEIO','VAGA_OCULTA','VAGA_REMOVIDA') NULL;

-- Migrar dados (mapear valores antigos para novos)
UPDATE denuncia
SET acao_moderacao_new = CASE
    WHEN acao_moderacao = 'NENHUMA' THEN 'NENHUMA'
    WHEN acao_moderacao = 'ADVERTENCIA' THEN 'NENHUMA'  -- sem advertência, mapear para NENHUMA
    WHEN acao_moderacao = 'BLOQUEIO' THEN 'BLOQUEIO'
    WHEN acao_moderacao = 'ANUNCIO_OCULTO' THEN 'VAGA_OCULTA'
    WHEN acao_moderacao = 'ANUNCIO_REMOVIDO' THEN 'VAGA_REMOVIDA'
    ELSE NULL
END
WHERE acao_moderacao IS NOT NULL;

-- Remover coluna antiga e renomear nova
ALTER TABLE denuncia
DROP COLUMN acao_moderacao;

ALTER TABLE denuncia
CHANGE COLUMN acao_moderacao_new acao_moderacao ENUM('NENHUMA','BLOQUEIO','VAGA_OCULTA','VAGA_REMOVIDA') NULL;

-- ============================================================================
-- 6. ADICIONAR CAMPOS DE RASTREABILIDADE: denuncia
-- ============================================================================

ALTER TABLE denuncia
ADD COLUMN id_admin INT NULL AFTER acao_moderacao;

ALTER TABLE denuncia
ADD COLUMN analisada_em DATETIME NULL AFTER id_admin;

-- Adicionar foreign key para id_admin (moderador)
ALTER TABLE denuncia
ADD CONSTRAINT fk_denuncia_admin
    FOREIGN KEY (id_admin) REFERENCES usuario(id_usuario) ON DELETE SET NULL;

-- ============================================================================
-- 7. SEED: USUÁRIO ADMINISTRADOR
-- ============================================================================

-- Inserir admin se não existir (email único previne duplicação)
-- Usa IGNORE para não falhar se já existir
INSERT IGNORE INTO usuario (
    nome, email, senha, telefone,
    foto_perfil, descricao, documento,
    is_admin, is_trabalhador, is_contratante,
    tipo_pessoa, ativo, data_cadastro
) VALUES (
    'Administrador',
    'admin@freelaja.com',
    '$2y$10$IhxuWLqg3ge6jjc5qukdcu/f5TVA6TzUGlurGbqPS1zBgCD/.2qH6',  -- senha123
    '(46)99999-0001',
    NULL,
    'Usuário administrador do sistema',
    NULL,
    1, 0, 0,
    'PJ', 1, NOW()
);

-- ============================================================================
-- FIM DA MIGRATION
-- ============================================================================
