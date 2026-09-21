-- Migração para bancos já criados: adiciona vaga.is_user_active e os triggers.
-- Criar triggers exige privilégio SUPER quando o binlog está ligado (padrão no MySQL 8):
-- rode como root ou ative log_bin_trust_function_creators.
-- Uso: mysql -u USUARIO -p freelaja < app/database/migrations/001_vaga_is_user_active.sql

ALTER TABLE vaga
    ADD COLUMN is_user_active BOOLEAN NOT NULL DEFAULT TRUE AFTER status;

UPDATE vaga v
JOIN usuario u ON u.id_usuario = v.id_contratante
SET v.is_user_active = COALESCE(u.ativo, 1);

DROP TRIGGER IF EXISTS trg_usuario_ativo_atualiza_vagas;
DROP TRIGGER IF EXISTS trg_vaga_define_is_user_active;

DELIMITER //

CREATE TRIGGER trg_usuario_ativo_atualiza_vagas
AFTER UPDATE ON usuario
FOR EACH ROW
UPDATE vaga
SET is_user_active = COALESCE(NEW.ativo, 1)
WHERE id_contratante = NEW.id_usuario
  AND NOT (COALESCE(NEW.ativo, 1) <=> COALESCE(OLD.ativo, 1));
//

CREATE TRIGGER trg_vaga_define_is_user_active
BEFORE INSERT ON vaga
FOR EACH ROW
SET NEW.is_user_active = COALESCE(
    (SELECT ativo FROM usuario WHERE id_usuario = NEW.id_contratante),
    1
);
//

DELIMITER ;
