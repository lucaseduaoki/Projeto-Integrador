-- Item 2: tipo de serviço da vaga (fixo ou temporário).
-- Vagas existentes ficam como FIXO; o servidor exige a escolha explícita em novas vagas.
ALTER TABLE vaga
    ADD COLUMN tipo_servico ENUM('FIXO','TEMPORARIO') NOT NULL DEFAULT 'FIXO' AFTER horario;
