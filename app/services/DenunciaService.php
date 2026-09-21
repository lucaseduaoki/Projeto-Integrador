<?php

namespace app\services;

use app\models\Denuncia;
use app\database\ConnectionFactory;
use app\repositories\AdvertenciaRepository;
use app\repositories\DenunciaRepository;
use app\repositories\UsuarioRepository;
use app\repositories\VagaRepository;
use Exception;

class DenunciaService
{
    private DenunciaRepository $repository;
    private UsuarioRepository $usuarioRepository;
    private VagaRepository $vagaRepository;
    private AdvertenciaRepository $advertenciaRepository;

    public function __construct()
    {
        $this->repository = new DenunciaRepository();
        $this->usuarioRepository = new UsuarioRepository();
        $this->vagaRepository = new VagaRepository();
        $this->advertenciaRepository = new AdvertenciaRepository();
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
     * Denúncia ainda pendente, ou exceção: a moderação decide uma única vez.
     */
    private function denunciaPendente(int $idDenuncia): Denuncia
    {
        $denuncia = $this->repository->buscarPorId($idDenuncia);

        if (!$denuncia) {
            throw new Exception('Denúncia não encontrada.');
        }

        if (!$denuncia->isPendente()) {
            throw new Exception('Esta denúncia já foi analisada.');
        }

        return $denuncia;
    }

    /**
     * Usuário atingido pela denúncia: o denunciado, ou o dono do anúncio denunciado.
     */
    private function usuarioAlvo(Denuncia $denuncia): int
    {
        if ($denuncia->getIdUsuarioDenunciado() !== null) {
            return $denuncia->getIdUsuarioDenunciado();
        }

        $vaga = $denuncia->getIdVagaDenunciada() !== null
            ? $this->vagaRepository->buscarPorId($denuncia->getIdVagaDenunciada())
            : null;

        if ($vaga === null) {
            throw new Exception('O alvo da denúncia não existe mais.');
        }

        return $vaga->getIdContratante();
    }

    /**
     * Moderar denúncia (admin) - bloquear a conta do usuário atingido
     */
    public function bloquearPorDenuncia(int $idDenuncia): bool
    {
        $denuncia = $this->denunciaPendente($idDenuncia);

        $pdo = ConnectionFactory::getConnection();
        $pdo->beginTransaction();

        try {
            $this->usuarioRepository->bloquear($this->usuarioAlvo($denuncia));
            $this->repository->registrarModeracao($idDenuncia, 'BLOQUEIO');
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        return true;
    }

    /**
     * Moderar denúncia (admin) - advertir o usuário atingido (RN14).
     * A advertência fica registrada e é mostrada ao advertido até ele dispensá-la.
     */
    public function advertir(int $idDenuncia, string $mensagem, int $idModerador): bool
    {
        $mensagem = trim($mensagem);

        if (mb_strlen($mensagem) < 5 || mb_strlen($mensagem) > 500) {
            throw new Exception('A mensagem da advertência deve ter entre 5 e 500 caracteres.');
        }

        $denuncia = $this->denunciaPendente($idDenuncia);

        $pdo = ConnectionFactory::getConnection();
        $pdo->beginTransaction();

        try {
            $this->advertenciaRepository->criar(
                $this->usuarioAlvo($denuncia),
                $idDenuncia,
                $idModerador,
                $mensagem
            );
            $this->repository->registrarModeracao($idDenuncia, 'ADVERTENCIA');
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        return true;
    }

    /**
     * Moderar denúncia (admin) - apenas marcar como analisada, sem sanção
     */
    public function analisar(int $idDenuncia): bool
    {
        $this->denunciaPendente($idDenuncia);

        return $this->repository->registrarModeracao($idDenuncia, 'NENHUMA');
    }

    /**
     * Contar denúncias contra um usuário
     */
    public function contarDenuncias(int $idUsuario): int
    {
        return $this->repository->contarDenunciasAoPorUsuario($idUsuario);
    }
}
