<?php

namespace app\controllers;

use app\core\Controller;

class ErroController extends Controller
{
    public function acessoNegado(): void
    {
        http_response_code(403);
        $this->view('errors/403');
    }
}
