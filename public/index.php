<?php

require_once __DIR__ . '/../app/config/Config.php';

$senha_banco = '123456';

/*------------------*/
// Sistema de rotas v0.1
/*------------------*/


$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

switch ($uri) {
    case '/teste':
        print 'teste!';
        break;

    case '/jogadores':
        require_once __DIR__ .'/../app/views/players/players_list.php';
        break;
    
    
    default:
        http_response_code(404);
        print 'Página não encontrada';
        break;
}

?>