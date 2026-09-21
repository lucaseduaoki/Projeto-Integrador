-- Item 10: data do serviço (dia em que o trabalho acontece), distinta de data_limite
-- (prazo opcional de candidatura, RN05). O filtro de busca por data usa esta coluna.
-- Vagas existentes: data_limite quando houver, senão a data de publicação.
ALTER TABLE vaga
    ADD COLUMN data_servico DATE NULL AFTER data_limite;

UPDATE vaga
SET data_servico = COALESCE(data_limite, DATE(data_publicacao))
WHERE data_servico IS NULL;

CREATE INDEX idx_vaga_data_servico ON vaga(data_servico);
