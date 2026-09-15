<?php

namespace App\Core;

use App\Middleware\MiddlewareInterface;

class Router
{
    private array $routes = [];

    public function get(string $path, array|callable $handler, array $middlewares = []): void
    {
        $this->addRoute('GET', $path, $handler, $middlewares);
    }

    public function post(string $path, array|callable $handler, array $middlewares = []): void
    {
        $this->addRoute('POST', $path, $handler, $middlewares);
    }

    private function addRoute(string $method, string $path, array|callable $handler, array $middlewares = []): void
    {
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = '#^' . rtrim($pattern, '/') . '$#';
        if ($path === '/') {
            $pattern = '#^/$#';
        }

        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'pattern' => $pattern,
            'handler' => $handler,
            'middlewares' => $middlewares,
        ];
    }

    public function dispatch(Request $request): void
    {
        $method = $request->getMethod();
        if ($method === 'HEAD') {
            $method = 'GET';
        }
        $path = $request->getPath();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $path, $matches)) {
                // Extract named parameter matches
                $params = [];
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $params[$key] = $value;
                    }
                }

                // Execute Middlewares
                foreach ($route['middlewares'] as $middlewareClass) {
                    if (is_string($middlewareClass) && class_exists($middlewareClass)) {
                        /** @var MiddlewareInterface $middleware */
                        $middleware = new $middlewareClass();
                        if (!$middleware->handle($request)) {
                            return; // Middleware aborted / redirected
                        }
                    } elseif (is_callable($middlewareClass)) {
                        if ($middlewareClass($request) === false) {
                            return;
                        }
                    }
                }

                // Call Handler
                $handler = $route['handler'];
                if (is_array($handler)) {
                    [$class, $action] = $handler;
                    $controller = new $class();
                    $controller->$action($request, $params);
                    return;
                }

                if (is_callable($handler)) {
                    $handler($request, $params);
                    return;
                }
            }
        }

        // 404 Not Found
        http_response_code(404);
        View::render('public/404', [
            'title' => 'Page Not Found - ' . config('app.name'),
        ], 'main');
    }
}
