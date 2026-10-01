<?php

namespace app\services;

use app\models\Usuario;
use app\models\Vaga;
use app\repositories\VagaRepository;
use app\repositories\UsuarioRepository;
use app\repositories\CandidaturaRepository;
use Exception;

class VagaService
{
    private VagaRepository $repository;
    private UsuarioRepository $usuarioRepository;
    private CandidaturaRepository $candidaturaRepository;

    public function __construct()
    {
        $this->repository = new VagaRepository();
        $this->usuarioRepository = new UsuarioRepository();
        $this->candidaturaRepository = new CandidaturaRepository();

    }

    /**
     * RN 04: Só contratante autenticado e ATIVO pode criar vaga
     */
    public function validarCriadorVaga(Usuario $usuario): void
    {
        if (!$usuario->isContratante()) {
            throw new Exception('Apenas contratantes podem criar vagas.');
        }
        if (!$usuario->isAtivo()) {
            throw new Exception('Sua conta está desativada. Entre em contato com o suporte.');
        }
    }

    /**
     * RN 05: Encerramento condicionado ao limite de aceitos atingido
     */
    public function validarEncerramentoVaga(Vaga $vaga): void
    {
        $aceitos = $vaga->getTotalAceitos();
        $limite = $vaga->getTrabalhadoresLimite();

        if ($aceitos < $limite) {
            throw new Exception(
                "A vaga não pode ser encerrada. Você precisa de $limite trabalhador(es) aceito(s). " .
                "Atualmente tem $aceitos aceito(s)."
            );
        }
    }

    /**
     * RN 10: Validar edição de limite (não pode reduzir abaixo de aceitos)
     */
    public function validarNovoLimite(int $novoLimite, int $aceitos): void
    {
        if ($novoLimite < $aceitos) {
            throw new Exception(
                "Não é possível reduzir o limite para $novoLimite. Você tem $aceitos " .
                "trabalhador(es) já aceito(s). O limite deve ser no mínimo $aceitos."
            );
        }
    }

    /**
     * RN 17: Edição de vaga com restrições (título e categoria travados se houver candidaturas)
     */
    public function validarEdicaoVaga(Vaga $vagaAntiga, string $novoTitulo, int $novaCategoria): void
    {
        $temCandidaturas = $this->candidaturaRepository->listarPorVaga($vagaAntiga->getIdVaga()) !== [];

        if ($temCandidaturas) {
            if ($novoTitulo !== $vagaAntiga->getTitulo()) {
                throw new Exception(
                    'Você não pode alterar o título da vaga depois que trabalhadores se candidataram.'
                );
            }
            if ($novaCategoria !== $vagaAntiga->getIdCategoria()) {
                throw new Exception(
                    'Você não pode alterar a categoria da vaga depois que trabalhadores se candidataram.'
                );
            }
        }
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
        ?string $duracao = null,
        ?string $observacoes = null,
        ?string $dataServico = null,
        ?string $bairro = null
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
            duracao: $duracao,
            observacoes: $observacoes,
            dataServico: $dataServico,
            bairro: $bairro
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
     * Buscar várias vagas de uma vez, indexado por id_vaga.
     */
    public function buscarPorIds(array $ids): array
    {
        return $this->repository->buscarPorIds($ids);
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

        if ($this->repository->possuiCandidaturas($vaga->getIdVaga())) {
            if ($vaga->getTitulo() !== $vagaExistente->getTitulo()) {
                throw new Exception("Esta vaga já tem candidaturas: a função (título) não pode ser alterada.");
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