<?php

namespace app\services;

use app\models\Vaga;
use app\repositories\VagaRepository;
use app\repositories\UsuarioRepository;
use Exception;

class VagaService
{
    private VagaRepository $repository;
    private UsuarioRepository $usuarioRepository;

    public function __construct()
    {
        $this->repository = new VagaRepository();
        $this->usuarioRepository = new UsuarioRepository();

    }

    /**
     * Criar vaga
     */
    public function criar(
        int $idContratante,
        int $idCategoria,
        string $titulo,
        string $descricao,
        ?string $localizacao = null,
        ?float $remuneracao = null,
        ?string $dataLimite = null,
        ?string $trabalhadoresLimite = null
    ): int {

        $vaga = new Vaga(
            0,
            $idContratante,
            $idCategoria,
            $titulo,
            $descricao,
            $localizacao,
            $remuneracao,
            null,
            $dataLimite,
            $trabalhadoresLimite
        );

        return $this->repository->criar($vaga);
    }



    /**
     * Buscar vaga
     */
    public function buscarPorId(int $id): ?Vaga
    {
        return $this->repository->buscarPorId($id);
    }

    /**
     * Listar vagas ativas
     */
    public function listar(int $limit = 50, int $offset = 0): array
    {
        return $this->repository->listar($limit, $offset);
    }

    /**
     * Listar vagas do contratante
     */
    public function listarPorContratante(int $idContratante): array
    {
        return $this->repository->listarPorContratante($idContratante);
    }

    /**
     * Buscar vagas
     */
    public function buscar(
        string $titulo = '',
        string $localizacao = ''
    ): array {

        return $this->repository->buscar(
            $titulo,
            $localizacao
        );
    }

    public function buscarContratantePorVaga(int $idVaga): ?object
    {
        return $this->usuarioRepository->buscarContratantePorVaga($idVaga);
    }

    /**
     * Atualizar vaga
     */
    public function atualizar(Vaga $vaga): bool
    {
        $vagaExistente = $this->repository->buscarPorId($vaga->getIdVaga());

        if (!$vagaExistente) {
            throw new Exception("Vaga não encontrada.");
        }

        return $this->repository->atualizar($vaga);
    }

    /**
     * Excluir vaga
     */
    public function deletar(int $idVaga): bool
    {
        $vagaExistente = $this->repository->buscarPorId($idVaga);

        if (!$vagaExistente) {
            throw new Exception("Vaga não encontrada.");
        }

        return $this->repository->deletar($idVaga);
    }

    /**
     * Encerrar vaga
     */
    public function encerrar(int $idVaga): bool
    {
        return $this->repository->mudarStatus(
            $idVaga,
            'ENCERRADA'
        );
    }

    /**
     * Reabrir vaga
     */
    public function reabrir(int $idVaga): bool
    {
        return $this->repository->mudarStatus(
            $idVaga,
            'ATIVA'
        );
    }
}