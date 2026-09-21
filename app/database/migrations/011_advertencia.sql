-- Item 14: advertência como ação de moderação (RN14).
-- Tabela advertencia: registro da advertência e controle de "já visualizada" pelo advertido.
-- denuncia.acao_moderacao: o que a moderação decidiu (nula enquanto a denúncia está pendente).
CREATE TABLE advertencia (
    id_advertencia INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_denuncia INT NULL,
    id_moderador INT NULL,
    mensagem VARCHAR(500) NOT NULL,
    data_advertencia DATETIME DEFAULT CURRENT_TIMESTAMP,
    visualizada_em DATETIME NULL,

    CONSTRAINT fk_advertencia_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    CONSTRAINT fk_advertencia_denuncia
        FOREIGN KEY (id_denuncia) REFERENCES denuncia(id_denuncia) ON DELETE SET NULL,
    CONSTRAINT fk_advertencia_moderador
        FOREIGN KEY (id_moderador) REFERENCES usuario(id_usuario) ON DELETE SET NULL
);

CREATE INDEX idx_advertencia_usuario_pendente ON advertencia(id_usuario, visualizada_em);

ALTER TABLE denuncia
    ADD COLUMN acao_moderacao ENUM('NENHUMA','ADVERTENCIA','BLOQUEIO') NULL AFTER status;
