-- Item 26: o local da vaga aceita até 150 caracteres (UC04.1); a coluna tinha 100.
-- Só amplia o tamanho: nenhum dado existente é alterado.
ALTER TABLE vaga
    MODIFY COLUMN localizacao VARCHAR(150) NULL;
