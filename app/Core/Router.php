<?php

namespace App\Core;

class Router
{
    private $container;
    private $routes = [];
    private $basePath = '';
    private $groupStack = [];

    public function __construct(Container $container = null)
    {
        $this->container = $container ?? Container::getInstance();
    }

    public function setBasePath($path)
    {
        $this->basePath = $path;
        return $this;
    }

    public function get($path, $handler, array $middleware = [])
    {
        return $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post($path, $handler, array $middleware = [])
    {
        return $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function put($path, $handler, array $middleware = [])
    {
        return $this->addRoute('PUT', $path, $handler, $middleware);
    }

    public function patch($path, $handler, array $middleware = [])
    {
        return $this->addRoute('PATCH', $path, $handler, $middleware);
    }

    public function delete($path, $handler, array $middleware = [])
    {
        return $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    public function group(array $attributes, callable $callback)
    {
        $this->groupStack[] = $attributes;
        $callback($this);
        array_pop($this->groupStack);
        return $this;
    }

    public function resource($name, $controller, array $middleware = [])
    {
        $this->get($name, [$controller, 'index'], $middleware);
        $this->get($name . '/create', [$controller, 'create'], $middleware);
        $this->post($name, [$controller, 'store'], $middleware);
        $this->get($name . '/{id}', [$controller, 'show'], $middleware);
        $this->get($name . '/{id}/edit', [$controller, 'edit'], $middleware);
        $this->put($name . '/{id}', [$controller, 'update'], $middleware);
        $this->delete($name . '/{id}', [$controller, 'destroy'], $middleware);
        return $this;
    }

    private function addRoute($method, $path, $handler, array $middleware = [])
    {
        $prefix = $this->groupStack ? end($this->groupStack)['prefix'] ?? '' : '';
        $groupMiddleware = $this->groupStack ? end($this->groupStack)['middleware'] ?? [] : [];

        $path = $this->basePath . $prefix . $path;
        $pattern = $this->convertToRegex($path);
        $middleware = array_merge($groupMiddleware, $middleware);

        $this->routes[$method][$pattern] = [
            'pattern' => $path,
            'handler' => $handler,
            'params' => $this->extractParams($path),
            'middleware' => $middleware
        ];
        return $this;
    }

    private function convertToRegex($path)
    {
        $path = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $path);
        $path = preg_replace('/\{([a-zA-Z0-9_]+)\?\}/', '([^/]*)', $path);
        return '#^' . $path . '$#';
    }

    private function extractParams($path)
    {
        preg_match_all('/\{([a-zA-Z0-9_]+)(\?)?\}/', $path, $matches);
        return $matches[1];
    }

    public function dispatch(Request $request, Response $response)
    {
        $uri = rtrim($request->path(), '/');
        if ($uri === '') {
            $uri = '/';
        }

        $method = $request->method();

        foreach (($this->routes[$method] ?? []) as $pattern => $route) {
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                $params = array_combine($route['params'], $matches);
                return $this->runRoute($route, $params, $request, $response);
            }
        }

        return $response->json(['error' => 'Route not found'], 404);
    }

    private function runRoute(array $route, array $params, Request $request, Response $response)
    {
        $middleware = $route['middleware'];
        $handler = $route['handler'];
        $params['request'] = $request;
        $params['response'] = $response;

        $core = function ($req, $res) use ($handler, $params) {
            return $this->callHandler($handler, $params, $req, $res);
        };

        $pipeline = array_reduce(
            array_reverse($middleware),
            function ($next, $mwClass) {
                return function ($req, $res) use ($mwClass, $next) {
                    $middleware = $this->container->make($mwClass);
                    return $middleware->handle($req, $res, $next);
                };
            },
            $core
        );

        return $pipeline($request, $response);
    }

    private function callHandler($handler, array $params, Request $request, Response $response)
    {
        if (is_array($handler)) {
            list($controller, $method) = $handler;
            $instance = $this->container->make($controller);
            $args = $this->resolveMethodArgs($instance, $method, $params);
            $result = call_user_func_array([$instance, $method], $args);
        } elseif (is_callable($handler)) {
            $reflector = new \ReflectionFunction(\Closure::fromCallable($handler));
            $args = $this->resolveCallableArgs($reflector, $params);
            $result = call_user_func_array($handler, $args);
        } else {
            throw new \Exception('Invalid route handler');
        }

        if ($result instanceof Response) {
            return $result;
        }
        if ($result === null) {
            return $response;
        }
        if (is_string($result)) {
            return $response->content($result);
        }
        if (is_array($result)) {
            return $response->json($result);
        }
        return $response;
    }

    private function resolveMethodArgs($instance, $method, array $params)
    {
        try {
            $reflector = new \ReflectionMethod($instance, $method);
        } catch (\ReflectionException $e) {
            throw new \Exception("Method {$method} not found");
        }
        return $this->resolveCallableArgs($reflector, $params);
    }

    private function resolveCallableArgs(\ReflectionFunctionAbstract $reflector, array $params)
    {
        $args = [];
        foreach ($reflector->getParameters() as $param) {
            $name = $param->getName();
            $type = $param->getType();

            if (isset($params[$name])) {
                $args[] = $params[$name];
                continue;
            }

            if ($type === null) {
                if ($param->isDefaultValueAvailable()) {
                    $args[] = $param->getDefaultValue();
                    continue;
                }
                throw new \Exception("Cannot resolve parameter [{$name}]");
            }

            $typeName = $type->getName();
            if (in_array($typeName, [Request::class, 'App\\Core\\Request'])) {
                $args[] = $this->container->make(Request::class);
            } elseif (in_array($typeName, [Response::class, 'App\\Core\\Response'])) {
                $args[] = $this->container->make(Response::class);
            } elseif (class_exists($typeName)) {
                $args[] = $this->container->make($typeName);
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            } else {
                throw new \Exception("Cannot resolve parameter [{$name}] of type {$typeName}");
            }
        }
        return $args;
    }

    public function url($name, array $params = [])
    {
        foreach ($this->routes as $method => $routes) {
            foreach ($routes as $pattern => $route) {
                if ($route['name'] ?? null === $name) {
                    $url = $route['pattern'];
                    foreach ($params as $key => $value) {
                        $url = preg_replace('/\{' . $key . '\??\}/', $value, $url);
                    }
                    return $url;
                }
            }
        }
        return '#';
    }
}
