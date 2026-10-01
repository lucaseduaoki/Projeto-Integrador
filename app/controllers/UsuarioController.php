<?php

namespace app\controllers;

use app\core\Controller;
use app\helpers\Validador;
use app\models\Usuario;
use app\services\UsuarioService;

class UsuarioController extends Controller
{
    private UsuarioService $service;

    public function __construct()
    {
        $this->service = new UsuarioService();
    }

    /**
     * Exibir página de cadastro
     */
    public function exibirCadastro(): void
    {
        $this->view('usuario/cadastro', [
        ]);
    }

    /**
     * Processar cadastro (já feito em AutenticacaoController)
     */
    public function cadastrar(): void
    {
        $this->redirect(URL_BASE . '/cadastro');
    }

    /**
     * Exibir perfil do usuário logado
     */
    public function exibirPerfil(): void
    {
        $this->autenticacaoRequired();
        
        $usuario = $this->usuarioLogado();
        $habilidades = $this->service->buscarHabilidades($usuario->getIdUsuario());
        
        $this->view('usuario/perfil', [
            'usuario' => $usuario,
            'habilidades' => $habilidades
        ]);
    }

    /**
     * Exibir perfil público de outro usuário (ex.: contratante de uma vaga)
     */
    public function exibirPerfilPublico(): void
    {
        $this->autenticacaoRequired();

        $idUsuario = (int)($_GET['id'] ?? 0);

        if ($idUsuario <= 0) {
            $this->redirect(URL_BASE . '/vagas');
            return;
        }

        $usuario = $this->service->buscarPorId($idUsuario);

        if (!$usuario) {
            $this->redirect(URL_BASE . '/vagas');
            return;
        }

        $habilidades = $this->service->buscarHabilidades($usuario->getIdUsuario());

        $this->view('usuario/perfil_publico', [
            'perfil' => $usuario,
            'habilidades' => $habilidades
        ]);
    }

    /**
     * Editar perfil do usuário
     */
    public function editarPerfil(): void
    {
        error_log("Dados recebidos para editar perfil: " . print_r($_POST, true));

        $this->autenticacaoRequired();

        $usuario = $this->usuarioLogado();

        // Sanitizar entrada
        $nome = htmlspecialchars(trim($_POST['nome'] ?? ''), ENT_QUOTES, 'UTF-8');
        $telefone = htmlspecialchars(trim($_POST['telefone'] ?? ''), ENT_QUOTES, 'UTF-8');
        $descricao = htmlspecialchars(trim($_POST['descricao'] ?? ''), ENT_QUOTES, 'UTF-8');
        $documento = htmlspecialchars(trim($_POST['documento'] ?? ''), ENT_QUOTES, 'UTF-8');

        // Contratante é sempre PJ e trabalhador é sempre PF: papéis não mudam pelo perfil.
        $validador = new Validador();

        // Validar
        $validador->documentoPorTipoPessoa('documento', $documento, $usuario->getTipoPessoa());
        $documento = preg_replace('/\D/', '', $documento);

        $validador->obrigatorio('nome', $nome)
            ->obrigatorio('documento', $documento)
            ->maximo('nome', $nome, 100)
            ->maximo('telefone', $telefone, 20)
            ->maximo('descricao', $descricao, 500)
            ->maximo('documento', $documento, 20);

        if ($validador->temErros()) {
            $this->view('usuario/perfil', [
                'usuario' => $usuario,
                'erros' => $validador->getErros(),
            ]);
            return;
        }

        try {
            // Atualizar dados
            $usuario->setNome($nome);
            $usuario->setTelefone($telefone ?: null);
            $usuario->setDescricao($descricao ?: null);
            $usuario->setDocumento($documento);

            // Foto de perfil (opcional): validada e gravada pelo service
            $fotoAnterior = $usuario->getFotoPerfil();
            $novaFoto = null;
            $enviouFoto = isset($_FILES['foto_perfil']) && ($_FILES['foto_perfil']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;

            if ($enviouFoto) {
                $novaFoto = $this->service->salvarFotoPerfil($_FILES['foto_perfil']);
                $usuario->setFotoPerfil($novaFoto);
            }
            error_log("Print Usuario antes de atualizar: " . print_r($usuario, true));
            $this->service->atualizarPerfil($usuario);

            // Habilidades só para prestadores de serviço; ids validados no service
            if ($usuario->isTrabalhador()) {
                $this->service->definirHabilidades(
                    $usuario,
                    array_filter((array)($_POST['habilidades'] ?? []), 'is_scalar')
                );
            }

            // A foto antiga só é apagada depois que a nova foi gravada com sucesso
            if ($novaFoto !== null) {
                $this->service->removerArquivoFoto($fotoAnterior);
            }

            // Atualizar sessão
            $_SESSION['usuario_logado'] = $usuario;

            $this->view('usuario/perfil', [
                'usuario' => $usuario,
                'habilidades' => $this->service->buscarHabilidades($usuario->getIdUsuario()),
                'sucesso' => 'Perfil atualizado com sucesso!',
            ]);
        } catch (\Exception $e) {
            // Falha depois de gravar a nova foto: não deixa arquivo órfão nem troca a foto do usuário
            if (isset($novaFoto) && $novaFoto !== null) {
                $this->service->removerArquivoFoto($novaFoto);
                $usuario->setFotoPerfil($fotoAnterior);
            }

            $this->view('usuario/perfil', [
                'usuario' => $usuario,
                'erro' => $this->mensagemAmigavel($e, 'Não foi possível salvar o perfil agora. Tente novamente em instantes.'),
            ]);
        }
    }

    public function exibirFormEditarPerfil(): void
    {
        $this->autenticacaoRequired();

        $usuario = $this->usuarioLogado();
        $habilidadesUsuario = array_map(
            fn($h) => $h->getIdHabilidade(),
            $this->service->buscarHabilidades($usuario->getIdUsuario())
        );

        $this->view('usuario/perfil_editar', [
            'usuario' => $usuario,
            'habilidades' => $this->service->listarHabilidades(),
            'habilidadesUsuario' => $habilidadesUsuario
        ]);
    }
}
