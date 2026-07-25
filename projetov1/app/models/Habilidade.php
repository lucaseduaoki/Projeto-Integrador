<?php

namespace app\models;

class Habilidade
{
    private int $idHabilidade;
    private string $nome;

    public function __construct(int $idHabilidade, string $nome)
    {
        $this->idHabilidade = $idHabilidade;
        $this->nome = $nome;
    }

    // Getters
    public function getIdHabilidade(): int
    {
        return $this->idHabilidade;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    // Setters
    public function setNome(string $nome): self
    {
        $this->nome = $nome;
        return $this;
    }

    // Método estático para converter array em objeto
    public static function arrayParaObjeto(array $data): static
    {
        return new self(
            $data['id_habilidade'],
            $data['nome']
        );
    }
}
