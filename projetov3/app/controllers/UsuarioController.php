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
     * Editar perfil do usuário
     */
    public function editarPerfil(): void
    {
        $this->autenticacaoRequired();

        $usuario = $this->usuarioLogado();

        // Sanitizar entrada
        $nome = htmlspecialchars(trim($_POST['nome'] ?? ''), ENT_QUOTES, 'UTF-8');
        $telefone = htmlspecialchars(trim($_POST['telefone'] ?? ''), ENT_QUOTES, 'UTF-8');
        $descricao = htmlspecialchars(trim($_POST['descricao'] ?? ''), ENT_QUOTES, 'UTF-8');

        // Validar
        $validador = new Validador();
        $validador->obrigatorio('nome', $nome)
                  ->minimo('nome', $nome, 3)
                  ->maximo('nome', $nome, 100);

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
}
