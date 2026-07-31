<?php

namespace app\controllers;

use app\core\Controller;
use app\helpers\Validador;
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
        $motivo = htmlspecialchars(trim($_POST['motivo'] ?? ''), ENT_QUOTES, 'UTF-8');
        $descricao = htmlspecialchars(trim($_POST['descricao'] ?? ''), ENT_QUOTES, 'UTF-8');
        $idVaga = (int)($_POST['id_vaga'] ?? 0);

        // Buscar o usuário denunciado logo no início, já que a view precisa dele
        // nos dois cenários abaixo (erro de validação e exceção)
        $denunciado = $this->usuarioService->buscarPorId($idDenunciado);

        // Validar
        $validador = new Validador();
        $validador->obrigatorio('motivo', $motivo)
                ->minimo('motivo', $motivo, 5);

        if ($validador->temErros()) {
            $this->view('denuncia/denuncia_form', [
                'denunciado' => $denunciado,
                'idDenunciado' => $idDenunciado,
                'erros' => $validador->getErros(),
            ]);
            return;
        }

        try {
            error_log("Tentando criar denúncia para o usuário denunciado ID: $idDenunciado"); // Log before creating the complaint
            $this->service->criar(
                $usuario->getIdUsuario(),
                $idDenunciado,
                $motivo,
                $descricao ?: null,
                $idVaga > 0 ? $idVaga : null
            );

            error_log("Denúncia criada com sucesso para o usuário denunciado ID: $idDenunciado"); // Log success message
            $this->view('denuncia/sucesso', [
                'mensagem' => 'Denúncia registrada. Obrigado por manter a plataforma segura!'
            ]);
        } catch (\Exception $e) {
            $this->view('denuncia/denuncia_form', [
                'denunciado' => $denunciado,
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
