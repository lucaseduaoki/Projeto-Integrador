<?php

namespace app\models;

class UsuarioHabilidade{
    private int $idUsuario;
    private int $idHabilidade;

    public function __construct(int $idUsuario, int $idHabilidade) {
        $this->idUsuario = $idUsuario;
        $this->idHabilidade = $idHabilidade;
    }

    public function getIdUsuario(): int {
        return $this->idUsuario;
    }

    public function getIdHabilidade(): int {
        return $this->idHabilidade;
    }

    public function toArray(): array {
        return [
            'idUsuario' => $this->idUsuario,
            'idHabilidade' => $this->idHabilidade
        ];
    }
}