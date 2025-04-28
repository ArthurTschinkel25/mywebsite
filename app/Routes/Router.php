<?php
namespace app\Routes;

class Router
{
    private $routes = [];

    public function addRoute($path, $handler, $method = 'handleRequest')
    {
        $this->routes[$path] = [
            'handler' => $handler,
            'method' => $method
        ];
    }

    public function dispatch()
    {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $path = rtrim($path, '/');
        $path = $path === '' ? '/' : $path;

        if (array_key_exists($path, $this->routes)) {
            $config = $this->routes[$path];

            if (is_callable($config['handler'])) {
                return call_user_func($config['handler']);
            }

            if (class_exists($config['handler'])) {
                $controller = new $config['handler']();
                $method = $config['method'];

                if (method_exists($controller, $method)) {
                    return $controller->$method();
                }
            }
        }

        http_response_code(404);
        echo "Página não encontrada";
    }
}