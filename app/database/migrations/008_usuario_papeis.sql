-- Item 7: papel duplo. tipo_usuario (ENUM único) vira três flags, para que uma
-- pessoa física possa ser trabalhador e contratante ao mesmo tempo.
-- Escolha: flags booleanas em vez de tabela N:N, pois há só três papéis fixos, a checagem
-- roda em toda requisição (sem JOIN) e o código já usa isAdmin/isTrabalhador/isContratante.
-- Nenhuma informação é perdida: cada valor antigo de tipo_usuario vira a flag correspondente.
ALTER TABLE usuario
    ADD COLUMN is_admin BOOLEAN NOT NULL DEFAULT 0 AFTER tipo_usuario,
    ADD COLUMN is_trabalhador BOOLEAN NOT NULL DEFAULT 0 AFTER is_admin,
    ADD COLUMN is_contratante BOOLEAN NOT NULL DEFAULT 0 AFTER is_trabalhador;

UPDATE usuario
SET is_admin = (tipo_usuario = 'ADMIN'),
    is_trabalhador = (tipo_usuario = 'TRABALHADOR'),
    is_contratante = (tipo_usuario = 'CONTRATANTE');

ALTER TABLE usuario DROP COLUMN tipo_usuario;

-- Toda conta tem ao menos um papel; empresa (PJ) atua em um único papel (RN02: só PF acumula).
ALTER TABLE usuario
    ADD CONSTRAINT chk_usuario_tem_papel
        CHECK (is_admin + is_trabalhador + is_contratante >= 1),
    ADD CONSTRAINT chk_usuario_pj_papel_unico
        CHECK (NOT (tipo_pessoa = 'PJ' AND is_trabalhador = 1 AND is_contratante = 1));
