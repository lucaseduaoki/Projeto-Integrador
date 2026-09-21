-- Item 3: duração do serviço (texto curto, ex.: "3 dias", "2 semanas").
-- Obrigatória só para tipo_servico = TEMPORARIO; nula nos demais casos.
ALTER TABLE vaga
    ADD COLUMN duracao VARCHAR(50) NULL AFTER tipo_servico;
