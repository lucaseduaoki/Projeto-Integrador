<?php

namespace app\controllers;

use app\core\Controller;
use app\services\VagaService;
use app\services\UsuarioService;
use app\services\InteresseService;
use app\helpers\Validador;

class VagaController extends Controller
{
    private VagaService $vagaService;
    private UsuarioService $usuarioService;
    private InteresseService $interesseService;

    public function __construct()
    {
        $this->vagaService = new VagaService();
        $this->usuarioService = new UsuarioService();
        $this->interesseService = new InteresseService();
    }

    /**
     * Lista todas as vagas ativas
     */
    public function listar(): void
    {
        $this->autenticacaoRequired();

        $limit = (int)($_GET['limit'] ?? 50);
        $offset = (int)($_GET['offset'] ?? 0);

        $vagas = $this->vagaService->listar($limit, $offset);

        $this->view('vaga/vaga_list', [
            'vagas' => $vagas,
            'usuario' => $this->usuarioLogado()
        ]);
    }

    /**
     * Visualizar vaga
     */
    public function visualizar(): void
    {
        $this->autenticacaoRequired();

        $idVaga = (int)($_GET['id'] ?? 0);
        error_log("Visualizando objeto vaga com ID: $idVaga"); // Log the idVaga value
        error_log("objeto vaga: " . print_r($this->vagaService->buscarPorId($idVaga), true)); // Log the vaga object
        error_log("verificando se usuario já demonstrou interesse: " . print_r($this->interesseService->jaDemonstrouInteresse($idVaga, $this->usuarioLogado()->getIdUsuario()), true)); // Log the result of jaDemonstrouInteresse
        if ($idVaga <= 0) {
            $this->redirect(URL_BASE . '/vagas');
        }

        $vaga = $this->vagaService->buscarPorId($idVaga);

        if (!$vaga) {
            $this->redirect(URL_BASE . '/vagas');
        }

        $contratante = $this->usuarioService
            ->buscarPorId($vaga->getIdContratante());

        $usuario = $this->usuarioLogado();

        $jaDemonstrouInteresse = false;

        if (
            $usuario !== null &&
            $usuario->getTipoUsuario() === 'TRABALHADOR'
        ) {
            $jaDemonstrouInteresse =
                $this->interesseService->jaDemonstrouInteresse(
                    $idVaga,
                    $usuario->getIdUsuario(),
                    $dataInteresse = null
                );
        }

        $this->view('vaga/vaga_show', [
            'vaga' => $vaga,
            'contratante' => $contratante,
            'usuario' => $usuario,
            'jaDemonstrouInteresse' => $jaDemonstrouInteresse
        ]);
    }

public function buscar(): void
{
    $this->autenticacaoRequired();

    $titulo = trim($_GET['keywords'] ?? '');
    $localizacao = trim($_GET['localizacao'] ?? '');

    $vagas = $this->vagaService->buscar($titulo, $localizacao);

    $this->view('vaga/vaga_busca', [
        'vagas' => $vagas,
        'usuario' => $this->usuarioLogado(),
        'filtros' => $_GET,
        'totalResultados' => count($vagas)
    ]);
}

/**
 * Exibir formulário de criação
 */
public function exibirFormCriar(): void
{
    $this->contratanteRequired();

    $this->view('vaga/vaga_form', [
        'acao' => 'criar'
    ]);
}

/**
 * Criar vaga
 */
public function criar(): void
{
    $this->contratanteRequired();

    $usuario = $this->usuarioLogado();

    $idCategoria = (int)($_POST['id_categoria'] ?? 0);
    $titulo = trim($_POST['titulo'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $localizacao = trim($_POST['localizacao'] ?? '');
    $remuneracao = $_POST['remuneracao'] ?? null;
    $dataLimite = $_POST['data_limite'] ?? null;
    $trabalhadoresLimite = $_POST['trabalhadores_limite'] ?? 1;

    $validador = new Validador();

    $validador
        ->obrigatorio('titulo', $titulo)
        ->obrigatorio('descricao', $descricao)
        ->obrigatorio('id_categoria', $idCategoria);

    if ($validador->temErros()) {
        $this->view('vaga/vaga_form', [
            'erros' => $validador->getErros(),
            'acao' => 'criar'
        ]);
        return;
    }

    try {

        $id = $this->service->criar(
            $usuario->getIdUsuario(),
            $idCategoria,
            $titulo,
            $descricao,
            $localizacao ?: null,
            $remuneracao !== '' ? (float)$remuneracao : null,
            $dataLimite ?: null,
            (int)$trabalhadoresLimite
        );

        $this->redirect(URL_BASE . '/vagas/visualizar?id=' . $id);

    } catch (\Exception $e) {

        $this->view('vaga/vaga_form', [
            'erro' => $e->getMessage(),
            'acao' => 'criar'
        ]);
    }
}
    /**
 * Exibir formulário de edição
 */
public function exibirFormEditar(): void
{
    $this->contratanteRequired();

    $idVaga = (int)($_GET['id'] ?? 0);

    if ($idVaga <= 0) {
        $this->redirect(URL_BASE . '/vagas');
    }

    $vaga = $this->service->buscarPorId($idVaga);

    if (!$vaga || $vaga->getIdContratante() !== $this->usuarioLogado()->getIdUsuario()) {
        $this->redirect(URL_BASE . '/403');
    }

    $this->view('vaga/vaga_form', [
        'vaga' => $vaga,
        'acao' => 'editar'
    ]);
}

/**
 * Editar vaga
 */
public function editar(): void
{
    $this->contratanteRequired();

    $usuario = $this->usuarioLogado();

    $idVaga = (int)($_POST['id'] ?? 0);

    $vaga = $this->service->buscarPorId($idVaga);

    if (!$vaga || $vaga->getIdContratante() !== $usuario->getIdUsuario()) {
        $this->redirect(URL_BASE . '/403');
    }

    $idCategoria = (int)($_POST['id_categoria'] ?? 0);
    $titulo = trim($_POST['titulo'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $localizacao = trim($_POST['localizacao'] ?? '');
    $remuneracao = $_POST['remuneracao'] ?? null;
    $dataLimite = $_POST['data_limite'] ?? null;
    $trabalhadoresLimite = $_POST['trabalhadores_limite'] ?? 1;

    $validador = new Validador();

    $validador
        ->obrigatorio('titulo', $titulo)
        ->obrigatorio('descricao', $descricao)
        ->obrigatorio('id_categoria', $idCategoria);

    if ($validador->temErros()) {

        $this->view('vaga/vaga_form', [
            'vaga' => $vaga,
            'erros' => $validador->getErros(),
            'acao' => 'editar'
        ]);

        return;
    }

    try {

        $vaga->setTitulo($titulo);
        $vaga->setDescricao($descricao);
        $vaga->setLocalizacao($localizacao ?: null);
        $vaga->setRemuneracao($remuneracao !== '' ? (float)$remuneracao : null);
        $vaga->setDataLimite($dataLimite ?: null);
        $vaga->setTrabalhadoresLimite((int)$trabalhadoresLimite);

        /*
         * Necessário adicionar este setter no model Vaga:
         *
         * public function setIdCategoria(int $idCategoria): void
         * {
         *     $this->idCategoria = $idCategoria;
         * }
         */
        $vaga->setIdCategoria($idCategoria);

        $this->service->atualizar($vaga);

        $this->redirect(URL_BASE . '/vagas/visualizar?id=' . $idVaga);

    } catch (\Exception $e) {

        $this->view('vaga/vaga_form', [
            'vaga' => $vaga,
            'erro' => $e->getMessage(),
            'acao' => 'editar'
        ]);
    }
    }
    /**
 * Excluir vaga
 */
public function excluir(): void
{
    $this->contratanteRequired();

    $usuario = $this->usuarioLogado();

    $idVaga = (int)($_POST['id'] ?? 0);

    $vaga = $this->service->buscarPorId($idVaga);

    if (!$vaga || $vaga->getIdContratante() !== $usuario->getIdUsuario()) {
        $this->redirect(URL_BASE . '/403');
    }

    try {

        $this->service->deletar($idVaga);

        $this->redirect(URL_BASE . '/vagas');

    } catch (\Exception $e) {

        $this->redirect(URL_BASE . '/vagas/visualizar?id=' . $idVaga);
    }
}

/**
 * Encerrar vaga
 */
public function encerrar(): void
{
    $this->contratanteRequired();

    $usuario = $this->usuarioLogado();

    $idVaga = (int)($_POST['id'] ?? 0);

    $vaga = $this->service->buscarPorId($idVaga);

    if (!$vaga || $vaga->getIdContratante() !== $usuario->getIdUsuario()) {
        $this->redirect(URL_BASE . '/403');
    }

    try {

        $this->service->encerrar($idVaga);

    } catch (\Exception $e) {
        // Opcional: registrar log
    }

    $this->redirect(URL_BASE . '/vagas/visualizar?id=' . $idVaga);
}

/**
 * Reabrir vaga
 */
public function reabrir(): void
{
    $this->contratanteRequired();

    $usuario = $this->usuarioLogado();

    $idVaga = (int)($_POST['id'] ?? 0);

    $vaga = $this->service->buscarPorId($idVaga);

    if (!$vaga || $vaga->getIdContratante() !== $usuario->getIdUsuario()) {
        $this->redirect(URL_BASE . '/403');
    }

    try {

        $this->service->reabrir($idVaga);

    } catch (\Exception $e) {
        // Opcional: registrar log
    }

    $this->redirect(URL_BASE . '/vagas/visualizar?id=' . $idVaga);
}
}
