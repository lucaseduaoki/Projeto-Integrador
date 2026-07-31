<?php

namespace app\controllers;

use app\core\Controller;
use app\services\InteresseService;
use app\services\VagaService;

class InteresseController extends Controller
{
    private InteresseService $service;
    private VagaService $vagaService;

    public function __construct()
    {
        $this->service = new InteresseService();
        $this->vagaService = new VagaService();
    }


    public function demonstrar(): void
    {
            error_log("ENTROU NO CONTROLLER demonstrar");
        $this->trabalhadorRequired();

        $usuario = $this->usuarioLogado();

        $idVaga = (int)($_POST['id_vaga'] ?? 0);

        if ($idVaga <= 0) {
            $this->redirect(URL_BASE . '/vagas');
        }


        try {

            $this->service->demonstrarInteresse(
                $idVaga,
                $usuario->getIdUsuario()
            );

            error_log("fazendo busca no banco para saber se o interesse deu certo");
            


            $this->redirect(
                URL_BASE . '/vagas/visualizar?id=' . $idVaga
            );


        } catch (\Exception $e) {

            $this->redirect(
                URL_BASE . '/vagas/visualizar?id=' . $idVaga
            );
        }
    }

     public function listarInteressados(): void
    {
        error_log("ENTROU NO CONTROLLER listarInteressados");
        $this->contratanteRequired();

        $usuario = $this->usuarioLogado();

        $idVaga = (int)($_GET['id'] ?? 0);

        if ($idVaga <= 0) {
            $this->redirect(URL_BASE . '/vagas');
            return;
        }
        error_log("Buscando vaga com ID: " . $idVaga);

        $vaga = $this->vagaService->buscarPorId($idVaga);


        if (!$vaga) {
            $this->redirect(URL_BASE . '/vagas');
            return;
        }


        // Garante que somente o dono da vaga veja os interessados
        if ($vaga->getIdContratante() !== $usuario->getIdUsuario()) {
            $this->redirect(URL_BASE . '/403');
            return;
        }


        $interessados = $this->service->listarInteressados($idVaga);
        error_log("Interessados encontrados: " . print_r($interessados, true));

        $this->view('interesse/candidatos_list', [
            'vaga' => $vaga,
            'interessados' => $interessados,
            'usuario' => $usuario
        ]);
    }

    public function visualizarHistorico(): void
    {
        $this->trabalhadorRequired();

        $usuario = $this->usuarioLogado();

        $idInteresse = (int)($_GET['id'] ?? 0);

        if ($idInteresse <= 0) {
            $this->redirect(URL_BASE . '/interesse/historico');
            return;
        }

        $interesse = $this->service->buscarPorId($idInteresse);

        if (!$interesse) {
            $this->redirect(URL_BASE . '/interesse/historico');
            return;
        }

        // Garante que o trabalhador só visualize suas próprias candidaturas
        if ($interesse->getIdTrabalhador() !== $usuario->getIdUsuario()) {
            $this->redirect(URL_BASE . '/403');
            return;
        }

        $this->view('interesse/visualizar_historico', [
            'interesse' => $interesse,
            'usuario' => $usuario
        ]);
    }

    public function historico(): void
    {
        $this->trabalhadorRequired();

        $usuario = $this->usuarioLogado();

        $interesses = $this->service->listarHistorico($usuario->getIdUsuario());

        $this->view('interesse/historico', [
            'interesses' => $interesses,
            'usuario' => $usuario
        ]);
    }
}