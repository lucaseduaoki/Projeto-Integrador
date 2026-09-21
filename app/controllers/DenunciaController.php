<?php

namespace app\controllers;

use app\core\Controller;
use app\helpers\Validador;
use app\models\Denuncia;
use app\services\DenunciaService;
use app\services\UsuarioService;
use app\services\VagaService;

class DenunciaController extends Controller
{
    private DenunciaService $service;
    private UsuarioService $usuarioService;
    private VagaService $vagaService;

    public function __construct()
    {
        $this->vagaService = new VagaService();
        $this->service = new DenunciaService();
        $this->usuarioService = new UsuarioService();
    }

    /**
     * Exibir formulário de denúncia.
     * ?id=<usuário> denuncia um usuário; ?vaga=<vaga> denuncia um anúncio (RN13).
     */
    public function exibirFormDenunciar(): void
    {
        $this->autenticacaoRequired();

        $usuario = $this->usuarioLogado();
        $idVaga = (int)($_GET['vaga'] ?? 0);

        if ($idVaga > 0) {
            $vaga = $this->vagaService->buscarPorId($idVaga);

            // Anúncio precisa existir e não pode ser do próprio denunciante
            if ($vaga === null || $vaga->getIdContratante() === $usuario->getIdUsuario()) {
                $this->redirect(URL_BASE . '/vagas');
            }

            $this->view('denuncia/denuncia_form', [
                'vaga' => $vaga,
                'motivos' => Denuncia::motivosPara(Denuncia::TIPO_ANUNCIO),
            ]);
            return;
        }

        $idDenunciado = (int)($_GET['id'] ?? 0);
        $denunciado = $idDenunciado > 0 ? $this->usuarioService->buscarPorId($idDenunciado) : null;

        if ($denunciado === null || $idDenunciado === $usuario->getIdUsuario()) {
            $this->redirect(URL_BASE . '/vagas');
        }

        $this->view('denuncia/denuncia_form', [
            'denunciado' => $denunciado,
            'motivos' => Denuncia::motivosPara(Denuncia::TIPO_USUARIO),
        ]);
    }

    /**
     * Criar denúncia
     */
    public function denunciar(): void
    {
        $this->autenticacaoRequired();

        $usuario = $this->usuarioLogado();
        $motivo = trim($_POST['motivo'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $idVaga = (int)($_POST['id_vaga'] ?? 0);

        // Cada tipo de alvo (anúncio ou usuário) tem seus motivos (RN13)
        $vaga = null;
        $denunciado = null;
        $idDenunciado = null;

        if ($idVaga > 0) {
            // Denúncia de anúncio: o alvo é a vaga; o dono vem do banco, não do formulário
            $vaga = $this->vagaService->buscarPorId($idVaga);

            if ($vaga === null || $vaga->getIdContratante() === $usuario->getIdUsuario()) {
                $this->redirect(URL_BASE . '/vagas');
            }

            $tipo = Denuncia::TIPO_ANUNCIO;
        } else {
            $idDenunciado = (int)($_POST['id_usuario_denunciado'] ?? 0);
            $denunciado = $idDenunciado > 0 ? $this->usuarioService->buscarPorId($idDenunciado) : null;

            // O alvo precisa existir e não pode ser a própria pessoa
            if ($denunciado === null || $idDenunciado === $usuario->getIdUsuario()) {
                $this->redirect(URL_BASE . '/vagas');
            }

            $tipo = Denuncia::TIPO_USUARIO;
        }

        $motivos = Denuncia::motivosPara($tipo);

        // Validar
        $validador = new Validador();
        $validador->obrigatorio('motivo', $motivo, 'Selecione o motivo da denúncia.')
                  ->emLista('motivo', $motivo, array_keys($motivos), 'Motivo inválido: escolha uma das opções.')
                  ->tamanhoMax('descricao', $descricao, 1000, 'A descrição deve ter no máximo 1000 caracteres.');

        $dadosForm = ['denunciado' => $denunciado, 'vaga' => $vaga, 'motivos' => $motivos];

        if ($validador->temErros()) {
            $this->view('denuncia/denuncia_form', $dadosForm + ['erros' => $validador->getErros()]);
            return;
        }

        try {
            $this->service->criar(
                $usuario->getIdUsuario(),
                $idDenunciado,
                $motivo,
                $descricao ?: null,
                $vaga?->getIdVaga()
            );

            $this->view('denuncia/sucesso', [
                'mensagem' => 'Denúncia registrada. Obrigado por manter a plataforma segura!'
            ]);
        } catch (\Exception $e) {
            $this->view('denuncia/denuncia_form', $dadosForm + ['erro' => $e->getMessage()]);
        }
    }

    /**
     * Listar denúncias (admin)
     */
    public function listar(): void
    {
        $this->adminRequired();

        $status = htmlspecialchars(trim($_GET['status'] ?? ''), ENT_QUOTES, 'UTF-8');

        if ($status === 'pendentes') {
            $denuncias = $this->service->listarPendentes();
        } else {
            $denuncias = $this->service->listarTodas();
        }

        $this->view('denuncia/listar', [
            'denuncias' => $denuncias,
            'status' => $status,
            'erroModeracao' => trim((string)($_GET['erro'] ?? '')),
        ]);
    }

    /**
     * Moderar denúncia (admin)
     */
    public function moderar(): void
    {
        $this->adminRequired();

        $idDenuncia = (int)($_POST['id'] ?? 0);
        $acao = htmlspecialchars(trim($_POST['acao'] ?? ''), ENT_QUOTES, 'UTF-8');

        if ($idDenuncia <= 0 || !in_array($acao, ['bloquear', 'analisar', 'advertir', 'ocultar', 'remover'], true)) {
            $this->redirect(URL_BASE . '/admin/denuncias');
        }

        try {
            if ($acao === 'bloquear') {
                $this->service->bloquearPorDenuncia($idDenuncia);
            } elseif ($acao === 'ocultar' || $acao === 'remover') {
                $this->service->moderarAnuncio($idDenuncia, $acao === 'ocultar' ? 'OCULTA' : 'REMOVIDA');
            } elseif ($acao === 'advertir') {
                $this->service->advertir(
                    $idDenuncia,
                    (string)($_POST['mensagem'] ?? ''),
                    $this->usuarioLogado()->getIdUsuario()
                );
            } else {
                $this->service->analisar($idDenuncia);
            }

            $this->redirect(URL_BASE . '/admin/denuncias?status=pendentes');
        } catch (\Exception $e) {
            $this->redirect(URL_BASE . '/admin/denuncias?erro=' . urlencode($e->getMessage()));
        }
    }
}
