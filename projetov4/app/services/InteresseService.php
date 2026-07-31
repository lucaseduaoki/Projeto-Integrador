<?php

namespace app\services;

use app\models\Interesse;
use app\repositories\InteresseRepository;
use app\repositories\VagaRepository;
use Exception;

class InteresseService
{
    private InteresseRepository $repository;
    private VagaRepository $vagaRepository;

    public function __construct()
    {
        $this->repository = new InteresseRepository();
        $this->vagaRepository = new VagaRepository();
    }

    /**
     * Trabalhador demonstra interesse em uma vaga.
     */
    public function demonstrarInteresse(
        int $idVaga,
        int $idTrabalhador
    ): int {
        error_log("ENTROU NO SERVICE demonstrarInteresse com idVaga=$idVaga e idTrabalhador=$idTrabalhador"); // Log the input values
        $vaga = $this->vagaRepository->buscarPorId($idVaga);
        if (!$vaga) {
            throw new Exception("Vaga não encontrada.");
        }
        error_log("Vaga encontrada: " . print_r($vaga, true)); // Log the vaga object
        if ($vaga->getStatus() !== 'ATIVA') {
            throw new Exception("Esta vaga já foi encerrada.");
        }
        error_log("Data limite da vaga: " . $vaga->getDataLimite()); // Log the data limite of the vaga
        if (
            $vaga->getDataLimite() !== null &&
            strtotime($vaga->getDataLimite()) < strtotime(date('Y-m-d'))
        ) {
            throw new Exception("O prazo para candidatura terminou.");
        }

        if ($vaga->getIdContratante() == $idTrabalhador) {
            throw new Exception("Você não pode demonstrar interesse na própria vaga.");
        }

        $jaExiste = $this->repository->buscarPorVagaETrabalhador(
            $idVaga,
            $idTrabalhador
        );
        error_log("Verificando se já existe interesse: " . ($jaExiste ? 'Sim' : 'Não')); // Log whether the interest already exists
        if ($jaExiste) {
            throw new Exception("Você já demonstrou interesse nesta vaga.");
        }

        $interesse = new Interesse(
            $idVaga,
            $idTrabalhador,
            'PENDENTE',
            date('Y-m-d H:i:s')


        );
        return $this->repository->criar($interesse);
    }

    /**
     * Lista interessados de uma vaga.
     */
    public function listarInteressados(int $idVaga): array
    {
        return $this->repository->listarPorVaga($idVaga);
    }

    /**
     * Aceita um interessado.
     */
    public function aceitarInteressado(int $idInteresse): bool
    {
        $interesse = $this->repository->buscarPorId($idInteresse);

        if (!$interesse) {
            throw new Exception("Interesse não encontrado.");
        }

        $vaga = $this->vagaRepository->buscarPorId(
            $interesse->getIdVaga()
        );

        if (!$vaga) {
            throw new Exception("Vaga não encontrada.");
        }

        if ($vaga->getStatus() !== 'ATIVA') {
            throw new Exception("A vaga já está encerrada.");
        }

        $this->repository->aceitar($idInteresse);

        $totalAceitos = $this->repository->contarAceitos(
            $vaga->getIdVaga()
        );

        if ($totalAceitos >= $vaga->getTrabalhadoresLimite()) {

            $this->vagaRepository->mudarStatus(
                $vaga->getIdVaga(),
                'ENCERRADA'
            );
        }

        return true;
    }

    /**
     * Lista somente os aceitos.
     */
    public function listarAceitos(int $idVaga): array
    {
        return $this->repository->listarAceitos($idVaga);
    }
    
    /**
     * Lista histórico de candidaturas de um trabalhador.
     */
    public function listarHistorico(int $idTrabalhador): array
    {
        return $this->repository->listarPorTrabalhador($idTrabalhador);
    }

    /**
     * Verifica se um trabalhador já demonstrou interesse.
     */
    public function jaDemonstrouInteresse(
        int $idVaga,
        int $idTrabalhador
    ): bool {

        return $this->repository
            ->buscarPorVagaETrabalhador(
                $idVaga,
                $idTrabalhador
                
            ) !== null;
    }

    /**
     * Quantidade de trabalhadores aceitos.
     */
    public function quantidadeAceitos(int $idVaga): int
    {
        return $this->repository->contarAceitos($idVaga);
    }
}