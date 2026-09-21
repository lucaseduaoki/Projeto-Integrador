<?php

namespace app\models;

class Interesse
{
    private ?int $idInteresse;
    private int $idVaga;
    private int $idTrabalhador;
    private string $status;
    private ?string $dataInteresse;
    private ?string $nomeTrabalhador = null;

    public function __construct(
        int $idVaga,
        int $idTrabalhador,
        string $status = 'PENDENTE',
        ?string $dataInteresse = null,
        ?int $idInteresse = null
    ) {
        $this->idInteresse = $idInteresse;
        $this->idVaga = $idVaga;
        $this->idTrabalhador = $idTrabalhador;
        $this->status = $status;
        $this->dataInteresse = $dataInteresse;
    }

    /**
     * Nome do trabalhador, quando a consulta o traz junto (nunca inclui contato).
     */
    public function getNomeTrabalhador(): ?string
    {
        return $this->nomeTrabalhador;
    }

    public function setNomeTrabalhador(?string $nomeTrabalhador): void
    {
        $this->nomeTrabalhador = $nomeTrabalhador;
    }

    public function getIdInteresse(): ?int
    {
        return $this->idInteresse;
    }

    public function getIdVaga(): int
    {
        return $this->idVaga;
    }

    public function getIdTrabalhador(): int
    {
        return $this->idTrabalhador;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getDataInteresse(): ?string
    {
        return $this->dataInteresse;
    }

    public function setIdInteresse(?int $idInteresse): void
    {
        $this->idInteresse = $idInteresse;
    }

    public function setIdVaga(int $idVaga): void
    {
        $this->idVaga = $idVaga;
    }

    public function setIdTrabalhador(int $idTrabalhador): void
    {
        $this->idTrabalhador = $idTrabalhador;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function setDataInteresse(?string $dataInteresse): void
    {
        $this->dataInteresse = $dataInteresse;
    }

    public function toArray(): array
    {
        return [
            'idInteresse' => $this->idInteresse,
            'idVaga' => $this->idVaga,
            'idTrabalhador' => $this->idTrabalhador,
            'status' => $this->status,
            'dataInteresse' => $this->dataInteresse,
        ];
    }

    public function toObject(): object
    {
        return (object) $this->toArray();
    }

    public static function arrayParaObjeto(array $dados): self
    {
        $interesse = new self(
            $dados['id_vaga'],
            $dados['id_trabalhador'],
            $dados['status'],
            $dados['data_interesse'],
            $dados['id_interesse']
        );

        $interesse->setNomeTrabalhador($dados['nome_trabalhador'] ?? null);

        return $interesse;
    }
}