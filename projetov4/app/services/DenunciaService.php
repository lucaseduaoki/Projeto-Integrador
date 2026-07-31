<?php

namespace app\services;

use app\models\Denuncia;
use app\repositories\DenunciaRepository;
use app\repositories\UsuarioRepository;
use Exception;

class DenunciaService
{
    private DenunciaRepository $repository;
    private UsuarioRepository $usuarioRepository;

    public function __construct()
    {
        $this->repository = new DenunciaRepository();
        $this->usuarioRepository = new UsuarioRepository();
    }

    /**
     * Criar denúncia
     * RN19: motivo é obrigatório
     */
    public function criar(
        int $idDenunciante,
        int $idDenunciado,
        string $motivo,
        ?string $descricao = null,
        ?int $idVaga = null
    ): int {
        error_log("motivo recebido: " . $motivo); // Log the received motivo
        if (empty($motivo)) {
            throw new Exception('Motivo da denúncia é obrigatório.');
        }


        $denuncia = new Denuncia(
            0,
            $idDenunciante,
            $idDenunciado,
            $idVaga,
            $motivo,
            $descricao,
            'PENDENTE',
            date('Y-m-d H:i:s')
        );
        error_log("Denúncia criada: " . print_r($denuncia, true)); // Log the created Denuncia object
        return $this->repository->criar($denuncia);
    }

    /**
     * Buscar denúncia por ID
     */
    public function buscarPorId(int $id): ?Denuncia
    {
        return $this->repository->buscarPorId($id);
    }

    /**
     * Listar denúncias pendentes (admin)
     */
    public function listarPendentes(): array
    {
        return $this->repository->listarPendentes();
    }

    /**
     * Listar todas as denúncias (admin)
     */
    public function listarTodas(): array
    {
        return $this->repository->listarTodas();
    }

    /**
     * Listar denúncias feitas por usuário
     */
    public function listarPorDenunciante(int $idDenunciante): array
    {
        return $this->repository->listarPorDenunciante($idDenunciante);
    }

    /**
     * Listar denúncias contra usuário
     */
    public function listarPorDenunciado(int $idDenunciado): array
    {
        return $this->repository->listarPorDenunciado($idDenunciado);
    }

    /**
     * Moderar denúncia (admin) - bloquear usuário
     */
    public function bloquearPorDenuncia(int $idDenuncia): bool
    {
        $denuncia = $this->repository->buscarPorId($idDenuncia);
        if (!$denuncia) {
            throw new Exception('Denúncia não encontrada.');
        }

        // Bloquear usuário denunciado
        $this->usuarioRepository->bloquear($denuncia->getIdUsuarioDenunciado());

        // Marcar denúncia como analisada
        return $this->repository->mudarStatus($idDenuncia, 'ANALISADO');
    }

    /**
     * Moderar denúncia (admin) - apenas marcar como analisada
     */
    public function analisar(int $idDenuncia): bool
    {
        $denuncia = $this->repository->buscarPorId($idDenuncia);
        if (!$denuncia) {
            throw new Exception('Denúncia não encontrada.');
        }

        return $this->repository->mudarStatus($idDenuncia, 'ANALISADO');
    }

    /**
     * Contar denúncias contra um usuário
     */
    public function contarDenuncias(int $idUsuario): int
    {
        return $this->repository->contarDenunciasAoPorUsuario($idUsuario);
    }
}
