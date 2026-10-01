<?php

namespace app\services;

use app\models\Usuario;
use app\models\Vaga;
use app\repositories\CandidaturaRepository;
use Exception;

/**
 * Validador centralizado de regras de negócio (RN)
 * Cada método corresponde a uma ou mais RNs
 */
class ValidadorRegrasNegocio
{
    private CandidaturaRepository $candidaturaRepository;
    
    public function __construct()
    {
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
        $candidaturaExistente = $this->candidaturaRepository->buscarPorVagaETrabalhador(
            $vaga->getIdVaga(),
            $trabalhador->getIdUsuario()
        );
        
        if ($candidaturaExistente !== null) {
            throw new Exception('Você já se candidatou a esta vaga.');
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
}
