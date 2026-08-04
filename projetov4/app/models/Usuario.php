<?php

namespace app\models;

class Usuario
{
    private int $idUsuario;
    private string $nome;
    private string $email;
    private string $senha;
    private ?string $telefone;
    private string $tipoUsuario;
    private ?string $fotoPerfil;
    private ?string $descricao;
    private ?string $documento;
    private int $ativo;
    private string $dataCadastro;

    public function __construct(
        int $idUsuario,
        string $nome,
        string $email,
        string $senha,
        string $tipoUsuario,
        ?string $telefone = null,
        ?string $fotoPerfil = null,
        ?string $descricao = null,
        ?string $documento = null,
        int $ativo = 1,
        string $dataCadastro = ''
    ) {
        $this->idUsuario = $idUsuario;
        $this->nome = $nome;
        $this->email = $email;
        $this->senha = $senha;
        $this->tipoUsuario = $tipoUsuario;
        $this->telefone = $telefone;
        $this->fotoPerfil = $fotoPerfil;
        $this->descricao = $descricao;
        $this->documento = $documento;
        $this->ativo = $ativo;
        $this->dataCadastro = $dataCadastro ?: date('Y-m-d H:i:s');
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

    public function getTipoUsuario(): string
    {
        return $this->tipoUsuario;
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

    public function setTipoUsuario(string $tipoUsuario): self
    {
        $this->tipoUsuario = $tipoUsuario;
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
        return $this->tipoUsuario === 'ADMIN';
    }

    public function isTrabalhador(): bool
    {
        return $this->tipoUsuario === 'TRABALHADOR';
    }

    public function isContratante(): bool
    {
        return $this->tipoUsuario === 'CONTRATANTE';
    }

    // Método estático para converter array em objeto
    public static function arrayParaObjeto(array $data): static
    {
        return new self(
            $data['id_usuario'],
            $data['nome'],
            $data['email'],
            $data['senha'],
            $data['tipo_usuario'],
            $data['telefone'] ?? null,
            $data['foto_perfil'] ?? null,
            $data['descricao'] ?? null,
            $data['documento'] ?? null,
            $data['ativo'] ?? 1,
            $data['data_cadastro'] ?? date('Y-m-d H:i:s')
        );
    }
}