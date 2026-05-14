<?php

namespace app\models;

class Avaliacao
{
    private int $idAvaliacao;
    private int $idAvaliador;
    private int $idAvaliado;
    private int $idAnuncio;
    private int $nota;
    private ?string $comentario;
    private string $data;

    public function __construct(
        int $idAvaliacao,
        int $idAvaliador,
        int $idAvaliado,
        int $idAnuncio,
        int $nota,
        string $data = '',
        ?string $comentario = null
    ) {
        $this->idAvaliacao = $idAvaliacao;
        $this->idAvaliador = $idAvaliador;
        $this->idAvaliado = $idAvaliado;
        $this->idAnuncio = $idAnuncio;
        $this->nota = $nota;
        $this->data = $data ?: date('Y-m-d H:i:s');
        $this->comentario = $comentario;
    }

    // Getters
    public function getIdAvaliacao(): int
    {
        return $this->idAvaliacao;
    }

    public function getIdAvaliador(): int
    {
        return $this->idAvaliador;
    }

    public function getIdAvaliado(): int
    {
        return $this->idAvaliado;
    }

    public function getIdAnuncio(): int
    {
        return $this->idAnuncio;
    }

    public function getNota(): int
    {
        return $this->nota;
    }

    public function getComentario(): ?string
    {
        return $this->comentario;
    }

    public function getData(): string
    {
        return $this->data;
    }

    // Setters
    public function setNota(int $nota): self
    {
        if ($nota < 1 || $nota > 5) {
            throw new \InvalidArgumentException('Nota deve estar entre 1 e 5');
        }
        $this->nota = $nota;
        return $this;
    }

    public function setComentario(?string $comentario): self
    {
        $this->comentario = $comentario;
        return $this;
    }

    // Método estático para converter array em objeto
    public static function arrayParaObjeto(array $data): static
    {
        return new self(
            $data['id_avaliacao'],
            $data['id_avaliador'],
            $data['id_avaliado'],
            $data['id_anuncio'],
            (int)$data['nota'],
            $data['data'] ?? date('Y-m-d H:i:s'),
            $data['comentario'] ?? null
        );
    }
}
