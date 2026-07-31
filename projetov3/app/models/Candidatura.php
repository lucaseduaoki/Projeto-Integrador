<?php

namespace app\models;

class Candidatura
{
    private int $idCandidatura;
    private int $idAnuncio;
    private int $idTrabalhador;
    private string $dataCandidatura;
    private string $status;
    private ?string $dataSelecao;

    public function __construct(
        int $idCandidatura,
        int $idAnuncio,
        int $idTrabalhador,
        string $status = 'PENDENTE',
        string $dataCandidatura = '',
        ?string $dataSelecao = null
    ) {
        $this->idCandidatura = $idCandidatura;
        $this->idAnuncio = $idAnuncio;
        $this->idTrabalhador = $idTrabalhador;
        $this->dataCandidatura = $dataCandidatura ?: date('Y-m-d H:i:s');
        $this->status = $status;
        $this->dataSelecao = $dataSelecao;
    }

    // Getters
    public function getIdCandidatura(): int
    {
        return $this->idCandidatura;
    }

    public function getIdAnuncio(): int
    {
        return $this->idAnuncio;
    }

    public function getIdTrabalhador(): int
    {
        return $this->idTrabalhador;
    }

    public function getDataCandidatura(): string
    {
        return $this->dataCandidatura;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getDataSelecao(): ?string
    {
        return $this->dataSelecao;
    }

    // Setters
    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function setDataSelecao(?string $dataSelecao): self
    {
        $this->dataSelecao = $dataSelecao;
        return $this;
    }

    // Métodos utilitários
    public function isPendente(): bool
    {
        return $this->status === 'PENDENTE';
    }

    public function isAceito(): bool
    {
        return $this->status === 'ACEITO';
    }

    public function isRecusado(): bool
    {
        return $this->status === 'RECUSADO';
    }

    // Método estático para converter array em objeto
    public static function arrayParaObjeto(array $data): static
    {
        return new self(
            $data['id_candidatura'],
            $data['id_anuncio'],
            $data['id_trabalhador'],
            $data['status'] ?? 'PENDENTE',
            $data['data_candidatura'] ?? date('Y-m-d H:i:s'),
            $data['data_selecao'] ?? null
        );
    }
}
