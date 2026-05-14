<?php

namespace app\controllers;

use app\core\Controller;
use app\helpers\Validador;
use app\services\DenunciaService;

class DenunciaController extends Controller
{
    private DenunciaService $service;

    public function __construct()
    {
        $this->service = new DenunciaService();
    }

    /**
     * Exibir formulário de denúncia
     */
    public function exibirFormDenunciar(): void
    {
        $this->autenticacaoRequired();

        $idDenunciado = (int)($_GET['id'] ?? 0);

        if ($idDenunciado <= 0) {
            $this->redirect(URL_BASE . '/anuncios');
        }

        $this->view('denuncia/denuncia_form', [
            'idDenunciado' => $idDenunciado,
        ]);
    }

    /**
     * Criar denúncia
     */
    public function denunciar(): void
    {
        $this->autenticacaoRequired();

        $usuario = $this->usuarioLogado();
        $idDenunciado = (int)($_POST['id_denunciado'] ?? 0);
        $motivo = htmlspecialchars(trim($_POST['motivo'] ?? ''), ENT_QUOTES, 'UTF-8');
        $descricao = htmlspecialchars(trim($_POST['descricao'] ?? ''), ENT_QUOTES, 'UTF-8');
        $idAnuncio = (int)($_POST['id_anuncio'] ?? 0);

        // Validar
        $validador = new Validador();
        $validador->obrigatorio('motivo', $motivo)
                  ->minimo('motivo', $motivo, 5);

        if ($validador->temErros()) {
            $this->view('denuncia/denuncia_form', [
                'idDenunciado' => $idDenunciado,
                'erros' => $validador->getErros(),
            ]);
            return;
        }

        try {
            $this->service->criar(
                $usuario->getIdUsuario(),
                $idDenunciado,
                $motivo,
                $descricao ?: null,
                $idAnuncio > 0 ? $idAnuncio : null
            );

            $this->view('denuncia/sucesso', [
                'mensagem' => 'Denúncia registrada. Obrigado por manter a plataforma segura!'
            ]);
        } catch (\Exception $e) {
            $this->view('denuncia/denuncia_form', [
                'idDenunciado' => $idDenunciado,
                'erro' => $e->getMessage(),
            ]);
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

        if ($idDenuncia <= 0 || !in_array($acao, ['bloquear', 'analisar'], true)) {
            $this->redirect(URL_BASE . '/admin/denuncias');
        }

        try {
            if ($acao === 'bloquear') {
                $this->service->bloquearPorDenuncia($idDenuncia);
            } else {
                $this->service->analisar($idDenuncia);
            }

            $this->redirect(URL_BASE . '/admin/denuncias?status=pendentes');
        } catch (\Exception $e) {
            $this->redirect(URL_BASE . '/admin/denuncias?erro=' . urlencode($e->getMessage()));
        }
    }
}
