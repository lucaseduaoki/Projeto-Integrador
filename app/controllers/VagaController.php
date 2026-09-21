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

        // Anúncio oculto/removido pela moderação só é visto pelo dono e pelo admin
        if (
            !$vaga->estaVisivel() &&
            !$usuario->isAdmin() &&
            $vaga->getIdContratante() !== $usuario->getIdUsuario()
        ) {
            $this->redirect(URL_BASE . '/vagas');
        }

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

        // Faixa de remuneração: numérica, não negativa e com mínimo <= máximo
        $validador = new Validador();
        $faixa = [];
        foreach (['remuneracao_min' => 'mínima', 'remuneracao_max' => 'máxima'] as $campo => $rotulo) {
            $valor = trim((string)($_GET[$campo] ?? ''));
            if ($valor === '') {
                continue;
            }

            $validador->numerico($campo, $valor, "A remuneração {$rotulo} deve ser um número e foi ignorada.");
            if (!isset($validador->getErros()[$campo]) && (float)$valor < 0) {
                $validador->erro($campo, "A remuneração {$rotulo} não pode ser negativa e foi ignorada.");
            }

            if (!isset($validador->getErros()[$campo])) {
                $faixa[$campo] = (string)(float)$valor;
            }
        }

        if (isset($faixa['remuneracao_min'], $faixa['remuneracao_max'])
            && (float)$faixa['remuneracao_min'] > (float)$faixa['remuneracao_max']) {
            $validador->erro('remuneracao_min', 'A remuneração mínima é maior que a máxima: a faixa foi ignorada.');
            $faixa = [];
        }

        $erros += $validador->getErros();
        $filtros += $faixa;

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

    $validador = $this->validarDadosVaga(compact(
        'idCategoria', 'titulo', 'descricao', 'localizacao', 'remuneracao', 'dataLimite',
        'dataServico', 'horario', 'tipoServico', 'duracao', 'observacoes'
    ));

    // Serviço fixo não tem duração
    if ($tipoServico !== 'TEMPORARIO') {
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

/**
 * Valida os campos do formulário de vaga. Criação e edição compartilham as mesmas regras
 * (UC04.1), sempre no servidor; o HTML só ajuda a pessoa a preencher.
 */
private function validarDadosVaga(array $d): Validador
{
    $validador = new Validador();

    $validador
        ->obrigatorio('titulo', $d['titulo'], 'Informe a função (título) da vaga.')
        ->obrigatorio('descricao', $d['descricao'], 'Informe a descrição da vaga.')
        ->obrigatorio('localizacao', $d['localizacao'], 'Informe o local do serviço.')
        ->obrigatorio('id_categoria', $d['idCategoria'], 'Selecione a categoria.')
        ->dataValida('data_servico', $d['dataServico'], false, 'A data do serviço deve ser uma data válida (aaaa-mm-dd).')
        ->obrigatorio('horario', $d['horario'], 'Informe o horário (hh:mm).')
        ->horaValida('horario', $d['horario'], 'O horário deve estar no formato hh:mm.')
        ->obrigatorio('tipo_servico', $d['tipoServico'], 'Selecione o tipo de serviço (fixo ou temporário).')
        ->emLista('tipo_servico', $d['tipoServico'], ['FIXO', 'TEMPORARIO'], 'Tipo de serviço inválido: escolha fixo ou temporário.')
        ->tamanhoMax('duracao', $d['duracao'], 50, 'A duração deve ter no máximo 50 caracteres.')
        ->tamanhoMax('observacoes', $d['observacoes'], 500, 'As observações devem ter no máximo 500 caracteres.');

    // Função (título): 2 a 100 caracteres
    if ($d['titulo'] !== '') {
        $validador->tamanhoMinMax('titulo', $d['titulo'], 2, 100, 'A função (título) deve ter entre 2 e 100 caracteres.');
    }

    // Local: 2 a 150 caracteres
    if ($d['localizacao'] !== '') {
        $validador->tamanhoMinMax('localizacao', $d['localizacao'], 2, 150, 'O local deve ter entre 2 e 150 caracteres.');
    }

    // Descrição: 10 a 1000 caracteres
    if ($d['descricao'] !== '') {
        $validador->tamanhoMinMax('descricao', $d['descricao'], 10, 1000, 'A descrição deve ter entre 10 e 1000 caracteres.');
    }

    if ($d['tipoServico'] === 'TEMPORARIO') {
        $validador->obrigatorio('duracao', $d['duracao'], 'Informe a duração do serviço temporário (ex.: 3 dias).');
    }

    return $validador;
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

    if (!$vaga || !$this->podeGerenciarVaga($vaga)) {
        $this->redirect(URL_BASE . '/403');
    }

    if ($vaga->foiRemovidaPelaModeracao()) {
        $this->redirect(URL_BASE . '/vagas/visualizar?id=' . $idVaga);
    }

    $this->view('vaga/vaga_form', [
        'vaga' => $vaga,
        'acao' => 'editar',
        'temCandidaturas' => $this->vagaService->possuiCandidaturas($idVaga)
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

    if (!$vaga || !$this->podeGerenciarVaga($vaga)) {
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

    $validador = $this->validarDadosVaga(compact(
        'idCategoria', 'titulo', 'descricao', 'localizacao', 'remuneracao', 'dataLimite',
        'dataServico', 'horario', 'tipoServico', 'duracao', 'observacoes'
    ));

    // Serviço fixo não tem duração
    if ($tipoServico !== 'TEMPORARIO') {
        $duracao = '';
    }

    if ($validador->temErros()) {

        $this->view('vaga/vaga_form', [
            'vaga' => $vaga,
            'erros' => $validador->getErros(),
            'acao' => 'editar',
            'temCandidaturas' => $this->vagaService->possuiCandidaturas($idVaga)
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
            'acao' => 'editar',
            'temCandidaturas' => $this->vagaService->possuiCandidaturas($idVaga)
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

    if (!$vaga || !$this->podeGerenciarVaga($vaga)) {
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

    if (!$vaga || !$this->podeGerenciarVaga($vaga)) {
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

    if (!$vaga || !$this->podeGerenciarVaga($vaga)) {
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
