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
        ?int $idDenunciado,
        string $motivo,
        ?string $descricao = null,
        ?int $idVaga = null
    ): int {
        if (empty($motivo)) {
            throw new Exception('Motivo da denúncia é obrigatório.');
        }

        // RN13: o motivo precisa pertencer à lista do tipo de alvo. Anúncio: só a vaga;
        // usuário: só a pessoa. Qualquer outra combinação de alvo é inválida.
        if ($idVaga !== null && $idDenunciado === null) {
            $tipo = Denuncia::TIPO_ANUNCIO;
        } elseif ($idDenunciado !== null && $idVaga === null) {
            $tipo = Denuncia::TIPO_USUARIO;
        } else {
            throw new Exception('Alvo da denúncia inválido.');
        }
        if (!array_key_exists($motivo, Denuncia::motivosPara($tipo))) {
            throw new Exception('Motivo inválido para este tipo de denúncia.');
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
        return $this->repository->mudarStatus($idDenuncia, 'ANALISADA');
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

        return $this->repository->mudarStatus($idDenuncia, 'ANALISADA');
    }

    /**
     * Contar denúncias contra um usuário
     */
    public function contarDenuncias(int $idUsuario): int
    {
        return $this->repository->contarDenunciasAoPorUsuario($idUsuario);
    }
}
