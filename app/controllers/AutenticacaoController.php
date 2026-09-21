<?php

namespace app\controllers;

use app\core\Controller;
use app\services\AutenticacaoService;
use app\services\UsuarioService;
use app\helpers\Validador;
use app\models\Usuario;

class AutenticacaoController extends Controller
{
    private ?AutenticacaoService $autenticacaoService = null;
    private ?UsuarioService $usuarioService = null;

    public function __construct()
    {
    }

    private function autenticacaoService(): AutenticacaoService
    {
        if ($this->autenticacaoService === null) {
            $this->autenticacaoService = new AutenticacaoService();
        }

        return $this->autenticacaoService;
    }

    private function usuarioService(): UsuarioService
    {
        if ($this->usuarioService === null) {
            $this->usuarioService = new UsuarioService();
        }

        return $this->usuarioService;
    }

    /**
     * Exibir página de login
     */
    public function exibirLogin(): void
    {
        $this->view('autenticacao/login', [
        ]);
    }

    /**
     * Processar login
     */
    public function logar(): void
    {
        error_log('[LOGIN] Iniciando processamento');
        error_log('[LOGIN] Method=' . ($_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN') . ' URI=' . ($_SERVER['REQUEST_URI'] ?? 'UNKNOWN'));


        // Sanitizar entrada
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $senha = $_POST['senha'] ?? '';

        error_log('[LOGIN] Email recebido=' . ($email ?? 'NULL'));
        error_log('[LOGIN] Tamanho da senha=' . strlen($senha));

        // Validar
        $validador = new Validador();
        $validador->obrigatorio('email', $email)
                  ->email('email', $email)
                  ->obrigatorio('senha', $senha)
                  ->minimo('senha', $senha, 6);

        if ($validador->temErros()) {
            error_log('[LOGIN] Erros de validação=' . json_encode($validador->getErros(), JSON_UNESCAPED_UNICODE));
            $this->view('autenticacao/login', [
                'erros' => $validador->getErros(),
            ]);
            return;
        }

        try {
            error_log('[LOGIN] Chamando AutenticacaoService::logar');
            $usuario = $this->autenticacaoService()->logar($email, $senha);
            error_log('[LOGIN] Login realizado com sucesso para usuario=' . $usuario->getEmail());
            
            // Redirecionar para dashboard ou página inicial
            $this->redirect(URL_BASE . '/vagas');
        } catch (\Exception $e) {
            error_log('[LOGIN] Falha no login: ' . $e->getMessage());
            $this->view('autenticacao/login', [
                'erros' => ['geral' => $e->getMessage()],
            ]);
        }
    }

    /**
     * Exibir página de cadastro
     */
    public function exibirCadastro(): void
    {
        $this->view('autenticacao/cadastro', [
        ]);
    }

    /**
     * Processar cadastro
     */
    public function cadastrar(): void
    {

        // Sanitizar entrada
        $nome = htmlspecialchars(trim($_POST['nome'] ?? ''), ENT_QUOTES, 'UTF-8');
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $senha = $_POST['senha'] ?? '';
        $confirmaSenha = $_POST['confirma_senha'] ?? '';
        $tipoUsuario = htmlspecialchars(trim($_POST['tipo_usuario'] ?? ''), ENT_QUOTES, 'UTF-8');
        $tipoPessoa = trim($_POST['tipo_pessoa'] ?? '');
        $documento = htmlspecialchars(trim($_POST['documento'] ?? ''), ENT_QUOTES, 'UTF-8');
        $telefone = htmlspecialchars(trim($_POST['telefone'] ?? ''), ENT_QUOTES, 'UTF-8');

        // Validar
        $validador = new Validador();
        $validador->obrigatorio('nome', $nome)
                  ->minimo('nome', $nome, 3)
                  ->obrigatorio('email', $email)
                  ->email('email', $email)
                  ->obrigatorio('senha', $senha)
                  ->minimo('senha', $senha, 8)
                  ->obrigatorio('confirma_senha', $confirmaSenha)
                  ->obrigatorio('tipo_usuario', $tipoUsuario)
                  ->emLista('tipo_usuario', $tipoUsuario, ['TRABALHADOR', 'CONTRATANTE'])
                  ->obrigatorio('tipo_pessoa', $tipoPessoa, 'Informe se você é pessoa física ou empresa.')
                  ->emLista('tipo_pessoa', $tipoPessoa, ['PF', 'PJ'], 'Tipo de pessoa inválido.');

        // Documento coerente com o tipo de pessoa (CPF para PF, CNPJ para PJ)
        $validador->documentoPorTipoPessoa('documento', $documento, $tipoPessoa);
        $documento = preg_replace('/\D/', '', $documento);

        // Verificar se senhas coincidem
        if ($senha !== $confirmaSenha) {
            $validador->obrigatorio('confirma_senha', 'As senhas não coincidem.');
        }

        if ($validador->temErros()) {
            $this->view('autenticacao/cadastro', [
                'erros' => $validador->getErros(),
                'nome' => $nome,
                'email' => $email,
                'tipo_usuario' => $tipoUsuario,
                'tipo_pessoa' => $tipoPessoa
            ]);
            return;
        }

        try {
            // Registrar novo usuário
            $usuario = $this->usuarioService()->registrar(
                $nome,
                $email,
                $senha,
                $tipoUsuario,
                $telefone ?: null,
                $documento ?: null,
                $tipoPessoa
            );

            // Logar automaticamente após cadastro
            $this->autenticacaoService()->logar($email, $senha);

            // Redirecionar para completar perfil
            $this->redirect(URL_BASE . '/perfil');
        } catch (\Exception $e) {
            $this->view('autenticacao/cadastro', [
                'erros' => ['geral' => $e->getMessage()],
                'nome' => $nome,
                'email' => $email,
                'tipo_usuario' => $tipoUsuario,
                'tipo_pessoa' => $tipoPessoa
            ]);
        }
    }

    /**
     * Fazer logout
     */
    public function logout(): void
    {
        $this->autenticacaoService()?->logout();
        $this->redirect(URL_BASE . '/login');
    }
}
