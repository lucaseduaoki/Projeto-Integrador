<?php

namespace app\models;

class Denuncia
{
    private int $idDenuncia;
    private int $idDenunciante;
    private int $idDenunciado;
    private ?int $idAnuncio;
    private string $motivo;
    private ?string $descricao;
    private string $data;
    private string $status;

    public function __construct(
        int $idDenuncia,
        int $idDenunciante,
        int $idDenunciado,
        string $motivo,
        string $status = 'PENDENTE',
        string $data = '',
        ?int $idAnuncio = null,
        ?string $descricao = null
    ) {
        $this->idDenuncia = $idDenuncia;
        $this->idDenunciante = $idDenunciante;
        $this->idDenunciado = $idDenunciado;
        $this->idAnuncio = $idAnuncio;
        $this->motivo = $motivo;
        $this->descricao = $descricao;
        $this->data = $data ?: date('Y-m-d H:i:s');
        $this->status = $status;
    }

    // Getters
    public function getIdDenuncia(): int
    {
        return $this->idDenuncia;
    }

    public function getIdDenunciante(): int
    {
        return $this->idDenunciante;
    }

    public function getIdDenunciado(): int
    {
        return $this->idDenunciado;
    }

    public function getIdAnuncio(): ?int
    {
        return $this->idAnuncio;
    }

    public function getMotivo(): string
    {
        return $this->motivo;
    }

    public function getDescricao(): ?string
    {
        return $this->descricao;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    // Setters
    public function setMotivo(string $motivo): self
    {
        $this->motivo = $motivo;
        return $this;
    }

    public function setDescricao(?string $descricao): self
    {
        $this->descricao = $descricao;
        return $this;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    // Métodos utilitários
    public function isPendente(): bool
    {
        return $this->status === 'PENDENTE';
    }

    public function isAnalisado(): bool
    {
        return $this->status === 'ANALISADO';
    }

    // Método estático para converter array em objeto
    public static function arrayParaObjeto(array $data): static
    {
        return new self(
            $data['id_denuncia'],
            $data['id_denunciante'],
            $data['id_denunciado'],
            $data['motivo'],
            $data['status'] ?? 'PENDENTE',
            $data['data'] ?? date('Y-m-d H:i:s'),
            $data['id_anuncio'] ?? null,
            $data['descricao'] ?? null
        );
    }
}
