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
        $idAnuncio = (int)($_POST['id_anuncio'] ?? 0);

        if ($idAnuncio <= 0) {
            $this->redirect(URL_BASE . '/anuncios');
        }

        try {
            $this->service->candidatar($idAnuncio, $usuario->getIdUsuario());
            $this->view('candidatura/sucesso', [
                'mensagem' => 'Candidatura realizada com sucesso!'
            ]);
        } catch (\Exception $e) {
            $this->view('candidatura/erro', [
                'erro' => $e->getMessage(),
                'idAnuncio' => $idAnuncio
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
        $idAnuncio = (int)($_GET['id'] ?? 0);

        if ($idAnuncio <= 0) {
            $this->redirect(URL_BASE . '/anuncios');
        }

        try {
            $candidatos = $this->service->listarCandidatos($idAnuncio, $usuario->getIdUsuario());
            $anuncioService = new \app\services\AnuncioService();
            $usuarioService = new \app\services\UsuarioService();
            $anuncio = $anuncioService->buscarPorId($idAnuncio);

            $candidaturasFormatadas = [];
            foreach ($candidatos as $candidatura) {
                $trabalhador = $usuarioService->buscarPorId($candidatura->getIdTrabalhador());

                $candidaturasFormatadas[] = [
                    'candidatura' => $candidatura,
                    'trabalhador' => $trabalhador,
                    'anuncio' => $anuncio,
                ];
            }

            $this->view('candidatura/candidatos_list', [
                'candidatos' => $candidaturasFormatadas,
                'candidaturas' => $candidaturasFormatadas,
                'anuncio' => $anuncio,
                'idAnuncio' => $idAnuncio,
            ]);
        } catch (\Exception $e) {
            $this->redirect(URL_BASE . '/anuncios');
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
            $anuncio = null;
            if ($candidatura) {
                $anuncioService = new \app\services\AnuncioService();
                $anuncio = $anuncioService->buscarPorId($candidatura->getIdAnuncio());
            }

            $this->view('candidatura/confirmada', [
                'candidatura' => $candidatura,
                'anuncio' => $anuncio,
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
        $anuncioService = new \app\services\AnuncioService();
        $usuarioService = new \app\services\UsuarioService();

        $itensHistorico = [];
        foreach ($candidaturas as $candidatura) {
            $anuncio = $anuncioService->buscarPorId($candidatura->getIdAnuncio());
            $contratante = $anuncio ? $usuarioService->buscarPorId($anuncio->getIdContratante()) : null;

            $itensHistorico[] = [
                'candidatura' => $candidatura,
                'anuncio' => $anuncio,
                'contratante' => $contratante,
            ];
        }

        $this->view('candidatura/historico', [
            'candidaturas' => $itensHistorico,
            'usuarioLogado' => $usuario
        ]);
    }
}
