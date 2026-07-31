<?php

namespace app\core;

use Exception;

class Router
{
    private array $routes = [];

    public function get($route, $action){

        $this->routes[] = [
            'method' => 'get',
            'route' => $route,
            'action' => $action
        ];
    }

    public function post($route, $action){

        $this->routes[] = [
            'method' => 'post',
            'route' => $route,
            'action' => $action
        ];
    }


   public function run()
    {
        $uri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Remove a pasta do projeto (caso esteja rodando em subpasta no Apache)
        $scriptDir = str_replace('/public', '', dirname($_SERVER['SCRIPT_NAME']));

        if ($scriptDir !== '/') {
            $uri = str_replace($scriptDir, '', $uri);
        }

        $uri = str_replace('/public', '', $uri);

        // Garante que a URI não fique vazia e comece com "/"
        if (empty($uri)) {
            $uri = '/';
        }
        if ($uri[0] !== '/') {
            $uri = '/' . $uri;
        }

        $method = strtolower($_SERVER['REQUEST_METHOD']);

        foreach ($this->routes as $route) {

            if ($route['route'] == $uri && $route['method'] == $method) {

                return $this->dispatch($route);
            }
        }

        http_response_code(404);
        exit('Rota não encontrada');
    }
    
    public function dispatch($route){

        list($controller, $method) = explode('@', $route['action']);

        $controllerClass = "app\\controllers\\$controller";

        if (!class_exists($controllerClass)) {
            print "Controller $controller não encontrado";
            die;
        }

        if (!method_exists($controllerClass, $method)) {
            print "Método $method não encontrado em $controllerClass";
            die;
        }
        
        $controller = new $controllerClass;
        $controller->$method();

    }

    public function getAllRoutes(){
        return $this->routes;
    }

}
