-- Item 4: observações adicionais da vaga (opcional, até 500 caracteres; limite validado no servidor).
ALTER TABLE vaga
    ADD COLUMN observacoes TEXT NULL AFTER duracao;
