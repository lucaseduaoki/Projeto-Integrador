-- Corrige o status usado pela moderação: o código gravava 'ANALISADO', valor que não existia no ENUM.
-- Acrescenta ANALISADA (denúncia já tratada pela moderação) mantendo os valores antigos.
ALTER TABLE denuncia
    MODIFY COLUMN status ENUM('PENDENTE','ANALISADA','APROVADA','REJEITADA') DEFAULT 'PENDENTE';
