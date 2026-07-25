<?php

namespace app\models;

class Anuncio
{
    private int $idAnuncio;
    private int $idContratante;
    private string $titulo;
    private string $descricao;
    private ?string $localizacao;
    private ?string $data;
    private ?float $remuneracao;
    private ?string $tipoServico;
    private ?string $duracao;
    private ?string $observacoes;
    private ?string $prazoCandidatura;
    private string $status;

    public function __construct(
        int $idAnuncio,
        int $idContratante,
        string $titulo,
        string $descricao,
        string $status = 'ABERTO',
        ?string $localizacao = null,
        ?string $data = null,
        ?float $remuneracao = null,
        ?string $tipoServico = null,
        ?string $duracao = null,
        ?string $observacoes = null,
        ?string $prazoCandidatura = null
    ) {
        $this->idAnuncio = $idAnuncio;
        $this->idContratante = $idContratante;
        $this->titulo = $titulo;
        $this->descricao = $descricao;
        $this->localizacao = $localizacao;
        $this->data = $data;
        $this->remuneracao = $remuneracao;
        $this->tipoServico = $tipoServico;
        $this->duracao = $duracao;
        $this->observacoes = $observacoes;
        $this->prazoCandidatura = $prazoCandidatura;
        $this->status = $status;
    }

    // Getters
    public function getIdAnuncio(): int
    {
        return $this->idAnuncio;
    }

    public function getId(): int
    {
        return $this->idAnuncio;
    }

    public function getIdContratante(): int
    {
        return $this->idContratante;
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

    public function getData(): ?string
    {
        return $this->data;
    }

    public function getRemuneracao(): ?float
    {
        return $this->remuneracao;
    }

    public function getTipoServico(): ?string
    {
        return $this->tipoServico;
    }

    public function getDuracao(): ?string
    {
        return $this->duracao;
    }

    public function getObservacoes(): ?string
    {
        return $this->observacoes;
    }

    public function getPrazoCandidatura(): ?string
    {
        return $this->prazoCandidatura;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    // Setters
    public function setTitulo(string $titulo): self
    {
        $this->titulo = $titulo;
        return $this;
    }

    public function setDescricao(string $descricao): self
    {
        $this->descricao = $descricao;
        return $this;
    }

    public function setLocalizacao(?string $localizacao): self
    {
        $this->localizacao = $localizacao;
        return $this;
    }

    public function setData(?string $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function setRemuneracao(?float $remuneracao): self
    {
        $this->remuneracao = $remuneracao;
        return $this;
    }

    public function setTipoServico(?string $tipoServico): self
    {
        $this->tipoServico = $tipoServico;
        return $this;
    }

    public function setDuracao(?string $duracao): self
    {
        $this->duracao = $duracao;
        return $this;
    }

    public function setObservacoes(?string $observacoes): self
    {
        $this->observacoes = $observacoes;
        return $this;
    }

    public function setPrazoCandidatura(?string $prazoCandidatura): self
    {
        $this->prazoCandidatura = $prazoCandidatura;
        return $this;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function isAberto(): bool
    {
        return $this->status === 'ABERTO';
    }

    public function isEncerrado(): bool
    {
        return $this->status === 'ENCERRADO';
    }

    // Método estático para converter array em objeto
    public static function arrayParaObjeto(array $data): static
    {
        return new self(
            $data['id_anuncio'],
            $data['id_contratante'],
            $data['titulo'],
            $data['descricao'],
            $data['status'] ?? 'ABERTO',
            $data['localizacao'] ?? null,
            $data['data'] ?? null,
            isset($data['remuneracao']) ? (float)$data['remuneracao'] : null,
            $data['tipo_servico'] ?? null,
            $data['duracao'] ?? null,
            $data['observacoes'] ?? null,
            $data['prazo_candidatura'] ?? null
        );
    }
}
