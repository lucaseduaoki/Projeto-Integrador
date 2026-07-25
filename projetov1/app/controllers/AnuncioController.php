<?php

namespace app\controllers;

use app\core\Controller;
use app\helpers\Validador;
use app\models\Anuncio;
use app\services\AnuncioService;

class AnuncioController extends Controller
{
    private AnuncioService $service;

    public function __construct()
    {
        $this->service = new AnuncioService();
    }

    /**
     * Listar anúncios abertos
     */
    public function listar(): void
    {
        error_log('[ANUNCIO] listar() iniciado');
        $this->autenticacaoRequired();

        $limit = (int)($_GET['limit'] ?? 50);
        $offset = (int)($_GET['offset'] ?? 0);
        error_log('[ANUNCIO] Parâmetros - limit: ' . $limit . ', offset: ' . $offset);

        $anuncios = $this->service->listar($limit, $offset);
        error_log('[ANUNCIO] Anúncios listados: ' . count($anuncios));

        $this->view('anuncio/anuncio_list', [
            'anuncios' => $anuncios,
            'usuario' => $this->usuarioLogado()
        ]);
    }

    /**
     * Buscar anúncios
     */
    public function buscar(): void
    {
        error_log('[ANUNCIO] buscar() iniciado');
        $this->autenticacaoRequired();

        $titulo = htmlspecialchars(trim($_GET['titulo'] ?? ''), ENT_QUOTES, 'UTF-8');
        $localizacao = htmlspecialchars(trim($_GET['localizacao'] ?? ''), ENT_QUOTES, 'UTF-8');
        $tipoServico = htmlspecialchars(trim($_GET['tipo_servico'] ?? ''), ENT_QUOTES, 'UTF-8');
        error_log('[ANUNCIO] Filtros - titulo: ' . $titulo . ', localizacao: ' . $localizacao . ', tipo: ' . $tipoServico);

        $anuncios = $this->service->buscar($titulo, $localizacao, $tipoServico ?: null);
        error_log('[ANUNCIO] Resultados encontrados: ' . count($anuncios));

        $this->view('anuncio/anuncio_busca', [
            'anuncios' => $anuncios,
            'titulo' => $titulo,
            'localizacao' => $localizacao,
            'tipoServico' => $tipoServico
        ]);
    }

    /**
     * Visualizar anúncio
     */
    public function visualizar(): void
    {
        error_log('[ANUNCIO] visualizar() iniciado');
        $this->autenticacaoRequired();

        $idAnuncio = (int)($_GET['id'] ?? 0);
        error_log('[ANUNCIO] ID do anúncio: ' . $idAnuncio);
        if ($idAnuncio <= 0) {
            error_log('[ANUNCIO] ID inválido, redirecionando');
            $this->redirect(URL_BASE . '/anuncios');
        }

        $anuncio = $this->service->buscarPorId($idAnuncio);
        if (!$anuncio) {
            error_log('[ANUNCIO] Anúncio não encontrado, redirecionando');
            $this->redirect(URL_BASE . '/anuncios');
        }
        error_log('[ANUNCIO] Anúncio encontrado: ' . $anuncio->getTitulo());

        // Buscar contratante
        $usuarioService = new \app\services\UsuarioService();
        $contratante = $usuarioService->buscarPorId($anuncio->getIdContratante());
        error_log('[ANUNCIO] Contratante: ' . ($contratante ? $contratante->getNome() : 'NÃO ENCONTRADO'));

        // Verificar se usuário já se candidatou
        $usuarioLogado = $this->usuarioLogado();
        $jaCanditatou = false;
        if ($usuarioLogado && $usuarioLogado->isTrabalhador()) {
            $candidaturaService = new \app\services\CandidaturaService();
            $jaCanditatou = $candidaturaService->verificarCandidatura($usuarioLogado->getIdUsuario(), $idAnuncio);
            error_log('[ANUNCIO] Usuário já se candidatou: ' . ($jaCanditatou ? 'SIM' : 'NÃO'));
        }

        $this->view('anuncio/anuncio_show', [
            'anuncio' => $anuncio,
            'contratante' => $contratante,
            'jaCanditatou' => $jaCanditatou,
            'usuarioLogado' => $usuarioLogado,
        ]);
    }

    /**
     * Exibir formulário de criar anúncio
     */
    public function exibirFormCriar(): void
    {
        error_log('[ANUNCIO] exibirFormCriar() iniciado');
        $this->contratanteRequired();

        $this->view('anuncio/anuncio_form', [
            'acao' => 'criar'
        ]);
    }

    /**
     * Criar anúncio
     */
    public function criar(): void
    {
        error_log('[ANUNCIO] criar() iniciado');
        $this->contratanteRequired();

        $usuario = $this->usuarioLogado();
        error_log('[ANUNCIO] Usuário criador: ' . $usuario->getIdUsuario());

        // Sanitizar
        $titulo = htmlspecialchars(trim($_POST['titulo'] ?? ''), ENT_QUOTES, 'UTF-8');
        $descricao = htmlspecialchars(trim($_POST['descricao'] ?? ''), ENT_QUOTES, 'UTF-8');
        $localizacao = htmlspecialchars(trim($_POST['localizacao'] ?? ''), ENT_QUOTES, 'UTF-8');
        $data = $_POST['data'] ?? '';
        $remuneracao = $_POST['remuneracao'] ?? '';
        $tipoServico = htmlspecialchars(trim($_POST['tipo_servico'] ?? ''), ENT_QUOTES, 'UTF-8');
        $duracao = htmlspecialchars(trim($_POST['duracao'] ?? ''), ENT_QUOTES, 'UTF-8');
        $observacoes = htmlspecialchars(trim($_POST['observacoes'] ?? ''), ENT_QUOTES, 'UTF-8');
        $prazoCandidatura = $_POST['prazo_candidatura'] ?? '';

        // Validar
        $validador = new Validador();
        $validador->obrigatorio('titulo', $titulo)
                  ->minimo('titulo', $titulo, 3)
                  ->maximo('titulo', $titulo, 100)
                  ->obrigatorio('descricao', $descricao)
                  ->minimo('descricao', $descricao, 10)
                  ->obrigatorio('tipo_servico', $tipoServico)
                  ->emLista('tipo_servico', $tipoServico, ['TEMPORARIO', 'FIXO']);

        if (!empty($remuneracao)) {
            $validador->numerico('remuneracao', $remuneracao);
        }

        if ($validador->temErros()) {
            error_log('[ANUNCIO] Erros de validação ao criar: ' . json_encode($validador->getErros()));
            $this->view('anuncio/anuncio_form', [
                'erros' => $validador->getErros(),
                'acao' => 'criar'
            ]);
            return;
        }

        try {
            error_log('[ANUNCIO] Criando anúncio com título: ' . $titulo);
            $idAnuncio = $this->service->criar(
                $usuario->getIdUsuario(),
                $titulo,
                $descricao,
                $localizacao ?: null,
                $data ?: null,
                !empty($remuneracao) ? (float)$remuneracao : null,
                $tipoServico,
                $duracao ?: null,
                $observacoes ?: null,
                $prazoCandidatura ?: null
            );
            error_log('[ANUNCIO] Anúncio criado com ID: ' . $idAnuncio);

            $this->redirect(URL_BASE . '/anuncios/visualizar?id=' . $idAnuncio);
        } catch (\Exception $e) {
            error_log('[ANUNCIO] Erro ao criar: ' . $e->getMessage());
            $this->view('anuncio/anuncio_form', [
                'erro' => $e->getMessage(),
                'acao' => 'criar'
            ]);
        }
    }

    /**
     * Exibir formulário de editar
     */
    public function exibirFormEditar(): void
    {
        error_log('[ANUNCIO] exibirFormEditar() iniciado');
        $this->contratanteRequired();

        $idAnuncio = (int)($_GET['id'] ?? 0);
        error_log('[ANUNCIO] ID do anúncio para editar: ' . $idAnuncio);
        if ($idAnuncio <= 0) {
            error_log('[ANUNCIO] ID inválido para editar');
            $this->redirect(URL_BASE . '/anuncios');
        }

        $anuncio = $this->service->buscarPorId($idAnuncio);
        if (!$anuncio || $anuncio->getIdContratante() !== $this->usuarioLogado()->getIdUsuario()) {
            error_log('[ANUNCIO] Permissão negada para editar anúncio ' . $idAnuncio);
            $this->redirect(URL_BASE . '/403');
        }
        error_log('[ANUNCIO] Formulário de edição carregado para anúncio: ' . $idAnuncio);

        $this->view('anuncio/anuncio_form', [
            'anuncio' => $anuncio,
            'acao' => 'editar'
        ]);
    }

    /**
     * Editar anúncio
     */
    public function editar(): void
    {
        error_log('[ANUNCIO] editar() iniciado');
        $this->contratanteRequired();

        $usuario = $this->usuarioLogado();
        $idAnuncio = (int)($_POST['id'] ?? 0);
        error_log('[ANUNCIO] Editando anúncio ID: ' . $idAnuncio . ', usuário: ' . $usuario->getIdUsuario());

        $anuncio = $this->service->buscarPorId($idAnuncio);
        if (!$anuncio || $anuncio->getIdContratante() !== $usuario->getIdUsuario()) {
            error_log('[ANUNCIO] Permissão negada para editar anúncio ' . $idAnuncio);
            $this->redirect(URL_BASE . '/403');
        }

        // Sanitizar
        error_log('[ANUNCIO] Processando dados de edição');
        $titulo = htmlspecialchars(trim($_POST['titulo'] ?? ''), ENT_QUOTES, 'UTF-8');
        $descricao = htmlspecialchars(trim($_POST['descricao'] ?? ''), ENT_QUOTES, 'UTF-8');
        $localizacao = htmlspecialchars(trim($_POST['localizacao'] ?? ''), ENT_QUOTES, 'UTF-8');
        $data = $_POST['data'] ?? '';
        $remuneracao = $_POST['remuneracao'] ?? '';
        $tipoServico = htmlspecialchars(trim($_POST['tipo_servico'] ?? ''), ENT_QUOTES, 'UTF-8');
        $duracao = htmlspecialchars(trim($_POST['duracao'] ?? ''), ENT_QUOTES, 'UTF-8');
        $observacoes = htmlspecialchars(trim($_POST['observacoes'] ?? ''), ENT_QUOTES, 'UTF-8');
        $prazoCandidatura = $_POST['prazo_candidatura'] ?? '';

        // Validar
        $validador = new Validador();
        $validador->obrigatorio('titulo', $titulo)
                  ->minimo('titulo', $titulo, 3)
                  ->obrigatorio('descricao', $descricao)
                  ->minimo('descricao', $descricao, 10)
                  ->obrigatorio('tipo_servico', $tipoServico)
                  ->emLista('tipo_servico', $tipoServico, ['TEMPORARIO', 'FIXO']);

        if ($validador->temErros()) {
            error_log('[ANUNCIO] Erros de validação ao editar: ' . json_encode($validador->getErros()));
            $this->view('anuncio/anuncio_form', [
                'anuncio' => $anuncio,
                'erros' => $validador->getErros(),
                'acao' => 'editar'
            ]);
            return;
        }

        try {
            error_log('[ANUNCIO] Atualizando anúncio ' . $idAnuncio);
            $anuncio->setTitulo($titulo);
            $anuncio->setDescricao($descricao);
            $anuncio->setLocalizacao($localizacao ?: null);
            $anuncio->setData($data ?: null);
            $anuncio->setRemuneracao(!empty($remuneracao) ? (float)$remuneracao : null);
            $anuncio->setTipoServico($tipoServico);
            $anuncio->setDuracao($duracao ?: null);
            $anuncio->setObservacoes($observacoes ?: null);
            $anuncio->setPrazoCandidatura($prazoCandidatura ?: null);

            $this->service->atualizar($anuncio);
            error_log('[ANUNCIO] Anúncio ' . $idAnuncio . ' atualizado com sucesso');

            $this->redirect(URL_BASE . '/anuncios/visualizar?id=' . $idAnuncio);
        } catch (\Exception $e) {
            error_log('[ANUNCIO] Erro ao editar: ' . $e->getMessage());
            $this->view('anuncio/anuncio_form', [
                'anuncio' => $anuncio,
                'erro' => $e->getMessage(),
                'acao' => 'editar'
            ]);
        }
    }

    /**
     * Excluir anúncio
     */
    public function excluir(): void
    {
        error_log('[ANUNCIO] excluir() iniciado');
        $this->contratanteRequired();

        $usuario = $this->usuarioLogado();
        $idAnuncio = (int)($_POST['id'] ?? 0);
        error_log('[ANUNCIO] Excluindo anúncio ID: ' . $idAnuncio . ', usuário: ' . $usuario->getIdUsuario());

        $anuncio = $this->service->buscarPorId($idAnuncio);
        if (!$anuncio || $anuncio->getIdContratante() !== $usuario->getIdUsuario()) {
            error_log('[ANUNCIO] Permissão negada para excluir anúncio ' . $idAnuncio);
            $this->redirect(URL_BASE . '/403');
        }

        try {
            error_log('[ANUNCIO] Deletando anúncio ' . $idAnuncio);
            $this->service->deletar($idAnuncio);
            error_log('[ANUNCIO] Anúncio ' . $idAnuncio . ' excluído com sucesso');
            $this->redirect(URL_BASE . '/anuncios');
        } catch (\Exception $e) {
            error_log('[ANUNCIO] Erro ao excluir: ' . $e->getMessage());
            $this->redirect(URL_BASE . '/anuncios/visualizar?id=' . $idAnuncio);
        }
    }
}
