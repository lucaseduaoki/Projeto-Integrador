<?php

namespace app\controllers;

use app\core\Controller;
use app\helpers\Validador;
use app\models\Candidatura;
use app\services\CandidaturaService;

class CandidaturaController extends Controller
{
    private CandidaturaService $service;

    public function __construct()
    {
        $this->service = new CandidaturaService();
    }

    /**
     * Candidatar a um anúncio
     */
    public function candidatar(): void
    {
        $this->trabalhadorRequired();

        $usuario = $this->usuarioLogado();
        $idVaga = (int)($_POST['id_vaga'] ?? 0);

        if ($idVaga <= 0) {
            $this->redirect(URL_BASE . '/vagas');
        }

        try {
            $this->service->candidatar($idVaga, $usuario->getIdUsuario());
            $this->view('candidatura/sucesso', [
                'mensagem' => 'Candidatura realizada com sucesso!'
            ]);
        } catch (\Exception $e) {
            $this->view('candidatura/erro', [
                'erro' => $e->getMessage(),
                'idVaga' => $idVaga
            ]);
        }
    }

    /**
     * Listar candidatos de um anúncio (para contratante)
     */
    public function listarCandidatos(): void
    {
        $this->contratanteRequired();

        $usuario = $this->usuarioLogado();
        $idVaga = (int)($_GET['id'] ?? 0);

        if ($idVaga <= 0) {
            $this->redirect(URL_BASE . '/vagas');
        }

        try {
            $candidatos = $this->service->listarCandidatos($idVaga, $usuario->getIdUsuario());
            $vagaService = new \app\services\VagaService();
            $usuarioService = new \app\services\UsuarioService();
            $vaga = $vagaService->buscarPorId($idVaga);

            $candidaturasFormatadas = [];
            foreach ($candidatos as $candidatura) {
                $trabalhador = $usuarioService->buscarPorId($candidatura->getIdTrabalhador());

                $candidaturasFormatadas[] = [
                    'candidatura' => $candidatura,
                    'trabalhador' => $trabalhador,
                    'vaga' => $vaga,
                ];
            }

            $this->view('candidatura/candidatos_list', [
                'candidatos' => $candidaturasFormatadas,
                'candidaturas' => $candidaturasFormatadas,
                'vaga' => $vaga,
                'idVaga' => $idVaga,
            ]);
        } catch (\Exception $e) {
            $this->redirect(URL_BASE . '/vagas');
        }
    }

    /**
     * Selecionar candidato
     */
    public function selecionar(): void
    {
        $this->contratanteRequired();

        $usuario = $this->usuarioLogado();
        $idCandidatura = (int)($_POST['id'] ?? 0);

        try {
            $this->service->selecionar($idCandidatura, $usuario->getIdUsuario());
            $this->json(['sucesso' => true, 'mensagem' => 'Candidato selecionado!']);
        } catch (\Exception $e) {
            $this->json(['sucesso' => false, 'erro' => $e->getMessage()], 400);
        }
    }

    /**
     * Rejeitar candidato
     */
    public function rejeitar(): void
    {
        $this->contratanteRequired();

        $usuario = $this->usuarioLogado();
        $idCandidatura = (int)($_POST['id'] ?? 0);

        try {
            $this->service->rejeitar($idCandidatura, $usuario->getIdUsuario());
            $this->json(['sucesso' => true, 'mensagem' => 'Candidato rejeitado!']);
        } catch (\Exception $e) {
            $this->json(['sucesso' => false, 'erro' => $e->getMessage()], 400);
        }
    }

    /**
     * Confirmar candidatura (trabalhador)
     */
    public function confirmar(): void
    {
        $this->trabalhadorRequired();

        $usuario = $this->usuarioLogado();
        $idCandidatura = (int)($_POST['id'] ?? 0);

        try {
            $this->service->confirmar($idCandidatura, $usuario->getIdUsuario());

            $candidatura = $this->service->buscarPorId($idCandidatura);
            $vaga = null;
            if ($candidatura) {
                $vagaService = new \app\services\VagaService();
                $vaga = $vagaService->buscarPorId($candidatura->getIdVaga());
            }

            $this->view('candidatura/confirmada', [
                'candidatura' => $candidatura,
                'vaga' => $vaga,
                'usuarioLogado' => $usuario,
            ]);
        } catch (\Exception $e) {
            $this->json(['sucesso' => false, 'erro' => $e->getMessage()], 400);
        }
    }

    /**
     * Histórico de candidaturas do trabalhador
     */
    public function historico(): void
    {
        $this->trabalhadorRequired();

        $usuario = $this->usuarioLogado();
        $candidaturas = $this->service->listarPorTrabalhador($usuario->getIdUsuario());
        $vagaService = new \app\services\VagaService();
        $usuarioService = new \app\services\UsuarioService();

        $itensHistorico = [];
        foreach ($candidaturas as $candidatura) {
            $vaga = $vagaService->buscarPorId($candidatura->getIdVaga());
            $contratante = $vaga ? $usuarioService->buscarPorId($vaga->getIdContratante()) : null;

            $itensHistorico[] = [
                'candidatura' => $candidatura,
                'vaga' => $vaga,
                'contratante' => $contratante,
            ];
        }

        $this->view('candidatura/historico', [
            'candidaturas' => $itensHistorico,
            'usuarioLogado' => $usuario
        ]);
    }
}
