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
        error_log("Print Usuario: " . print_r($usuario, true));
        $habilidades = $this->service->buscarHabilidades($usuario->getIdUsuario());
        
        $this->view('usuario/perfil', [
            'usuario' => $usuario,
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
        $localizacao = htmlspecialchars(trim($_POST['localizacao'] ?? ''), ENT_QUOTES, 'UTF-8');
        
        // Validar
        $validador = new Validador();
        $validador->obrigatorio('nome', $nome)
            ->obrigatorio('documento', $documento)
            ->maximo('nome', $nome, 100)
            ->maximo('telefone', $telefone, 20)
            ->maximo('descricao', $descricao, 500)
            ->maximo('documento', $documento, 20)
            ->maximo('localizacao', $localizacao, 100);

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
            error_log("Print Usuario antes de atualizar: " . print_r($usuario, true));
            $this->service->atualizarPerfil($usuario);
            
            // Atualizar sessão
            $_SESSION['usuario_logado'] = $usuario;

            $this->view('usuario/perfil', [
                'usuario' => $usuario,
                'sucesso' => 'Perfil atualizado com sucesso!',
            ]);
        } catch (\Exception $e) {
            $this->view('usuario/perfil', [
                'usuario' => $usuario,
                'erro' => $e->getMessage(),
            ]);
        }
    }

    public function exibirFormEditarPerfil(): void
    {
        $this->autenticacaoRequired();

        $usuario = $this->usuarioLogado();
        $habilidades = $this->service->buscarHabilidades($usuario->getIdUsuario());
        error_log("Print Usuario: " . print_r($usuario, true));
        $this->view('usuario/perfil_editar', [
            'usuario' => $usuario,
            'habilidades' => $habilidades
        ]);
    }
}
