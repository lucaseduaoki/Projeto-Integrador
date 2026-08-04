<?php

namespace app\services;

use app\models\Usuario;
use app\repositories\UsuarioRepository;
use Exception;

class AutenticacaoService
{
    private UsuarioRepository $usuarioRepository;

    public function __construct()
    {
        $this->usuarioRepository = new UsuarioRepository();
    }

    /**
     * Logar usuário
     */
    public function logar(string $email, string $senha): Usuario
    {
        error_log('[LOGIN][Service] Buscando usuario ativo por email=' . $email);

        // Buscar usuário ativo por email
        $usuario = $this->usuarioRepository->buscarAtivoPorEmail($email);

        if (!$usuario) {
            error_log('[LOGIN][Service] Usuario nao encontrado ou inativo');
            throw new Exception('Usuário não encontrado ou inativo.');
        }

        error_log('[LOGIN][Service] Usuario encontrado id=' . $usuario->getIdUsuario());

        // Verificar senha
        if (!password_verify($senha, $usuario->getSenha())) {
            error_log('[LOGIN][Service] Password verify falhou');
            throw new Exception('Credenciais inválidas.');
        }

        error_log('[LOGIN][Service] Password verify OK');

        // Regenerar ID da sessão (previne session fixation)
        session_regenerate_id(true);

        // Armazenar dados na sessão
        $_SESSION['usuario_logado'] = $usuario;
        $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'] ?? '';
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';

        return $usuario;
    }

    /**
     * Fazer logout
     */
    public function logout(): void
    {
        session_destroy();
    }

    /**
     * Verificar se usuário está autenticado
     */
    public static function usuarioAutenticado(): bool
    {
        return isset($_SESSION['usuario_logado']) && $_SESSION['usuario_logado'] instanceof Usuario;
    }

    /**
     * Obter usuário logado
     */
    public static function usuarioLogado(): ?Usuario
    {
        return $_SESSION['usuario_logado'] ?? null;
    }

    /**
     * Validar integridade da sessão
     */
    public static function validarIntegridade(): bool
    {
        if (!isset($_SESSION['usuario_logado'])) {
            return false;
        }

        // Validar IP
        if (($_SESSION['ip'] ?? '') !== ($_SERVER['REMOTE_ADDR'] ?? '')) {
            return false;
        }

        // Validar User-Agent
        if (($_SESSION['user_agent'] ?? '') !== ($_SERVER['HTTP_USER_AGENT'] ?? '')) {
            return false;
        }

        return true;
    }
}
