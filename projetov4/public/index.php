<?php

require_once __DIR__ . '/../app/core/Autoload.php';
require_once __DIR__ . '/../app/config/Config.php';

use app\core\Router;

$router = new Router();

$router->get('/', 'AutenticacaoController@exibirLogin');

// ============================================================================
// AUTENTICAÇÃO
// ============================================================================
$router->get('/login',          'AutenticacaoController@exibirLogin');
$router->post('/login/submit',  'AutenticacaoController@logar');
$router->get('/logout',         'AutenticacaoController@logout');
$router->get('/cadastro',       'AutenticacaoController@exibirCadastro');
$router->post('/cadastro/submit','AutenticacaoController@cadastrar');

// ============================================================================
// PERFIL DE USUÁRIO
// ============================================================================
$router->get('/perfil',         'UsuarioController@exibirPerfil');
$router->post('/perfil/editar', 'UsuarioController@editarPerfil');

// ============================================================================
// ANÚNCIOS
// ============================================================================
$router->get('/vagas',               'VagaController@listar');
$router->get('/vagas/buscar',        'VagaController@buscar');
$router->get('/vagas/criar',         'VagaController@exibirFormCriar');
$router->post('/vagas/criar/submit', 'VagaController@criar');
$router->get('/vagas/visualizar',    'VagaController@visualizar');
$router->get('/vagas/editar',        'VagaController@exibirFormEditar');
$router->post('/vagas/editar/submit','VagaController@editar');
$router->post('/vagas/excluir',      'VagaController@excluir');

// ============================================================================
// INTERESSES
// ============================================================================
$router->post('/interesse/demonstrar',      'InteresseController@demonstrar');
$router->get('/interesse/interessados',     'InteresseController@listarInteressados');
$router->get('/interesse/historico',     'InteresseController@historico');
$router->get('/interesse/historico/visualizar',     'InteresseController@visualizarHistorico');



// ============================================================================
// DENÚNCIAS
// ============================================================================
$router->get('/denuncia/criar',          'DenunciaController@exibirFormDenunciar');
$router->post('/denuncia/criar/submit',  'DenunciaController@denunciar');

// ============================================================================
// ADMIN
// ============================================================================
$router->get('/admin/denuncias',         'DenunciaController@listar');
$router->post('/admin/denuncias/moderar','DenunciaController@moderar');

// ============================================================================
// EXECUTAR
// ============================================================================
$router->run();

