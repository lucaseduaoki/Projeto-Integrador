-- Item 1: horário do serviço na vaga (hh:mm).
-- Nulo em vagas antigas; o servidor exige o campo em novas vagas e nas edições.
ALTER TABLE vaga
    ADD COLUMN horario TIME NULL AFTER data_limite;
