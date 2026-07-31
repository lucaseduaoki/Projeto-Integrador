<?php

namespace app\models;

class Denuncia
{
    private int $idDenuncia;
    private int $idDenunciante;
    private ?int $idUsuarioDenunciado;
    private ?int $idVagaDenunciada;
    private string $motivo;
    private ?string $descricao;
    private string $status;
    private ?string $dataDenuncia;

    public function __construct(
        int $idDenuncia,
        int $idDenunciante,
        ?int $idUsuarioDenunciado,
        ?int $idVagaDenunciada,
        string $motivo,
        ?string $descricao = null,
        string $status = 'PENDENTE',
        ?string $dataDenuncia = null
    ) {
        $this->idDenuncia = $idDenuncia;
        $this->idDenunciante = $idDenunciante;
        $this->idUsuarioDenunciado = $idUsuarioDenunciado;
        $this->idVagaDenunciada = $idVagaDenunciada;
        $this->motivo = $motivo;
        $this->descricao = $descricao;
        $this->status = $status;
        $this->dataDenuncia = $dataDenuncia;
    }

    public static function arrayParaObjeto(array $dados): Denuncia
    {
        return new Denuncia(
            (int)$dados['id_denuncia'],
            (int)$dados['id_denunciante'],
            isset($dados['id_usuario_denunciado'])
                ? (int)$dados['id_usuario_denunciado']
                : null,
            isset($dados['id_vaga_denunciada'])
                ? (int)$dados['id_vaga_denunciada']
                : null,
            $dados['motivo'],
            $dados['descricao'] ?? null,
            $dados['status'] ?? 'PENDENTE',
            $dados['data_denuncia'] ?? null
        );
    }

    public function getIdDenuncia(): int
    {
        return $this->idDenuncia;
    }

    public function getIdDenunciante(): int
    {
        return $this->idDenunciante;
    }

    public function getIdUsuarioDenunciado(): ?int
    {
        return $this->idUsuarioDenunciado;
    }

    public function getIdVagaDenunciada(): ?int
    {
        return $this->idVagaDenunciada;
    }

    public function getMotivo(): string
    {
        return $this->motivo;
    }

    public function getDescricao(): ?string
    {
        return $this->descricao;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getDataDenuncia(): ?string
    {
        return $this->dataDenuncia;
    }

    public function setMotivo(string $motivo): void
    {
        $this->motivo = $motivo;
    }

    public function setDescricao(?string $descricao): void
    {
        $this->descricao = $descricao;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function isPendente(): bool
    {
        return $this->status === 'PENDENTE';
    }

    public function isAprovada(): bool
    {
        return $this->status === 'APROVADA';
    }

    public function isRejeitada(): bool
    {
        return $this->status === 'REJEITADA';
    }

    public function toArray(): array
    {
        return [
            'idDenuncia' => $this->idDenuncia,
            'idDenunciante' => $this->idDenunciante,
            'idUsuarioDenunciado' => $this->idUsuarioDenunciado,
            'idVagaDenunciada' => $this->idVagaDenunciada,
            'motivo' => $this->motivo,
            'descricao' => $this->descricao,
            'status' => $this->status,
            'dataDenuncia' => $this->dataDenuncia
        ];
    }

    public function toObject(): object
    {
        return (object)$this->toArray();
    }
}