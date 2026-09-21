<?php

require_once __DIR__ . '/../app/core/Autoload.php';
require_once __DIR__ . '/../app/config/Config.php';

use app\core\Router;

// Última barreira: qualquer exceção sem tratamento vira uma página amigável. O detalhe (SQL, host,
// caminho do arquivo) vai só para o log, nunca para a tela.
set_exception_handler(function (\Throwable $e) {
    error_log('[ERRO NAO TRATADO] ' . get_class($e) . ': ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());

    if (!headers_sent()) {
        http_response_code(500);
    }

    require __DIR__ . '/../app/views/errors/500.php';
});

$router = new Router();

$router->get('/', 'AutenticacaoController@exibirLogin');

// ============================================================================
// AUTENTICAÇÃO
// ============================================================================
$router->get('/403',            'ErroController@acessoNegado');
$router->get('/login',          'AutenticacaoController@exibirLogin');
$router->post('/login/submit',  'AutenticacaoController@logar');
$router->get('/logout',         'AutenticacaoController@logout');
$router->get('/cadastro',       'AutenticacaoController@exibirCadastro');
$router->post('/cadastro/submit','AutenticacaoController@cadastrar');

$router->post('/advertencias/dispensar', 'AdvertenciaController@dispensar');

// ============================================================================
// PERFIL DE USUÁRIO
// ============================================================================
$router->get('/perfil',         'UsuarioController@exibirPerfil');
$router->get('/perfil/editar',    'UsuarioController@exibirFormEditarPerfil');
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
$router->get('/vagas/minhas',        'VagaController@minhas');

// ============================================================================
// INTERESSES
// ============================================================================
$router->post('/interesse/demonstrar',      'InteresseController@demonstrar');
$router->get('/interesse/candidato',        'InteresseController@visualizarCandidato');
$router->get('/interesse/interessados',     'InteresseController@listarInteressados');
$router->get('/interesse/historico',     'InteresseController@historico');
$router->get('/interesse/historico/visualizar',     'InteresseController@visualizarHistorico');
$router->post('/interesse/aceitar',      'InteresseController@aceitar');
$router->get('/interesse/aceitos',      'InteresseController@listarAceitos');



// ============================================================================
// DENÚNCIAS
// ============================================================================
$router->get('/denuncia/criar',          'DenunciaController@exibirFormDenunciar');
$router->post('/denuncia/criar/submit',  'DenunciaController@denunciar');
$router->get('/denuncia/nao-comparecimento',         'DenunciaController@exibirFormNaoComparecimento');
$router->post('/denuncia/nao-comparecimento/submit', 'DenunciaController@registrarNaoComparecimento');

// ============================================================================
// ADMIN
// ============================================================================
$router->get('/admin/denuncias',         'DenunciaController@listar');
$router->post('/admin/denuncias/moderar','DenunciaController@moderar');

// ============================================================================
// EXECUTAR
// ============================================================================
$router->run();

