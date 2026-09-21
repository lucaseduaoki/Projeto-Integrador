<?php

namespace app\controllers;

use app\core\Controller;
use app\helpers\Validador;
use app\models\Denuncia;
use app\services\DenunciaService;
use app\services\UsuarioService;

class DenunciaController extends Controller
{
    private DenunciaService $service;
    private UsuarioService $usuarioService;

    public function __construct()
    {
        $this->service = new DenunciaService();
        $this->usuarioService = new UsuarioService();
    }

    /**
     * Exibir formulário de denúncia
     */
    public function exibirFormDenunciar(): void
    {
        $this->autenticacaoRequired();

        $idDenunciado = (int)($_GET['id'] ?? 0);
        if ($idDenunciado <= 0) {
            $this->redirect(URL_BASE . '/vagas');
        }
        $denunciadoObject = $this->usuarioService->buscarPorId($idDenunciado);
        error_log("Denunciado object: " . print_r($denunciadoObject, true)); // Log the value of $denunciadoObject
        if (!$denunciadoObject->getIdUsuario()) {
            $this->redirect(URL_BASE . '/vagas');
        }
        error_log("🙃​🙃​🙃​🙃​🙃​");
        error_log("Denunciado object after check: " . print_r($denunciadoObject, true)); // Log the value of $denunciadoObject after the check
        error_log("🙃​🙃​🙃​🙃​🙃​");
        $this->view('denuncia/denuncia_form', [
            'denunciado' => $denunciadoObject,
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
        $idDenunciado = (int)($_POST['id_usuario_denunciado'] ?? 0);
        $motivo = trim($_POST['motivo'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $idVaga = (int)($_POST['id_vaga'] ?? 0);

        // Denúncia de anúncio (com id_vaga) ou de usuário; cada tipo tem seus motivos (RN13)
        $tipo = $idVaga > 0 ? Denuncia::TIPO_ANUNCIO : Denuncia::TIPO_USUARIO;
        $motivos = Denuncia::motivosPara($tipo);

        // O alvo precisa existir e não pode ser a própria pessoa
        $denunciado = $this->usuarioService->buscarPorId($idDenunciado);
        if ($denunciado === null || $idDenunciado === $usuario->getIdUsuario()) {
            $this->redirect(URL_BASE . '/vagas');
        }

        // Validar
        $validador = new Validador();
        $validador->obrigatorio('motivo', $motivo, 'Selecione o motivo da denúncia.')
                  ->emLista('motivo', $motivo, array_keys($motivos), 'Motivo inválido: escolha uma das opções.')
                  ->tamanhoMax('descricao', $descricao, 1000, 'A descrição deve ter no máximo 1000 caracteres.');

        if ($validador->temErros()) {
            $this->view('denuncia/denuncia_form', [
                'denunciado' => $denunciado,
                'motivos' => $motivos,
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
                $idVaga > 0 ? $idVaga : null
            );

            $this->view('denuncia/sucesso', [
                'mensagem' => 'Denúncia registrada. Obrigado por manter a plataforma segura!'
            ]);
        } catch (\Exception $e) {
            $this->view('denuncia/denuncia_form', [
                'denunciado' => $denunciado,
                'motivos' => $motivos,
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
