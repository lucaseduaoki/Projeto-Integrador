<?php

namespace app\models;

class Usuario
{
    private int $idUsuario;
    private string $nome;
    private string $email;
    private string $senha;
    private ?string $telefone;
    private bool $isAdmin = false;
    private bool $isTrabalhador = false;
    private bool $isContratante = false;
    private ?string $fotoPerfil;
    private ?string $descricao;
    private ?string $documento;
    private int $ativo;
    private string $dataCadastro;
    private string $tipoPessoa;
    private ?string $nomeResponsavel;

    public function __construct(
        int $idUsuario,
        string $nome,
        string $email,
        string $senha,
        bool $isAdmin,
        bool $isTrabalhador,
        bool $isContratante,
        ?string $telefone = null,
        ?string $fotoPerfil = null,
        ?string $descricao = null,
        ?string $documento = null,
        int $ativo = 1,
        string $dataCadastro = '',
        string $tipoPessoa = 'PF',
        ?string $nomeResponsavel = null
    ) {
        $this->idUsuario = $idUsuario;
        $this->nome = $nome;
        $this->email = $email;
        $this->senha = $senha;
        $this->isAdmin = $isAdmin;
        $this->isTrabalhador = $isTrabalhador;
        $this->isContratante = $isContratante;
        $this->telefone = $telefone;
        $this->fotoPerfil = $fotoPerfil;
        $this->descricao = $descricao;
        $this->documento = $documento;
        $this->ativo = $ativo;
        $this->dataCadastro = $dataCadastro ?: date('Y-m-d H:i:s');
        $this->tipoPessoa = $tipoPessoa;
        $this->nomeResponsavel = $nomeResponsavel;
    }

    // Getters
    public function getLocalizacao(): ?string
    {
        return "Foz do Iguaçu, PR"; // Retorna a localização fixa
    }
    
    public function getIdUsuario(): int
    {
        return $this->idUsuario;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getSenha(): string
    {
        return $this->senha;
    }

    public function getTelefone(): ?string
    {
        return $this->telefone;
    }

    /**
     * Rótulo dos papéis para exibição (ex.: "Trabalhador e contratante").
     */
    public function getRotuloPapeis(): string
    {
        if ($this->isAdmin) {
            return 'Admin';
        }

        if ($this->isTrabalhador && $this->isContratante) {
            return 'Trabalhador e contratante';
        }

        return $this->isContratante ? 'Contratante' : 'Trabalhador';
    }

    public function getTipoPessoa(): string
    {
        return $this->tipoPessoa;
    }

    public function getNomeResponsavel(): ?string
    {
        return $this->nomeResponsavel;
    }

    public function setNomeResponsavel(?string $nomeResponsavel): self
    {
        $this->nomeResponsavel = $nomeResponsavel;
        return $this;
    }

    public function isPessoaJuridica(): bool
    {
        return $this->tipoPessoa === 'PJ';
    }

    public function getFotoPerfil(): ?string
    {
        return $this->fotoPerfil;
    }

    public function getDescricao(): ?string
    {
        return $this->descricao;
    }

    public function getDocumento(): ?string
    {
        return $this->documento;
    }

    public function getAtivo(): int
    {
        return $this->ativo;
    }

    public function getDataCadastro(): string
    {
        return $this->dataCadastro;
    }

    // Setters
    public function setLocalizacao(?string $localizacao): self
    {
        $this->localizacao = $localizacao;
        return $this;
    }
    public function setNome(string $nome): self
    {
        $this->nome = $nome;
        return $this;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function setSenha(string $senha): self
    {
        $this->senha = $senha;
        return $this;
    }

    public function setTelefone(?string $telefone): self
    {
        $this->telefone = $telefone;
        return $this;
    }

    public function setIsTrabalhador(bool $valor): self
    {
        $this->isTrabalhador = $valor;
        return $this;
    }

    public function setIsContratante(bool $valor): self
    {
        $this->isContratante = $valor;
        return $this;
    }

    public function setFotoPerfil(?string $fotoPerfil): self
    {
        $this->fotoPerfil = $fotoPerfil;
        return $this;
    }

    public function setDescricao(?string $descricao): self
    {
        $this->descricao = $descricao;
        return $this;
    }

    public function setDocumento(?string $documento): self
    {
        $this->documento = $documento;
        return $this;
    }

    public function setAtivo(int $ativo): self
    {
        $this->ativo = $ativo;
        return $this;
    }

    public function setIdUsuario(int $idUsuario): self
    {
        $this->idUsuario = $idUsuario;
        return $this;
    }

    // Métodos utilitários
    public function isAtivo(): bool
    {
        return $this->ativo === 1;
    }

    public function isAdmin(): bool
    {
        return $this->isAdmin;
    }

    public function isTrabalhador(): bool
    {
        return $this->isTrabalhador;
    }

    public function isContratante(): bool
    {
        return $this->isContratante;
    }

    // Método estático para converter array em objeto
    public static function arrayParaObjeto(array $data): static
    {
        return new self(
            $data['id_usuario'],
            $data['nome'],
            $data['email'],
            $data['senha'],
            (bool)$data['is_admin'],
            (bool)$data['is_trabalhador'],
            (bool)$data['is_contratante'],
            $data['telefone'] ?? null,
            $data['foto_perfil'] ?? null,
            $data['descricao'] ?? null,
            $data['documento'] ?? null,
            $data['ativo'] ?? 1,
            $data['data_cadastro'] ?? date('Y-m-d H:i:s'),
            $data['tipo_pessoa'] ?? 'PF',
            $data['nome_responsavel'] ?? null
        );
    }
}