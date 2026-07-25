<?php

namespace app\services;

use app\models\Avaliacao;
use app\repositories\AvaliacaoRepository;
use Exception;

class AvaliacaoService
{
    private AvaliacaoRepository $repository;

    public function __construct()
    {
        $this->repository = new AvaliacaoRepository();
    }

    /**
     * Criar avaliação
     * RN16: Cada usuário avalia uma vez por serviço
     */
    public function criar(
        int $idAvaliador,
        int $idAvaliado,
        int $idAnuncio,
        int $nota,
        ?string $comentario = null
    ): int {
        // RN16: Verificar se já existe avaliação
        $existente = $this->repository->buscarPorAvaliadorAvaliadoAnuncio(
            $idAvaliador,
            $idAvaliado,
            $idAnuncio
        );

        if ($existente) {
            throw new Exception('Você já avaliou este usuário neste serviço.');
        }

        $avaliacao = new Avaliacao(
            0,
            $idAvaliador,
            $idAvaliado,
            $idAnuncio,
            $nota,
            date('Y-m-d H:i:s'),
            $comentario
        );

        return $this->repository->criar($avaliacao);
    }

    /**
     * Buscar avaliação por ID
     */
    public function buscarPorId(int $id): ?Avaliacao
    {
        return $this->repository->buscarPorId($id);
    }

    /**
     * Listar avaliações de um usuário
     */
    public function listarPorAvaliado(int $idAvaliado): array
    {
        return $this->repository->listarPorAvaliado($idAvaliado);
    }

    /**
     * Listar avaliações de um serviço
     */
    public function listarPorAnuncio(int $idAnuncio): array
    {
        return $this->repository->listarPorAnuncio($idAnuncio);
    }

    /**
     * Atualizar avaliação
     */
    public function atualizar(Avaliacao $avaliacao): bool
    {
        $existing = $this->repository->buscarPorId($avaliacao->getIdAvaliacao());
        if (!$existing) {
            throw new Exception('Avaliação não encontrada.');
        }
        return $this->repository->atualizar($avaliacao);
    }

    /**
     * Calcular média de avaliações
     */
    public function calcularMedia(int $idUsuario): float
    {
        return $this->repository->calcularMediaNotas($idUsuario);
    }

    /**
     * Contar avaliações
     */
    public function contarAvaliacoes(int $idUsuario): int
    {
        return $this->repository->contarAvaliacoes($idUsuario);
    }
}
