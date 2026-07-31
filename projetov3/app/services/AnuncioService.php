<?php

namespace app\services;

use app\models\Anuncio;
use app\repositories\AnuncioRepository;
use Exception;

class AnuncioService
{
    private AnuncioRepository $repository;

    public function __construct()
    {
        $this->repository = new AnuncioRepository();
    }

    /**
     * Criar novo anúncio
     */
    public function criar(
        int $idContratante,
        string $titulo,
        string $descricao,
        ?string $localizacao = null,
        ?string $data = null,
        ?float $remuneracao = null,
        ?string $tipoServico = null,
        ?string $duracao = null,
        ?string $observacoes = null,
        ?string $prazoCandidatura = null
    ): int {
        $anuncio = new Anuncio(
            0,
            $idContratante,
            $titulo,
            $descricao,
            'ABERTO',
            $localizacao,
            $data,
            $remuneracao,
            $tipoServico,
            $duracao,
            $observacoes,
            $prazoCandidatura
        );

        return $this->repository->criar($anuncio);
    }

    /**
     * Buscar anúncio por ID
     */
    public function buscarPorId(int $id): ?Anuncio
    {
        return $this->repository->buscarPorId($id);
    }

    /**
     * Listar anúncios abertos
     */
    public function listar(int $limit = 50, int $offset = 0): array
    {
        return $this->repository->listar($limit, $offset);
    }

    /**
     * Listar anúncios de um contratante
     */
    public function listarPorContratante(int $idContratante): array
    {
        return $this->repository->listarPorContratante($idContratante);
    }

    /**
     * Buscar anúncios por critérios
     */
    public function buscar(string $titulo = '', string $localizacao = '', ?string $tipoServico = null): array
    {
        return $this->repository->buscar($titulo, $localizacao, $tipoServico);
    }

    /**
     * Atualizar anúncio
     */
    public function atualizar(Anuncio $anuncio): bool
    {
        $existing = $this->repository->buscarPorId($anuncio->getIdAnuncio());
        if (!$existing) {
            throw new Exception('Anúncio não encontrado.');
        }
        return $this->repository->atualizar($anuncio);
    }

    /**
     * Deletar anúncio
     */
    public function deletar(int $idAnuncio): bool
    {
        $existing = $this->repository->buscarPorId($idAnuncio);
        if (!$existing) {
            throw new Exception('Anúncio não encontrado.');
        }
        return $this->repository->deletar($idAnuncio);
    }

    /**
     * Encerrar anúncio
     */
    public function encerrar(int $idAnuncio): bool
    {
        return $this->repository->mudarStatus($idAnuncio, 'ENCERRADO');
    }

    /**
     * Reabrir anúncio
     */
    public function reabrir(int $idAnuncio): bool
    {
        return $this->repository->mudarStatus($idAnuncio, 'ABERTO');
    }
}
