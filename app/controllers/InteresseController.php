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

public function aceitar(): void
{
    error_log("[CONTROLLER] Entrou no método aceitar.");

    $idInteresse = (int)($_POST['id'] ?? 0);

    error_log("[CONTROLLER] ID Interesse: {$idInteresse}");

    $usuario = $this->usuarioLogado();

    error_log("[CONTROLLER] Usuário: {$usuario->getIdUsuario()}");

    try {

        $this->service->aceitarInteressado(
            $idInteresse,
            $usuario->getIdUsuario()
        );

        error_log("[CONTROLLER] Aceite realizado.");

    } catch (\Exception $e) {

        error_log("[ERRO] " . $e->getMessage());
    }

    $this->redirect(URL_BASE . '/vagas');
}

public function listarAceitos(): void
{
    error_log("[ACEITOS] Entrou no controller");

    $this->contratanteRequired();

    $usuario = $this->usuarioLogado();

    error_log("[ACEITOS] Usuário: " . $usuario->getIdUsuario());

    $idVaga = (int)($_GET['id'] ?? 0);

    error_log("[ACEITOS] ID Vaga: " . $idVaga);

    if ($idVaga <= 0) {
        error_log("[ACEITOS] ID inválido");

        http_response_code(400);

        echo json_encode([
            'erro' => 'ID da vaga inválido.'
        ]);

        return;
    }

    try {

        error_log("[ACEITOS] Chamando service...");

        $aceitos = $this->service->listarContatosAceitos(
            $idVaga,
            $usuario->getIdUsuario()
        );

        error_log("[ACEITOS] Retorno do service:");
        error_log(print_r($aceitos, true));

        header('Content-Type: application/json');

        echo json_encode($aceitos);

    } catch (\Exception $e) {

        error_log("[ACEITOS] ERRO: " . $e->getMessage());

        http_response_code(403);

        echo json_encode([
            'erro' => $e->getMessage()
        ]);
    }
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

        $interesses = $this->service->listarHistorico(
            $usuario->getIdUsuario()
        );

        $candidaturas = array_map(function ($interesse) {
            error_log("Montando histórico para interesse ID: " . $interesse->getIdInteresse());
            error_log("Interesse: " . print_r($interesse->toArray(), true));

            return [
                'candidatura' => $interesse,
                'vaga' => $this->vagaService->buscarPorId(
                    $interesse->getIdVaga()
                ),
                'contratante' => $this->vagaService->buscarContratantePorVaga(
                    $interesse->getIdVaga()
                )
            ];

        }, $interesses);

        error_log("Histórico montado:");
        error_log(print_r($candidaturas, true));

        $this->view('interesse/historico', [
            'interesses' => $candidaturas,
            'usuarioLogado' => $usuario
        ]);
    }
}