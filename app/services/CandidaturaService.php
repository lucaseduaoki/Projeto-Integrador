<?php

namespace app\services;

use app\models\Candidatura;
use app\models\Usuario;
use app\models\Vaga;
use app\repositories\CandidaturaRepository;
use app\repositories\VagaRepository;
use Exception;

class CandidaturaService
{
    private CandidaturaRepository $repository;
    private VagaRepository $vagaRepository;

    public function __construct()
    {
        $this->repository = new CandidaturaRepository();
        $this->vagaRepository = new VagaRepository();
    }

    /**
     * RN 10: Validar aceitação (não pode exceder limite)
     */
    public function validarAceitacaoCandidato(Vaga $vaga, int $idCandidato): void
    {
        $aceitos = $vaga->getTotalAceitos();
        $limite = $vaga->getTrabalhadoresLimite();

        if ($aceitos >= $limite) {
            throw new Exception('Esta vaga já atingiu o limite de trabalhadores aceitos.');
        }
    }

    /**
     * RN 07-15: Validar candidatura (trabalhador, vaga ativa, dentro prazo, única por vaga)
     */
    public function validarCandidatura(Usuario $trabalhador, Vaga $vaga): void
    {
        // RN 07: Só trabalhador se candidata
        if (!$trabalhador->isTrabalhador()) {
            throw new Exception('Apenas trabalhadores podem se candidatar a vagas.');
        }

        // RN 06-07: Vaga deve estar ativa
        if ($vaga->getStatus() !== 'ATIVA') {
            throw new Exception('Esta vaga não está mais ativa.');
        }

        // RN 06-07: Vaga deve estar visível
        if (!$vaga->estaVisivel()) {
            throw new Exception('Esta vaga não está disponível.');
        }

        // RN 06: Vaga deve estar dentro do prazo (data_limite)
        if ($vaga->getDataLimite() !== null && $vaga->getDataLimite() < date('Y-m-d')) {
            throw new Exception('O prazo para se candidatar a esta vaga já expirou.');
        }

        // RN 06: Deve ter vagas disponíveis (aceitos < limite)
        if ($vaga->getTotalAceitos() >= $vaga->getTrabalhadoresLimite()) {
            throw new Exception('Esta vaga já atingiu o limite de candidatos aceitos.');
        }

        // RN 15: Uma candidatura por vaga
        $candidaturaExistente = $this->repository->buscarPorVagaETrabalhador(
            $vaga->getIdVaga(),
            $trabalhador->getIdUsuario()
        );

        if ($candidaturaExistente !== null) {
            throw new Exception('Você já se candidatou a esta vaga.');
        }
    }

    /**
     * Trabalhador demonstra interesse em uma vaga.
     */
    public function demonstrarInteresse(
        int $idVaga,
        int $idTrabalhador
    ): int {
        error_log("ENTROU NO SERVICE demonstrarInteresse com idVaga=$idVaga e idTrabalhador=$idTrabalhador");
        $vaga = $this->vagaRepository->buscarPorId($idVaga);
        if (!$vaga) {
            throw new Exception("Vaga não encontrada.");
        }
        error_log("Vaga encontrada: " . print_r($vaga, true));
        if (!$vaga->isUserActive() || !$vaga->estaVisivel()) {
            throw new Exception("Esta vaga não está mais disponível.");
        }
        if ($vaga->getStatus() !== 'ATIVA') {
            throw new Exception("Esta vaga já foi encerrada.");
        }
        error_log("Data limite da vaga: " . $vaga->getDataLimite());
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
        error_log("Verificando se já existe interesse: " . ($jaExiste ? 'Sim' : 'Não'));
        if ($jaExiste) {
            throw new Exception("Você já demonstrou interesse nesta vaga.");
        }

        $candidatura = new Candidatura(
            0,
            $idVaga,
            $idTrabalhador,
            'PENDENTE',
            date('Y-m-d H:i:s')
        );
        return $this->repository->criar($candidatura);
    }

    public function buscarPorId(int $idInteresse): ?Candidatura
    {
        return $this->repository->buscarPorId($idInteresse);
    }

    /**
     * Lista interessados de uma vaga.
     */
    public function listarInteressados(int $idVaga): array
    {
        return $this->repository->listarPorVaga($idVaga);
    }

public function aceitarInteressado(
    int $idInteressado,
    int $idContratante
): bool {

    error_log("[ACEITAR] Iniciando aceite. Interesse={$idInteressado} Contratante={$idContratante}");

    $interesse = $this->repository->buscarPorId($idInteressado);

    error_log("[ACEITAR] Interesse encontrado: " . ($interesse ? "SIM" : "NÃO"));

    if (!$interesse) {
        throw new Exception("Interesse não encontrado.");
    }

    $vaga = $this->vagaRepository->buscarPorId(
        $interesse->getIdVaga()
    );

    error_log("[ACEITAR] Vaga encontrada: " . ($vaga ? "SIM" : "NÃO"));

    if (!$vaga) {
        throw new Exception("Vaga não encontrada.");
    }

    error_log("[ACEITAR] Dono da vaga: {$vaga->getIdContratante()}");
    error_log("[ACEITAR] Usuário logado: {$idContratante}");

    if ($vaga->getIdContratante() !== $idContratante) {
        throw new Exception("Sem permissão.");
    }

    error_log("[ACEITAR] Status da vaga: {$vaga->getStatus()}");

    if ($vaga->getStatus() !== 'ATIVA') {
        throw new Exception("A vaga está encerrada.");
    }

    if (!$vaga->estaVisivel()) {
        throw new Exception("O anúncio está oculto ou foi removido pela moderação.");
    }

    // Impede aceitar o mesmo trabalhador duas vezes
    if ($interesse->getStatus() === 'ACEITO') {
        throw new Exception("Este trabalhador já foi aceito.");
    }

    // Verifica se ainda há vagas disponíveis
    $totalAceitos = $this->repository->contarAceitos(
        $vaga->getIdVaga()
    );

    error_log("[ACEITAR] Aceitos atualmente: {$totalAceitos}");
    error_log("[ACEITAR] Limite da vaga: {$vaga->getTrabalhadoresLimite()}");

    if ($totalAceitos >= $vaga->getTrabalhadoresLimite()) {
        throw new Exception(
            "Esta vaga já atingiu o número máximo de trabalhadores."
        );
    }

    error_log("[ACEITAR] Aceitando interesse...");

    $this->repository->aceitar($idInteressado);

    error_log("[ACEITAR] Interesse aceito.");

    return true;
}

    public function listarContatosAceitos(
    int $idVaga,
    int $idContratante
): array {

    $vaga = $this->vagaRepository->buscarPorId($idVaga);

    if (!$vaga) {
        throw new Exception("Vaga não encontrada.");
    }

    if ($vaga->getIdContratante() !== $idContratante) {
        throw new Exception("Sem permissão.");
    }

    return $this->repository->listarContatosAceitos($idVaga);
}


    /**
     * Lista somente os aceitos.
     */
    public function listarAceitos(int $idVaga, int $idContratante): array
    {
        $vaga = $this->vagaRepository->buscarPorId($idVaga);

        if (!$vaga) {
            throw new Exception("Vaga não encontrada.");
        }

        if ($vaga->getIdContratante() !== $idContratante) {
            throw new Exception("Sem permissão.");
        }

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