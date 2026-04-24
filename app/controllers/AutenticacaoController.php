<?php

namespace app\controllers;

use app\core\Controller;
use app\services\AutenticacaoService;

class AutenticacaoController extends Controller
{

    private AutenticacaoService $autenticacaoService;

    public function __construct()
    {
        $this->autenticacaoService = new AutenticacaoService();
    }

    public function login()
    {

        $this->view('autenticacao/login');
    }

    public function logar()
    {

        $email = $_POST['email'];
        $senha = $_POST['senha'];

        $resultado = $this->autenticacaoService->logar($email, $senha);
        if ($resultado) {
            $this->redirect(URL_BASE . '/filmes');
        } else {

            $dados['erros'] = "Uma linda mensagem de erro";

            $this->view('autenticacao/login', $dados);
        }
    }
}
