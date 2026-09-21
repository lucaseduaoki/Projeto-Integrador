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
    private int $totalAceitos = 0;
    private bool $isUserActive = true;
    private ?string $horario = null;
    private string $tipoServico = 'FIXO';
    private ?string $duracao = null;
    private ?string $observacoes = null;
    private ?string $dataServico = null;
    private string $visibilidade = 'VISIVEL';
    private ?string $categoriaNome = null;

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
    string $status = 'ATIVA',
    int $totalAceitos = 0,
    bool $isUserActive = true,
    ?string $horario = null,
    string $tipoServico = 'FIXO',
    ?string $duracao = null,
    ?string $observacoes = null,
    ?string $dataServico = null,
    string $visibilidade = 'VISIVEL',
    ?string $categoriaNome = null
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
        $this->totalAceitos = $totalAceitos;
        $this->isUserActive = $isUserActive;
        $this->horario = $horario;
        $this->tipoServico = $tipoServico;
        $this->duracao = $duracao;
        $this->observacoes = $observacoes;
        $this->dataServico = $dataServico;
        $this->visibilidade = $visibilidade;
        $this->categoriaNome = $categoriaNome;
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
            $dados['status'] ?? 'ATIVA',
            isset($dados['total_aceitos']) ? (int)$dados['total_aceitos'] : 0,
            (bool)($dados['is_user_active'] ?? true),
            isset($dados['horario']) ? substr($dados['horario'], 0, 5) : null,
            $dados['tipo_servico'] ?? 'FIXO',
            $dados['duracao'] ?? null,
            $dados['observacoes'] ?? null,
            $dados['data_servico'] ?? null,
            $dados['visibilidade'] ?? 'VISIVEL',
            $dados['categoria_nome'] ?? null
        );
    }

    /**
     * O contratante dono da vaga está ativo? (campo mantido por trigger no banco)
     */
    public function isUserActive(): bool
    {
        return $this->isUserActive;
    }

    /**
     * Vaga realmente disponível: status ATIVA, contratante ativo e não oculta/removida pela moderação.
     */
    public function estaDisponivel(): bool
    {
        return $this->status === 'ATIVA' && $this->isUserActive && $this->estaVisivel();
    }

    public function getTotalAceitos(): int
    {
        return $this->totalAceitos;
    }

    public function setTotalAceitos(int $totalAceitos): void
    {
        $this->totalAceitos = $totalAceitos;
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

    public function getTipoServico(): string
    {
        return $this->tipoServico;
    }

    public function setTipoServico(string $tipoServico): void
    {
        $this->tipoServico = $tipoServico;
    }

    public function getDuracao(): ?string
    {
        return $this->duracao;
    }

    public function setDuracao(?string $duracao): void
    {
        $this->duracao = $duracao;
    }

    /**
     * Nome da categoria, quando a consulta o traz junto (JOIN com categoria).
     */
    public function getCategoriaNome(): ?string
    {
        return $this->categoriaNome;
    }

    public function getVisibilidade(): string
    {
        return $this->visibilidade;
    }

    public function estaVisivel(): bool
    {
        return $this->visibilidade === 'VISIVEL';
    }

    public function foiRemovidaPelaModeracao(): bool
    {
        return $this->visibilidade === 'REMOVIDA';
    }

    public function getDataServico(): ?string
    {
        return $this->dataServico;
    }

    public function setDataServico(?string $dataServico): void
    {
        $this->dataServico = $dataServico;
    }

    public function getObservacoes(): ?string
    {
        return $this->observacoes;
    }

    public function setObservacoes(?string $observacoes): void
    {
        $this->observacoes = $observacoes;
    }

    public function isTemporario(): bool
    {
        return $this->tipoServico === 'TEMPORARIO';
    }

    public function getHorario(): ?string
    {
        return $this->horario;
    }

    public function setHorario(?string $horario): void
    {
        $this->horario = $horario;
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

    public function setIdCategoria(int $idCategoria): void
    {
        $this->idCategoria = $idCategoria;
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
            'status' => $this->status,
            'isUserActive' => $this->isUserActive,
            'horario' => $this->horario,
            'tipoServico' => $this->tipoServico,
            'duracao' => $this->duracao,
            'observacoes' => $this->observacoes,
            'dataServico' => $this->dataServico,
            'visibilidade' => $this->visibilidade,
            'categoriaNome' => $this->categoriaNome
        ];
    }

    public function toObject(): object
    {
        return (object)$this->toArray();
    }
}