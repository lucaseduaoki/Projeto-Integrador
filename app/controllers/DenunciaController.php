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
            if ($vaga === null) {
                $this->flashErro('Anúncio não encontrado.');
                $this->redirect(URL_BASE . '/vagas');
                return;
            }

            if ($vaga->getIdContratante() === $usuario->getIdUsuario()) {
                $this->flashErro('Você não pode denunciar o seu próprio anúncio.');
                $this->redirect(URL_BASE . '/vagas');
                return;
            }

            $this->view('denuncia/denuncia_form', [
                'vaga' => $vaga,
                'motivos' => Denuncia::motivosPara(Denuncia::TIPO_ANUNCIO),
            ]);
            return;
        }

        $idDenunciado = (int)($_GET['id'] ?? 0);
        $denunciado = $idDenunciado > 0 ? $this->usuarioService->buscarPorId($idDenunciado) : null;

        if ($denunciado === null) {
            $this->flashErro('Usuário não encontrado.');
            $this->redirect(URL_BASE . '/vagas');
            return;
        }

        if ($idDenunciado === $usuario->getIdUsuario()) {
            $this->flashErro('Você não pode denunciar a si mesmo.');
            $this->redirect(URL_BASE . '/vagas');
            return;
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

        // RN 12: Validar motivo e descrição obrigatórios
        $validador = new Validador();
        $validador->obrigatorio('motivo', $motivo, 'Selecione o motivo da denúncia.')
                  ->emLista('motivo', $motivo, array_keys($motivos), 'Motivo inválido: escolha uma das opções.')
                  ->obrigatorio('descricao', $descricao, 'Descreva os detalhes da denúncia.')
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
            $this->view('denuncia/denuncia_form', $dadosForm + ['erro' => $this->mensagemAmigavel($e, 'Não foi possível registrar a denúncia agora. Tente novamente em instantes.')]);
        }
    }

    /**
     * Formulário de registro de não comparecimento (RN16): ?id=<candidatura>.
     */
    public function exibirFormNaoComparecimento(): void
    {
        $this->contratanteRequired();

        $idCandidatura = (int)($_GET['id'] ?? 0);

        try {
            $candidatura = $this->service->validarNaoComparecimento($idCandidatura, $this->usuarioLogado()->getIdUsuario());
        } catch (\Exception $e) {
            $this->flashErro($this->mensagemAmigavel($e, 'Não foi possível abrir o registro de não comparecimento agora.'));
            $this->redirect(URL_BASE . '/vagas/minhas');
            return;
        }

        $this->view('denuncia/nao_comparecimento', [
            'candidatura' => $candidatura,
            'trabalhador' => $this->usuarioService->buscarPorId($candidatura->getIdTrabalhador()),
            'vaga' => $this->vagaService->buscarPorId($candidatura->getIdVaga()),
        ]);
    }

    /**
     * Registrar não comparecimento (RN 16)
     */
    public function registrarNaoComparecimento(): void
    {
        $this->contratanteRequired();

        $usuario = $this->usuarioLogado();
        $idInteresse = (int)($_POST['id_interesse'] ?? 0);
        $descricao = trim($_POST['descricao'] ?? '');

        // RN 16: Descrição obrigatória
        $validador = new Validador();
        $validador->obrigatorio('descricao', $descricao, 'Descreva o motivo do não comparecimento.')
                  ->tamanhoMax('descricao', $descricao, 1000, 'A descrição deve ter no máximo 1000 caracteres.');

        try {
            $candidatura = $this->service->validarNaoComparecimento($idInteresse, $usuario->getIdUsuario());
        } catch (\Exception $e) {
            $this->flashErro($this->mensagemAmigavel($e, 'Não foi possível registrar o não comparecimento agora.'));
            $this->redirect(URL_BASE . '/vagas/minhas');
            return;
        }

        $dadosForm = [
            'candidatura' => $candidatura,
            'trabalhador' => $this->usuarioService->buscarPorId($candidatura->getIdTrabalhador()),
            'vaga' => $this->vagaService->buscarPorId($candidatura->getIdVaga()),
        ];

        if ($validador->temErros()) {
            $this->view('denuncia/nao_comparecimento', $dadosForm + ['erros' => $validador->getErros()]);
            return;
        }

        try {
            $this->service->registrarNaoComparecimento($usuario->getIdUsuario(), $idInteresse, $descricao ?: null);

            $this->view('denuncia/sucesso', [
                'mensagem' => 'Não comparecimento registrado. A moderação vai analisar o caso.'
            ]);
        } catch (\Exception $e) {
            $this->view('denuncia/nao_comparecimento', $dadosForm + ['erro' => $this->mensagemAmigavel($e, 'Não foi possível registrar o não comparecimento agora. Tente novamente em instantes.')]);
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

        // Nomes de denunciante/denunciado e título do anúncio, buscados em lote (evita N+1)
        $idsUsuarios = [];
        $idsVagas = [];
        foreach ($denuncias as $denuncia) {
            $idsUsuarios[] = $denuncia->getIdDenunciante();
            if ($denuncia->getIdUsuarioDenunciado() !== null) {
                $idsUsuarios[] = $denuncia->getIdUsuarioDenunciado();
            }
            if ($denuncia->getIdVagaDenunciada() !== null) {
                $idsVagas[] = $denuncia->getIdVagaDenunciada();
            }
        }

        $this->view('denuncia/listar', [
            'denuncias' => $denuncias,
            'usuarios' => $this->usuarioService->buscarPorIds($idsUsuarios),
            'vagas' => $this->vagaService->buscarPorIds($idsVagas),
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
        $idAdmin = $this->usuarioLogado()->getIdUsuario();

        if ($idDenuncia <= 0 || !in_array($acao, ['bloquear', 'arquivar', 'remover'], true)) {
            $this->redirect(URL_BASE . '/admin/denuncias');
        }

        $mensagens = [
            'bloquear' => 'Conta bloqueada com sucesso!',
            'remover' => 'Anúncio removido com sucesso!',
            'arquivar' => 'Denúncia arquivada com sucesso!',
        ];

        try {
            if ($acao === 'bloquear') {
                $this->service->bloquearPorDenuncia($idDenuncia, $idAdmin);
            } elseif ($acao === 'remover') {
                $this->service->moderarAnuncio($idDenuncia, $idAdmin);
            } else {
                $this->service->arquivar($idDenuncia, $idAdmin);
            }

            $this->flashSucesso($mensagens[$acao]);
            $this->redirect(URL_BASE . '/admin/denuncias?status=pendentes');
        } catch (\Exception $e) {
            $this->redirect(URL_BASE . '/admin/denuncias?erro=' . urlencode($this->mensagemAmigavel($e, 'Não foi possível concluir a moderação agora. Tente novamente em instantes.')));
        }
    }
}
