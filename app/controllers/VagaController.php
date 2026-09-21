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

        $contratante = $this->usuarioService->buscarPorId($vaga->getIdContratante());

        $usuario = $this->usuarioLogado();

        $jaDemonstrouInteresse = false;

        if (
            $usuario !== null &&
            $usuario->isTrabalhador()
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



    /**
     * Buscar vagas por palavra-chave e localização
     */
    public function buscar(): void
    {
        $this->autenticacaoRequired();

        $filtros = [
            'titulo' => trim((string)($_GET['keywords'] ?? '')),
            'localizacao' => trim((string)($_GET['localizacao'] ?? '')),
        ];

        // Tipo de serviço: só aceita os valores do domínio, qualquer outro é ignorado
        $tipoServico = trim((string)($_GET['tipo_servico'] ?? ''));
        if (in_array($tipoServico, ['FIXO', 'TEMPORARIO'], true)) {
            $filtros['tipo_servico'] = $tipoServico;
        }

        // Data a partir de: formato válido ou ignorada, com aviso
        $erros = [];
        $dataFrom = trim((string)($_GET['data_from'] ?? ''));
        if ($dataFrom !== '') {
            $validador = new Validador();
            $validador->dataValida('data_from', $dataFrom, false, 'A data do filtro é inválida e foi ignorada.');
            if ($validador->temErros()) {
                $erros = $validador->getErros();
            } else {
                $filtros['data_from'] = $dataFrom;
            }
        }

        $vagas = $this->vagaService->buscar($filtros);

        $this->view('vaga/vaga_busca', [
            'vagas' => $vagas,
            'usuario' => $this->usuarioLogado(),
            'filtros' => $_GET,
            'erros' => $erros,
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
    error_log("ENTROU NO CONTROLLER criar");
    $this->contratanteRequired();
        error_log("Informações recebidas para criar vaga: " . print_r($_POST, true)); // Log the received data

    $usuario = $this->usuarioLogado();

    $idCategoria = (int)($_POST['id_categoria'] ?? 0);
    $titulo = trim($_POST['titulo'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $localizacao = trim($_POST['localizacao'] ?? '');
    $remuneracao = $_POST['remuneracao'] ?? null;
    $dataLimite = $_POST['data_limite'] ?? null;
    $dataServico = trim($_POST['data_servico'] ?? '');
    $horario = trim($_POST['horario'] ?? '');
    $tipoServico = trim($_POST['tipo_servico'] ?? '');
    $duracao = trim($_POST['duracao'] ?? '');
    $observacoes = trim($_POST['observacoes'] ?? '');
    $trabalhadoresLimite = $_POST['trabalhadores_limite'] ?? 1;

    $validador = new Validador();

    $validador
        ->obrigatorio('titulo', $titulo)
        ->obrigatorio('descricao', $descricao)
        ->obrigatorio('id_categoria', $idCategoria)
        ->dataValida('data_servico', $dataServico, false, 'A data do serviço deve ser uma data válida (aaaa-mm-dd).')
        ->obrigatorio('horario', $horario, 'Informe o horário (hh:mm).')
        ->horaValida('horario', $horario, 'O horário deve estar no formato hh:mm.')
        ->obrigatorio('tipo_servico', $tipoServico, 'Selecione o tipo de serviço (fixo ou temporário).')
        ->emLista('tipo_servico', $tipoServico, ['FIXO', 'TEMPORARIO'], 'Tipo de serviço inválido: escolha fixo ou temporário.')
        ->tamanhoMax('duracao', $duracao, 50, 'A duração deve ter no máximo 50 caracteres.')
        ->tamanhoMax('observacoes', $observacoes, 500, 'As observações devem ter no máximo 500 caracteres.');

    if ($tipoServico === 'TEMPORARIO') {
        $validador->obrigatorio('duracao', $duracao, 'Informe a duração do serviço temporário (ex.: 3 dias).');
    } else {
        $duracao = '';
    }

    if ($validador->temErros()) {
        $this->view('vaga/vaga_form', [
            'erros' => $validador->getErros(),
            'acao' => 'criar'
        ]);
        return;
    }
    try {

        $id = $this->vagaService->criar(
            $usuario->getIdUsuario(),
            $idCategoria,
            $titulo,
            $descricao,
            $localizacao ?: null,
            $remuneracao !== '' ? (float)$remuneracao : null,
            $dataLimite ?: null,
            (int)$trabalhadoresLimite,
            $horario,
            $tipoServico,
            $duracao ?: null,
            $observacoes ?: null,
            $dataServico ?: null
        );

        $this->redirect(URL_BASE . '/vagas/visualizar?id=' . $id);

    } catch (\Exception $e) {

        $this->view('vaga/vaga_form', [
            'erro' => $e->getMessage(),
            'acao' => 'criar'
        ]);
    }
}

public function minhas(): void
{
    $this->contratanteRequired();

    $usuario = $this->usuarioLogado();

    $vagas = $this->vagaService
        ->listarPorContratante(
            $usuario->getIdUsuario()
        );


    $this->view('vaga/minhas', [
        'vagas' => $vagas,
        'usuario' => $usuario
    ]);
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

    $vaga = $this->vagaService->buscarPorId($idVaga);

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

    $vaga = $this->vagaService->buscarPorId($idVaga);

    if (!$vaga || $vaga->getIdContratante() !== $usuario->getIdUsuario()) {
        $this->redirect(URL_BASE . '/403');
    }

    $idCategoria = (int)($_POST['id_categoria'] ?? 0);
    $titulo = trim($_POST['titulo'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $localizacao = trim($_POST['localizacao'] ?? '');
    $remuneracao = $_POST['remuneracao'] ?? null;
    $dataLimite = $_POST['data_limite'] ?? null;
    $dataServico = trim($_POST['data_servico'] ?? '');
    $horario = trim($_POST['horario'] ?? '');
    $tipoServico = trim($_POST['tipo_servico'] ?? '');
    $duracao = trim($_POST['duracao'] ?? '');
    $observacoes = trim($_POST['observacoes'] ?? '');
    $trabalhadoresLimite = $_POST['trabalhadores_limite'] ?? 1;

    $validador = new Validador();

    $validador
        ->obrigatorio('titulo', $titulo)
        ->obrigatorio('descricao', $descricao)
        ->obrigatorio('id_categoria', $idCategoria)
        ->dataValida('data_servico', $dataServico, false, 'A data do serviço deve ser uma data válida (aaaa-mm-dd).')
        ->obrigatorio('horario', $horario, 'Informe o horário (hh:mm).')
        ->horaValida('horario', $horario, 'O horário deve estar no formato hh:mm.')
        ->obrigatorio('tipo_servico', $tipoServico, 'Selecione o tipo de serviço (fixo ou temporário).')
        ->emLista('tipo_servico', $tipoServico, ['FIXO', 'TEMPORARIO'], 'Tipo de serviço inválido: escolha fixo ou temporário.')
        ->tamanhoMax('duracao', $duracao, 50, 'A duração deve ter no máximo 50 caracteres.')
        ->tamanhoMax('observacoes', $observacoes, 500, 'As observações devem ter no máximo 500 caracteres.');

    if ($tipoServico === 'TEMPORARIO') {
        $validador->obrigatorio('duracao', $duracao, 'Informe a duração do serviço temporário (ex.: 3 dias).');
    } else {
        $duracao = '';
    }

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
        $vaga->setHorario($horario);
        $vaga->setTipoServico($tipoServico);
        $vaga->setDuracao($duracao ?: null);
        $vaga->setObservacoes($observacoes ?: null);
        $vaga->setDataServico($dataServico ?: null);

        $vaga->setIdCategoria($idCategoria);

        $this->vagaService->atualizar($vaga);

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

    $vaga = $this->vagaService->buscarPorId($idVaga);

    if (!$vaga || $vaga->getIdContratante() !== $usuario->getIdUsuario()) {
        $this->redirect(URL_BASE . '/403');
    }

    try {

        $this->vagaService->deletar($idVaga);

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

    $vaga = $this->vagaService->buscarPorId($idVaga);

    if (!$vaga || $vaga->getIdContratante() !== $usuario->getIdUsuario()) {
        $this->redirect(URL_BASE . '/403');
    }

    try {

        $this->vagaService->encerrar($idVaga);

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

    $vaga = $this->vagaService->buscarPorId($idVaga);

    if (!$vaga || $vaga->getIdContratante() !== $usuario->getIdUsuario()) {
        $this->redirect(URL_BASE . '/403');
    }

    try {

        $this->vagaService->reabrir($idVaga);

    } catch (\Exception $e) {
        // Opcional: registrar log
    }

    $this->redirect(URL_BASE . '/vagas/visualizar?id=' . $idVaga);
}
}
