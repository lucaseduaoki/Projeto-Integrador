-- ============================================================================
-- SEED: Usuário Administrador
-- ============================================================================
-- Cria um usuário administrador com as seguintes credenciais:
-- Email: admin@freelaja.com
-- Senha: admin123
-- Papel: Administrador
--
-- Para executar:
-- mysql -u freelaja -proot freelaja < app/database/admin.sql
-- ============================================================================

INSERT IGNORE INTO usuario (
    nome,
    email,
    senha,
    telefone,
    foto_perfil,
    descricao,
    documento,
    is_admin,
    is_trabalhador,
    is_contratante,
    tipo_pessoa,
    ativo,
    data_cadastro
) VALUES (
    'Administrador',
    'admin@freelaja.com',
    '$2y$10$IhxuWLqg3ge6jjc5qukdcu/f5TVA6TzUGlurGbqPS1zBgCD/.2qH6',
    '(46)99999-0001',
    NULL,
    'Usuário administrador do sistema FreelaJá',
    NULL,
    1,
    0,
    0,
    'PJ',
    1,
    NOW()
);

-- ============================================================================
-- FIM
-- ============================================================================
