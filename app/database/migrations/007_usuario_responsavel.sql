-- Item 6: responsável único pela execução do serviço, exigido de empresas (PJ)
-- que atuam como prestadoras (trabalhador). Nulo para PF e para PJ só contratante.
ALTER TABLE usuario
    ADD COLUMN nome_responsavel VARCHAR(100) NULL AFTER documento;
