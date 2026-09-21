-- Item 15: ocultar/remover anúncio na moderação (RN14). Nada é apagado.
-- OCULTA: some da listagem e da busca e não recebe candidaturas; só dono e admin a veem (o dono pode corrigi-la).
-- REMOVIDA: mesmo efeito, mas travada para o dono (não edita nem exclui).
ALTER TABLE vaga
    ADD COLUMN visibilidade ENUM('VISIVEL','OCULTA','REMOVIDA') NOT NULL DEFAULT 'VISIVEL' AFTER status;

ALTER TABLE denuncia
    MODIFY COLUMN acao_moderacao
        ENUM('NENHUMA','ADVERTENCIA','BLOQUEIO','ANUNCIO_OCULTO','ANUNCIO_REMOVIDO') NULL;
