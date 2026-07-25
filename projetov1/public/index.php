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
$router->get('/anuncios',               'AnuncioController@listar');
$router->get('/anuncios/buscar',        'AnuncioController@buscar');
$router->get('/anuncios/criar',         'AnuncioController@exibirFormCriar');
$router->post('/anuncios/criar/submit', 'AnuncioController@criar');
$router->get('/anuncios/visualizar',    'AnuncioController@visualizar');
$router->get('/anuncios/editar',        'AnuncioController@exibirFormEditar');
$router->post('/anuncios/editar/submit','AnuncioController@editar');
$router->post('/anuncios/excluir',      'AnuncioController@excluir');

// ============================================================================
// CANDIDATURAS
// ============================================================================
$router->post('/candidatura/criar',      'CandidaturaController@candidatar');
$router->get('/candidatura/candidatos',  'CandidaturaController@listarCandidatos');
$router->post('/candidatura/selecionar', 'CandidaturaController@selecionar');
$router->post('/candidatura/rejeitar',   'CandidaturaController@rejeitar');
$router->post('/candidatura/confirmar',  'CandidaturaController@confirmar');
$router->get('/candidatura/historico',   'CandidaturaController@historico');

// ============================================================================
// AVALIAÇÕES
// ============================================================================
$router->get('/avaliacao/criar',         'AvaliacaoController@exibirFormAvaliar');
$router->post('/avaliacao/criar/submit', 'AvaliacaoController@avaliar');

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

