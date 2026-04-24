<?php

require_once __DIR__ . '/../app/core/Autoload.php';
require_once __DIR__ . '/../app/config/Config.php';

use app\core\Router;

$router = new Router();

$router->get('/', 'FilmeController@listarTodos');

// Jogador Routes
$router->get('/filmes', 'FilmeController@listarTodos');        
$router->get('/filmes/criar', 'FilmeController@criar');   
$router->post('/filmes/salvar', 'FilmeController@salvar');  
$router->get('/filmes/ver', 'FilmeController@verFilme');    
$router->get('/filmes/editar', 'FilmeController@editar');   
$router->post('/filmes/atualizar', 'FilmeController@atualizar');
$router->get('/filmes/excluir', 'FilmeController@excluir'); 


$router->get('/usuarios', 'UsuarioController@index');
$router->get('/usuarios/cadastrar', 'UsuarioController@cadastrar');
$router->post('/usuarios/salvar', 'UsuarioController@salvar');
$router->get('/usuarios/editar', 'UsuarioController@editar');
$router->post('/usuarios/atualizar', 'UsuarioController@atualizar');
$router->get('/usuarios/excluir', 'UsuarioController@excluir');

//Autenticacao
$router->get('/login', 'AutenticacaoController@login');
$router->post('/logar', 'AutenticacaoController@logar');



$router->get('/teste', 'FilmeController@redirecionarTeste');


$router->run();
