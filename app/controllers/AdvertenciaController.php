<?php

namespace app\controllers;

use app\core\Controller;
use app\services\AdvertenciaService;

class AdvertenciaController extends Controller
{
    /**
     * Dispensar (marcar como lida) uma advertência do próprio usuário.
     */
    public function dispensar(): void
    {
        $this->autenticacaoRequired();

        $idAdvertencia = (int)($_POST['id'] ?? 0);

        if ($idAdvertencia > 0) {
            (new AdvertenciaService())->dispensar($idAdvertencia, $this->usuarioLogado()->getIdUsuario());
        }

        // Volta para a página de origem, só se for do próprio sistema
        $origem = $_SERVER['HTTP_REFERER'] ?? '';
        $this->redirect(str_starts_with($origem, URL_BASE . '/') ? $origem : URL_BASE . '/vagas');
    }
}
