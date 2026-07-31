<?php

namespace app\core;

use app\models\Usuario;

class Controller
{
    /**
     * Renderizar view
     */
    public function view(string $view, ?array $data = null)
    {
        if ($data) {
            extract($data);
        }

        $path = __DIR__ . "/../views/$view.php";

        if (file_exists($path)) {
            require_once $path;
        } else {
            print 'A view solicitada não foi encontrada: ' . $view;
        }
    }

    /**
     * Redirecionar para URL
     */
    public function redirect(string $url)
    {
        header('location: ' . $url);
        exit();
    }

    /**
     * Verificar se usuário está autenticado
     */
    public function autenticacaoRequired(): void
    {
        if (!isset($_SESSION['usuario_logado']) || !($_SESSION['usuario_logado'] instanceof Usuario)) {
            $this->redirect(URL_BASE . '/login');
            exit;
        }

        // Validar integridade da sessão (IP e User-Agent)
        if (($_SESSION['ip'] ?? '') !== ($_SERVER['REMOTE_ADDR'] ?? '') ||
            ($_SESSION['user_agent'] ?? '') !== ($_SERVER['HTTP_USER_AGENT'] ?? '')) {
            session_destroy();
            $this->redirect(URL_BASE . '/login');
            exit;
        }
    }

    /**
     * Verificar se usuário é ADMIN
     */
    public function adminRequired(): void
    {
        $this->autenticacaoRequired();
        
        $usuario = $_SESSION['usuario_logado'];
        if ($usuario->getTipoUsuario() !== 'ADMIN') {
            $this->redirect(URL_BASE . '/403');
            exit;
        }
    }

    /**
     * Verificar se usuário é CONTRATANTE
     */
    public function contratanteRequired(): void
    {
        $this->autenticacaoRequired();
        
        $usuario = $_SESSION['usuario_logado'];
        if ($usuario->getTipoUsuario() !== 'CONTRATANTE' && $usuario->getTipoUsuario() !== 'ADMIN') {
            $this->redirect(URL_BASE . '/403');
            exit;
        }
    }

    /**
     * Verificar se usuário é TRABALHADOR
     */
    public function trabalhadorRequired(): void
    {
        $this->autenticacaoRequired();
        
        $usuario = $_SESSION['usuario_logado'];
        if ($usuario->getTipoUsuario() !== 'TRABALHADOR' && $usuario->getTipoUsuario() !== 'ADMIN') {
            $this->redirect(URL_BASE . '/403');
            exit;
        }
    }

    /**
     * Obter usuário logado
     */
    protected function usuarioLogado(): ?Usuario
    {
        return $_SESSION['usuario_logado'] ?? null;
    }

    /**
     * Sanitizar entrada POST
     */
    protected function sanitizarPost(string $chave, string $tipo = 'string'): mixed
    {
        $valor = $_POST[$chave] ?? null;

        if ($valor === null) {
            return null;
        }

        return match ($tipo) {
            'email' => filter_var($valor, FILTER_SANITIZE_EMAIL),
            'int' => (int)$valor,
            'float' => (float)$valor,
            'url' => filter_var($valor, FILTER_SANITIZE_URL),
            default => htmlspecialchars(trim($valor), ENT_QUOTES, 'UTF-8'),
        };
    }

    /**
     * Sanitizar GET
     */
    protected function sanitizarGet(string $chave, string $tipo = 'string'): mixed
    {
        $valor = $_GET[$chave] ?? null;

        if ($valor === null) {
            return null;
        }

        return match ($tipo) {
            'email' => filter_var($valor, FILTER_SANITIZE_EMAIL),
            'int' => (int)$valor,
            'float' => (float)$valor,
            'url' => filter_var($valor, FILTER_SANITIZE_URL),
            default => htmlspecialchars(trim($valor), ENT_QUOTES, 'UTF-8'),
        };
    }

    /**
     * Responder com JSON
     */
    protected function json($data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }
}
