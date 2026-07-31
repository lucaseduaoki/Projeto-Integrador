<?php

namespace app\models;

class Vaga
{
    private int $idVaga;
    private int $idContratante;
    private int $idCategoria;
    private string $titulo;
    private string $descricao;
    private ?string $localizacao;
    private ?float $remuneracao;
    private ?string $dataPublicacao;
    private ?string $dataLimite;
    private ?int $trabalhadoresLimite;
    private string $status;

    public function __construct(
        int $idVaga,
        int $idContratante,
        int $idCategoria,
        string $titulo,
        string $descricao,
        ?string $localizacao = null,
        ?float $remuneracao = null,
        ?string $dataPublicacao = null,
        ?string $dataLimite = null,
        ?int $trabalhadoresLimite = null,
        string $status = 'ATIVA'
    ) {
        $this->idVaga = $idVaga;
        $this->idContratante = $idContratante;
        $this->idCategoria = $idCategoria;
        $this->titulo = $titulo;
        $this->descricao = $descricao;
        $this->localizacao = $localizacao;
        $this->remuneracao = $remuneracao;
        $this->dataPublicacao = $dataPublicacao;
        $this->dataLimite = $dataLimite;
        $this->trabalhadoresLimite = $trabalhadoresLimite;
        $this->status = $status;
    }

    public static function arrayParaObjeto(array $dados): Vaga
    {
        return new Vaga(
            (int)$dados['id_vaga'],
            (int)$dados['id_contratante'],
            (int)$dados['id_categoria'],
            $dados['titulo'],
            $dados['descricao'],
            $dados['localizacao'] ?? null,
            isset($dados['remuneracao']) ? (float)$dados['remuneracao'] : null,
            $dados['data_publicacao'] ?? null,
            $dados['data_limite'] ?? null,
            isset($dados['trabalhadores_limite']) ? (int)$dados['trabalhadores_limite'] : null,
            $dados['status'] ?? 'ATIVA'
        );
    }

    public function getIdVaga(): int
    {
        return $this->idVaga;
    }

    public function getIdContratante(): int
    {
        return $this->idContratante;
    }

    public function getIdCategoria(): int
    {
        return $this->idCategoria;
    }

    public function getTitulo(): string
    {
        return $this->titulo;
    }

    public function getDescricao(): string
    {
        return $this->descricao;
    }

    public function getLocalizacao(): ?string
    {
        return $this->localizacao;
    }

    public function getRemuneracao(): ?float
    {
        return $this->remuneracao;
    }

    public function getDataPublicacao(): ?string
    {
        return $this->dataPublicacao;
    }

    public function getDataLimite(): ?string
    {
        return $this->dataLimite;
    }

    public function getTrabalhadoresLimite(): ?int
    {
        return $this->trabalhadoresLimite;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setTitulo(string $titulo): void
    {
        $this->titulo = $titulo;
    }

    public function setDescricao(string $descricao): void
    {
        $this->descricao = $descricao;
    }

    public function setLocalizacao(?string $localizacao): void
    {
        $this->localizacao = $localizacao;
    }

    public function setRemuneracao(?float $remuneracao): void
    {
        $this->remuneracao = $remuneracao;
    }

    public function setDataLimite(?string $dataLimite): void
    {
        $this->dataLimite = $dataLimite;
    }

    public function setTrabalhadoresLimite(?int $trabalhadoresLimite): void
    {
        $this->trabalhadoresLimite = $trabalhadoresLimite;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function toArray(): array
    {
        return [
            'idVaga' => $this->idVaga,
            'idContratante' => $this->idContratante,
            'idCategoria' => $this->idCategoria,
            'titulo' => $this->titulo,
            'descricao' => $this->descricao,
            'localizacao' => $this->localizacao,
            'remuneracao' => $this->remuneracao,
            'dataPublicacao' => $this->dataPublicacao,
            'dataLimite' => $this->dataLimite,
            'trabalhadoresLimite' => $this->trabalhadoresLimite,
            'status' => $this->status
        ];
    }

    public function toObject(): object
    {
        return (object)$this->toArray();
    }
}