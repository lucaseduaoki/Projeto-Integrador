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
        ?string $trabalhadoresLimite = null,
        ?string $horario = null,
        string $tipoServico = 'FIXO',
        ?string $duracao = null,
        ?string $observacoes = null,
        ?string $dataServico = null
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
            $trabalhadoresLimite,
            horario: $horario,
            tipoServico: $tipoServico,
            duracao: $duracao,
            observacoes: $observacoes,
            dataServico: $dataServico
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
    public function buscar(array $filtros = []): array
    {
        return $this->repository->buscar($filtros);
    }

    public function possuiCandidaturas(int $idVaga): bool
    {
        return $this->repository->possuiCandidaturas($idVaga);
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

        if ($vagaExistente->foiRemovidaPelaModeracao()) {
            throw new Exception("Este anúncio foi removido pela moderação e não pode ser editado.");
        }

        // RN18: com candidaturas, a função (título) e o tipo de serviço não podem mudar
        if ($this->repository->possuiCandidaturas($vaga->getIdVaga())) {
            if ($vaga->getTitulo() !== $vagaExistente->getTitulo()) {
                throw new Exception("Esta vaga já tem candidaturas: a função (título) não pode ser alterada.");
            }

            if ($vaga->getTipoServico() !== $vagaExistente->getTipoServico()) {
                throw new Exception("Esta vaga já tem candidaturas: o tipo de serviço não pode ser alterado.");
            }
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

        // Anúncio moderado fica preservado (evidência da denúncia): o dono não o apaga
        if (!$vagaExistente->estaVisivel()) {
            throw new Exception("Anúncios ocultos ou removidos pela moderação não podem ser excluídos.");
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