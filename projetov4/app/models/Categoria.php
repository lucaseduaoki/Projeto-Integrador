<?php

namespace app\models;

class Categoria{
    
    private int $idCategoria;
    private string $nome;

    public function __construct(string $nome, int $idCategoria=null) {
        $this->idCategoria = $idCategoria;
        $this->nome = $nome;
    }

    public function getIdCategoria(): int {
        return $this->idCategoria;
    }

    public function getNome(): string {
        return $this->nome;
    }

    public function setIdCategoria(int $idCategoria): void {
        $this->idCategoria = $idCategoria;
    }
    
    public function setNome(string $nome): void {
        $this->nome = $nome;
    }

    public function toArray(): array {
        return [
            'idCategoria' => $this->idCategoria,
            'nome' => $this->nome
        ];
    }


}