<?php

namespace app\core;

use app\models\Usuario;
use app\models\Vaga;
use app\services\AdvertenciaService;
use app\services\UsuarioService;

class Controller
{
    /**
     * Renderizar view
     */
    public function view(string $view, ?array $data = null)
    {
        // Advertências ainda não dispensadas do usuário logado, exibidas no topo (navbar)
        $usuarioSessao = $_SESSION['usuario_logado'] ?? null;
        $advertenciasPendentes = $usuarioSessao instanceof Usuario
            ? (new AdvertenciaService())->listarNaoVisualizadas($usuarioSessao->getIdUsuario())
            : [];

        if ($data) {
            // Controllers enviam 'erro' (mensagem única); as views leem $erros['geral']
            if (isset($data['erro'])) {
                $data['erros'] = ($data['erros'] ?? []) + ['geral' => $data['erro']];
            }

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

        // RN04: a conta é conferida no banco a cada requisição. Bloqueada, desativada ou removida
        // depois do login, a sessão é encerrada. O usuário da sessão também é atualizado, então
        // papéis alterados passam a valer sem novo login.
        $atual = (new UsuarioService())->buscarPorId($_SESSION['usuario_logado']->getIdUsuario());

        if ($atual === null || !$atual->isAtivo()) {
            $_SESSION = [];
            session_destroy();
            $this->redirect(URL_BASE . '/login?motivo=conta_inativa');
            exit;
        }

        $_SESSION['usuario_logado'] = $atual;
    }

    /**
     * Dono da vaga ou administrador (RN15: o admin gerencia as vagas de qualquer contratante).
     */
    protected function podeGerenciarVaga(Vaga $vaga): bool
    {
        $usuario = $this->usuarioLogado();

        return $usuario !== null
            && ($usuario->isAdmin() || $vaga->getIdContratante() === $usuario->getIdUsuario());
    }

    /**
     * Verificar se usuário é ADMIN
     */
    public function adminRequired(): void
    {
        $this->autenticacaoRequired();
        
        $usuario = $_SESSION['usuario_logado'];
        if (!$usuario->isAdmin()) {
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
        if (!$usuario->isContratante() && !$usuario->isAdmin()) {
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
        if (!$usuario->isTrabalhador() && !$usuario->isAdmin()) {
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
