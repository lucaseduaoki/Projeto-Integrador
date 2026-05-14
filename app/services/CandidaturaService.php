<?php

namespace app\services;

use app\models\Candidatura;
use app\repositories\AnuncioRepository;
use app\repositories\CandidaturaRepository;
use Exception;

class CandidaturaService
{
    private CandidaturaRepository $repository;
    private AnuncioRepository $anuncioRepository;

    public function __construct()
    {
        $this->repository = new CandidaturaRepository();
        $this->anuncioRepository = new AnuncioRepository();
    }

    /**
     * Candidatar trabalhador a vaga
     * RN21: impede candidatura duplicada
     * RN08: trabalhador não pode se candidatar à própria vaga
     */
    public function candidatar(int $idAnuncio, int $idTrabalhador): int
    {
        // Verificar se anúncio existe
        $anuncio = $this->anuncioRepository->buscarPorId($idAnuncio);
        if (!$anuncio) {
            throw new Exception('Anúncio não encontrado.');
        }

        // RN08: Trabalhador não pode se candidatar à própria vaga
        if ($anuncio->getIdContratante() === $idTrabalhador) {
            throw new Exception('Você não pode se candidatar à sua própria vaga.');
        }

        // RN21: Candidatura duplicada é proibida
        $existente = $this->repository->buscarPorAnuncioDeTrabalhador($idAnuncio, $idTrabalhador);
        if ($existente) {
            throw new Exception('Você já se candidatou a esta vaga.');
        }

        // Criar candidatura
        $candidatura = new Candidatura(
            0,
            $idAnuncio,
            $idTrabalhador,
            'PENDENTE'
        );

        return $this->repository->criar($candidatura);
    }

    /**
     * Buscar candidatura por ID
     */
    public function buscarPorId(int $id): ?Candidatura
    {
        return $this->repository->buscarPorId($id);
    }

    /**
     * Verificar se um trabalhador já se candidatou a um anúncio
     */
    public function verificarCandidatura(int $idTrabalhador, int $idAnuncio): bool
    {
        return $this->repository->buscarPorAnuncioDeTrabalhador($idAnuncio, $idTrabalhador) !== null;
    }

    /**
     * Listar candidatos de um anúncio
     * RN10: Contratante visualiza apenas candidatos das próprias vagas
     */
    public function listarCandidatos(int $idAnuncio, int $idContratanteLogado): array
    {
        $anuncio = $this->anuncioRepository->buscarPorId($idAnuncio);
        if (!$anuncio) {
            throw new Exception('Anúncio não encontrado.');
        }

        // RN10: Verificar propriedade
        if ($anuncio->getIdContratante() !== $idContratanteLogado) {
            throw new Exception('Acesso negado. Você não é o contratante desta vaga.');
        }

        return $this->repository->listarPorAnuncio($idAnuncio);
    }

    /**
     * Listar candidaturas de um trabalhador
     */
    public function listarPorTrabalhador(int $idTrabalhador): array
    {
        return $this->repository->listarPorTrabalhador($idTrabalhador);
    }

    /**
     * Selecionar candidato
     * RN11: Uma vaga só pode ter um candidato ACEITO por vez
     */
    public function selecionar(int $idCandidatura, int $idContratanteLogado): bool
    {
        $candidatura = $this->repository->buscarPorId($idCandidatura);
        if (!$candidatura) {
            throw new Exception('Candidatura não encontrada.');
        }

        $anuncio = $this->anuncioRepository->buscarPorId($candidatura->getIdAnuncio());
        
        // Verificar propriedade
        if ($anuncio->getIdContratante() !== $idContratanteLogado) {
            throw new Exception('Acesso negado.');
        }

        // RN11: Verificar se já existe outro aceito
        $aceitos = $this->repository->buscarAceitosPorAnuncio($candidatura->getIdAnuncio());
        if (!empty($aceitos)) {
            throw new Exception('Já existe um candidato aceito nesta vaga.');
        }

        // Atualizar status
        $candidatura->setStatus('ACEITO');
        $candidatura->setDataSelecao(date('Y-m-d H:i:s'));
        
        return $this->repository->atualizar($candidatura);
    }

    /**
     * Rejeitar candidato
     */
    public function rejeitar(int $idCandidatura, int $idContratanteLogado): bool
    {
        $candidatura = $this->repository->buscarPorId($idCandidatura);
        if (!$candidatura) {
            throw new Exception('Candidatura não encontrada.');
        }

        $anuncio = $this->anuncioRepository->buscarPorId($candidatura->getIdAnuncio());
        
        // Verificar propriedade
        if ($anuncio->getIdContratante() !== $idContratanteLogado) {
            throw new Exception('Acesso negado.');
        }

        $candidatura->setStatus('RECUSADO');
        return $this->repository->atualizar($candidatura);
    }

    /**
     * Confirmar candidatura (trabalhador aceita proposta)
     */
    public function confirmar(int $idCandidatura, int $idTrabalhadorLogado): bool
    {
        $candidatura = $this->repository->buscarPorId($idCandidatura);
        if (!$candidatura) {
            throw new Exception('Candidatura não encontrada.');
        }

        // Verificar se é o trabalhador correto
        if ($candidatura->getIdTrabalhador() !== $idTrabalhadorLogado) {
            throw new Exception('Acesso negado.');
        }

        // Trabalhadoreador deve estar em status ACEITO
        if ($candidatura->getStatus() !== 'ACEITO') {
            throw new Exception('Esta candidatura não está em status para confirmar.');
        }

        // Encerrar anúncio (RN06)
        $this->anuncioRepository->mudarStatus($candidatura->getIdAnuncio(), 'ENCERRADO');

        return true;
    }
}
