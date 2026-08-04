<?php

namespace app\models;

class Empresa
{
    private int $idPerfil;
    private int $idUsuario;
    private ?string $nomeFantasia;
    private ?string $localizacao;

    public function __construct(
        int $idPerfil,
        int $idUsuario,
        ?string $nomeFantasia = null,
        ?string $localizacao = null
    ) {
        $this->idPerfil = $idPerfil;
        $this->idUsuario = $idUsuario;
        $this->nomeFantasia = $nomeFantasia;
        $this->localizacao = $localizacao;
    }

    // Getters
    public function getIdPerfil(): int
    {
        return $this->idPerfil;
    }

    public function getIdUsuario(): int
    {
        return $this->idUsuario;
    }

    public function getNomeFantasia(): ?string
    {
        return $this->nomeFantasia;
    }

    public function getLocalizacao(): ?string
    {
        return $this->localizacao;
    }

    // Setters
    public function setNomeFantasia(?string $nomeFantasia): self
    {
        $this->nomeFantasia = $nomeFantasia;
        return $this;
    }

    public function setLocalizacao(?string $localizacao): self
    {
        $this->localizacao = $localizacao;
        return $this;
    }

    // Método estático para converter array em objeto
    public static function arrayParaObjeto(array $data): static
    {
        return new self(
            $data['id_perfil'],
            $data['id_usuario'],
            $data['nome_fantasia'] ?? null,
            $data['localizacao'] ?? null
        );
    }
}
