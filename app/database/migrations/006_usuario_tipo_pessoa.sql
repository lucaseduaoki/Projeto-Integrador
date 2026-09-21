-- Item 5: tipo de pessoa (PF/PJ), separado do papel do usuário.
-- PF usa CPF e PJ usa CNPJ no campo documento. Contas existentes: PJ se o documento
-- tiver 14 dígitos, PF nos demais casos. Nenhum documento é alterado.
ALTER TABLE usuario
    ADD COLUMN tipo_pessoa ENUM('PF','PJ') NOT NULL DEFAULT 'PF' AFTER tipo_usuario;

UPDATE usuario
SET tipo_pessoa = 'PJ'
WHERE documento IS NOT NULL
  AND CHAR_LENGTH(REGEXP_REPLACE(documento, '[^0-9]', '')) = 14;
