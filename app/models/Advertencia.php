<?php

namespace app\models;

class Advertencia
{
    private int $idAdvertencia;
    private int $idUsuario;
    private ?int $idDenuncia;
    private string $mensagem;
    private ?string $dataAdvertencia;
    private ?string $visualizadaEm;

    public function __construct(
        int $idAdvertencia,
        int $idUsuario,
        ?int $idDenuncia,
        string $mensagem,
        ?string $dataAdvertencia = null,
        ?string $visualizadaEm = null
    ) {
        $this->idAdvertencia = $idAdvertencia;
        $this->idUsuario = $idUsuario;
        $this->idDenuncia = $idDenuncia;
        $this->mensagem = $mensagem;
        $this->dataAdvertencia = $dataAdvertencia;
        $this->visualizadaEm = $visualizadaEm;
    }

    public static function arrayParaObjeto(array $dados): Advertencia
    {
        return new Advertencia(
            (int)$dados['id_advertencia'],
            (int)$dados['id_usuario'],
            isset($dados['id_denuncia']) ? (int)$dados['id_denuncia'] : null,
            $dados['mensagem'],
            $dados['data_advertencia'] ?? null,
            $dados['visualizada_em'] ?? null
        );
    }

    public function getIdAdvertencia(): int
    {
        return $this->idAdvertencia;
    }

    public function getIdUsuario(): int
    {
        return $this->idUsuario;
    }

    public function getIdDenuncia(): ?int
    {
        return $this->idDenuncia;
    }

    public function getMensagem(): string
    {
        return $this->mensagem;
    }

    public function getDataAdvertencia(): ?string
    {
        return $this->dataAdvertencia;
    }

    public function foiVisualizada(): bool
    {
        return $this->visualizadaEm !== null;
    }
}
