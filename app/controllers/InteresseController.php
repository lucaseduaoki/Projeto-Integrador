<?php

namespace app\controllers;

use app\core\Controller;
use app\services\InteresseService;
use app\services\UsuarioService;
use app\services\VagaService;
use app\services\ValidadorRegrasNegocio;

class InteresseController extends Controller
{
    private InteresseService $service;
    private VagaService $vagaService;
    private UsuarioService $usuarioService;
    private ValidadorRegrasNegocio $validador;

    public function __construct()
    {
        $this->usuarioService = new UsuarioService();
        $this->service = new InteresseService();
        $this->vagaService = new VagaService();
        $this->validador = new ValidadorRegrasNegocio();
    }

    /**
     * Candidatura (RN 07, RN 15)
     */
    public function demonstrar(): void
    {
        $this->trabalhadorRequired();

        $usuario = $this->usuarioLogado();
        $idVaga = (int)($_POST['id_vaga'] ?? 0);

        if ($idVaga <= 0) {
            $this->redirect(URL_BASE . '/vagas');
            return;
        }

        $vaga = $this->vagaService->buscarPorId($idVaga);
        if (!$vaga) {
            $this->redirect(URL_BASE . '/vagas');
            return;
        }

        // RN 07, RN 15: Validar candidatura
        try {
            $this->validador->validarCandidatura($usuario, $vaga);
            $this->service->demonstrarInteresse(
                $idVaga,
                $usuario->getIdUsuario()
            );
            $this->redirect(URL_BASE . '/vagas/visualizar?id=' . $idVaga);
        } catch (\Exception $e) {
            $this->redirect(URL_BASE . '/vagas/visualizar?id=' . $idVaga);
        }
    }

    /**
     * Listar candidatos de uma vaga (RN 08)
     */
    public function listarInteressados(): void
    {
        $this->contratanteRequired();

        $usuario = $this->usuarioLogado();
        $idVaga = (int)($_GET['id'] ?? 0);

        if ($idVaga <= 0) {
            $this->redirect(URL_BASE . '/vagas');
            return;
        }

        $vaga = $this->vagaService->buscarPorId($idVaga);

        if (!$vaga) {
            $this->redirect(URL_BASE . '/vagas');
            return;
        }

        // RN 14: Só o dono da vaga vê os candidatos
        if (!$this->podeGerenciarVaga($vaga)) {
            $this->redirect(URL_BASE . '/403');
            return;
        }

        $interessados = $this->service->listarInteressados($idVaga);

        $this->view('interesse/candidatos_list', [
            'vaga' => $vaga,
            'interessados' => $interessados,
            'usuario' => $usuario
        ]);
    }

/**
 * Aceitar candidato (RN 10)
 */
public function aceitar(): void
{
    $this->contratanteRequired();

    $idInteresse = (int)($_POST['id'] ?? 0);
    $usuario = $this->usuarioLogado();

    try {
        $interesse = $this->service->buscarPorId($idInteresse);
        if (!$interesse) {
            throw new \Exception('Candidatura não encontrada.');
        }

        $vaga = $this->vagaService->buscarPorId($interesse->getIdVaga());
        if (!$vaga) {
            throw new \Exception('Vaga não encontrada.');
        }

        // RN 10: Validar aceitação (não exceder limite)
        $this->validador->validarAceitacaoCandidato($vaga, $interesse->getIdTrabalhador());

        $this->service->aceitarInteressado(
            $idInteresse,
            $usuario->getIdUsuario()
        );

    } catch (\Exception $e) {
        error_log("Erro ao aceitar: " . $e->getMessage());
    }

    $this->redirect(URL_BASE . '/vagas');
}

/**
 * Contatos dos candidatos aceitos (RN 09, RN 11, RN 16)
 */
public function listarAceitos(): void
{
    $this->contratanteRequired();

    $usuario = $this->usuarioLogado();
    $idVaga = (int)($_GET['id'] ?? 0);

    if ($idVaga <= 0) {
        http_response_code(400);
        echo json_encode(['erro' => 'ID da vaga inválido.']);
        return;
    }

    try {
        $aceitos = $this->service->listarContatosAceitos(
            $idVaga,
            $usuario->getIdUsuario()
        );

        header('Content-Type: application/json');
        echo json_encode($aceitos);

    } catch (\Exception $e) {
        http_response_code(403);
        echo json_encode([
            'erro' => $this->mensagemAmigavel($e, 'Não foi possível carregar os contatos.')
        ]);
    }
}
    /**
     * Perfil de um candidato (RN 08, RN 14)
     */
    public function visualizarCandidato(): void
    {
        $this->contratanteRequired();

        $interesse = $this->service->buscarPorId((int)($_GET['id'] ?? 0));
        $vaga = $interesse ? $this->vagaService->buscarPorId($interesse->getIdVaga()) : null;

        if (!$interesse || !$vaga) {
            $this->redirect(URL_BASE . '/vagas/minhas');
            return;
        }

        // RN 14: Só o contratante daquela vaga vê o perfil do candidato
        if (!$this->podeGerenciarVaga($vaga)) {
            $this->redirect(URL_BASE . '/403');
            return;
        }

        $trabalhador = $this->usuarioService->buscarPorId($interesse->getIdTrabalhador());

        $this->view('usuario/perfil_candidato', [
            'trabalhador' => $trabalhador,
            'habilidades' => $this->usuarioService->buscarHabilidades($interesse->getIdTrabalhador()),
            'interesse' => $interesse,
            'vaga' => $vaga,
        ]);
    }

    /**
     * Visualizar histórico de candidatura (trabalhador)
     */
    public function visualizarHistorico(): void
    {
        $this->trabalhadorRequired();

        $usuario = $this->usuarioLogado();
        $idInteresse = (int)($_GET['id'] ?? 0);

        if ($idInteresse <= 0) {
            $this->redirect(URL_BASE . '/candidatura/historico');
            return;
        }

        $interesse = $this->service->buscarPorId($idInteresse);

        if (!$interesse) {
            $this->redirect(URL_BASE . '/candidatura/historico');
            return;
        }

        // RN 14: Trabalhador só vê suas próprias candidaturas
        if ($interesse->getIdTrabalhador() !== $usuario->getIdUsuario()) {
            $this->redirect(URL_BASE . '/403');
            return;
        }

        $this->view('interesse/visualizar_historico', [
            'interesse' => $interesse,
            'usuario' => $usuario
        ]);
    }

    /**
     * Histórico de candidaturas (trabalhador)
     */
    public function historico(): void
    {
        $this->trabalhadorRequired();

        $usuario = $this->usuarioLogado();
        $interesses = $this->service->listarHistorico($usuario->getIdUsuario());

        $candidaturas = array_map(function ($interesse) {
            return [
                'candidatura' => $interesse,
                'vaga' => $this->vagaService->buscarPorId($interesse->getIdVaga()),
                'contratante' => $this->vagaService->buscarContratantePorVaga($interesse->getIdVaga())
            ];
        }, $interesses);

        $this->view('interesse/historico', [
            'interesses' => $candidaturas,
            'usuarioLogado' => $usuario
        ]);
    }
}