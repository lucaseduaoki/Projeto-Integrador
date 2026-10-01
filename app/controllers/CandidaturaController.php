<?php

namespace app\controllers;

use app\core\Controller;
use app\services\CandidaturaService;
use app\services\UsuarioService;
use app\services\VagaService;
use app\services\ValidadorRegrasNegocio;

class CandidaturaController extends Controller
{
    private CandidaturaService $service;
    private VagaService $vagaService;
    private UsuarioService $usuarioService;
    private ValidadorRegrasNegocio $validador;

    public function __construct()
    {
        $this->usuarioService = new UsuarioService();
        $this->service = new CandidaturaService();
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
            $this->flashSucesso('Candidatura enviada com sucesso!');
        } catch (\Exception $e) {
            $this->flashErro($this->mensagemAmigavel($e, 'Não foi possível enviar sua candidatura agora. Tente novamente em instantes.'));
        }

        $this->redirect(URL_BASE . '/vagas/visualizar?id=' . $idVaga);
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

        $candidatos = $this->service->listarInteressados($idVaga);

        $this->view('interesse/candidatos_list', [
            'vaga' => $vaga,
            'interessados' => $candidatos,
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
        $candidatura = $this->service->buscarPorId($idInteresse);
        if (!$candidatura) {
            throw new \Exception('Candidatura não encontrada.');
        }

        $vaga = $this->vagaService->buscarPorId($candidatura->getIdVaga());
        if (!$vaga) {
            throw new \Exception('Vaga não encontrada.');
        }

        // RN 10: Validar aceitação (não exceder limite)
        $this->validador->validarAceitacaoCandidato($vaga, $candidatura->getIdTrabalhador());

        $this->service->aceitarInteressado(
            $idInteresse,
            $usuario->getIdUsuario()
        );

        $this->flashSucesso('Candidato aceito com sucesso!');

    } catch (\Exception $e) {
        error_log("Erro ao aceitar: " . $e->getMessage());
        $this->flashErro($this->mensagemAmigavel($e, 'Não foi possível aceitar o candidato agora. Tente novamente em instantes.'));
    }

    $this->redirect(URL_BASE . '/vagas/minhas');
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

        $candidatura = $this->service->buscarPorId((int)($_GET['id'] ?? 0));
        $vaga = $candidatura ? $this->vagaService->buscarPorId($candidatura->getIdVaga()) : null;

        if (!$candidatura || !$vaga) {
            $this->redirect(URL_BASE . '/vagas/minhas');
            return;
        }

        // RN 14: Só o contratante daquela vaga vê o perfil do candidato
        if (!$this->podeGerenciarVaga($vaga)) {
            $this->redirect(URL_BASE . '/403');
            return;
        }

        $trabalhador = $this->usuarioService->buscarPorId($candidatura->getIdTrabalhador());

        $this->view('usuario/perfil_candidato', [
            'trabalhador' => $trabalhador,
            'habilidades' => $this->usuarioService->buscarHabilidades($candidatura->getIdTrabalhador()),
            'interesse' => $candidatura,
            'vaga' => $vaga,
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

        // Busca vagas e contratantes em lote (evita N+1: antes eram até 2 queries por candidatura)
        $vagas = $this->vagaService->buscarPorIds(array_map(
            fn($candidatura) => $candidatura->getIdVaga(),
            $interesses
        ));
        $contratantes = $this->usuarioService->buscarPorIds(array_map(
            fn($vaga) => $vaga->getIdContratante(),
            $vagas
        ));

        $candidaturas = array_map(function ($candidatura) use ($vagas, $contratantes) {
            $vaga = $vagas[$candidatura->getIdVaga()] ?? null;
            return [
                'candidatura' => $candidatura,
                'vaga' => $vaga,
                'contratante' => $vaga ? ($contratantes[$vaga->getIdContratante()] ?? null) : null
            ];
        }, $interesses);

        $this->view('interesse/historico', [
            'interesses' => $candidaturas,
            'usuarioLogado' => $usuario
        ]);
    }
}