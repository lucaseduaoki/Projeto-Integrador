<?php 

namespace app\services;

use app\repositories\UsuarioRepository;

class AutenticacaoService {

    private UsuarioRepository $usuarioRepository;

    public function __construct(){
        $this->usuarioRepository = new UsuarioRepository();
    }


    public function logar(string $email, string $senha) : bool {

        $usuario = $this->usuarioRepository->getUsuarioByEmail($email);
            error_log($senha);
        if ($usuario && password_verify($senha, $usuario->getSenha())) {
            $_SESSION['usuario_logado'] = $usuario;
            return true;
            
        }
        return false;
    }

    public function logout(){
        session_destroy();
    }




}