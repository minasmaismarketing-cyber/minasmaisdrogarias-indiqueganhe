<?php

declare(strict_types=1);

class Router
{
    /** @var array<string, array<string, array{0: string, 1: string, 2: ?string}>> */
    private array $staticRoutes = [];

    /**
     * @var array<string, list<array{
     *     pattern: RoutePattern,
     *     controller: string,
     *     action: string,
     *     middleware: ?string,
     *     path: string,
     *     literalCount: int
     * }>>
     */
    private array $dynamicRoutes = [];

    public function get(string $path, string $controller, string $action = 'index', ?string $middleware = null): void
    {
        $this->addRoute('GET', $path, $controller, $action, $middleware);
    }

    public function post(string $path, string $controller, string $action = 'index', ?string $middleware = null): void
    {
        $this->addRoute('POST', $path, $controller, $action, $middleware);
    }

    public function put(string $path, string $controller, string $action = 'index', ?string $middleware = null): void
    {
        $this->addRoute('PUT', $path, $controller, $action, $middleware);
    }

    public function delete(string $path, string $controller, string $action = 'index', ?string $middleware = null): void
    {
        $this->addRoute('DELETE', $path, $controller, $action, $middleware);
    }

    private function addRoute(string $method, string $path, string $controller, string $action, ?string $middleware = null): void
    {
        $path = $this->normalizePath($path);
        $method = strtoupper($method);

        $pattern = RoutePattern::compile($path);

        if ($pattern === null) {
            $this->staticRoutes[$method][$path] = [$controller, $action, $middleware];
            return;
        }

        $this->dynamicRoutes[$method][] = [
            'pattern' => $pattern,
            'controller' => $controller,
            'action' => $action,
            'middleware' => $middleware,
            'path' => $path,
            'literalCount' => $pattern->literalSegmentCount(),
        ];

        usort(
            $this->dynamicRoutes[$method],
            static fn(array $a, array $b): int => $b['literalCount'] <=> $a['literalCount']
                ?: strlen($b['path']) <=> strlen($a['path'])
        );
    }

    public function dispatch(string $method, string $path): void
    {
        $path = $this->normalizePath($path);
        $method = strtoupper($method);

        Logger::info('Router dispatch', [
            'method' => $method,
            'path' => $path,
            'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
            'script_name' => $_SERVER['SCRIPT_NAME'] ?? '',
            'base_path' => base_path(),
        ]);

        $resolved = $this->resolve($method, $path);

        if ($resolved === null) {
            Logger::warning('Route not found', ['method' => $method, 'path' => $path]);
            $this->notFound();
            return;
        }

        Logger::info('Route matched', [
            'controller' => $resolved['controller'],
            'action' => $resolved['action'],
            'params' => $resolved['params'],
        ]);

        $this->runMiddleware($resolved['middleware']);
        $this->invoke($resolved['controller'], $resolved['action'], $resolved['params']);
    }

    /**
     * Resolve rota sem executar controller (útil para testes e diagnóstico).
     *
     * @return array{
     *     controller: string,
     *     action: string,
     *     middleware: ?string,
     *     params: array<string, string>
     * }|null
     */
    public function resolve(string $method, string $path): ?array
    {
        $path = $this->normalizePath($path);
        $method = strtoupper($method);

        if (isset($this->staticRoutes[$method][$path])) {
            [$controller, $action, $middleware] = $this->staticRoutes[$method][$path];

            return [
                'controller' => $controller,
                'action' => $action,
                'middleware' => $middleware,
                'params' => [],
            ];
        }

        foreach ($this->dynamicRoutes[$method] ?? [] as $route) {
            $params = $route['pattern']->match($path);

            if ($params !== null) {
                return [
                    'controller' => $route['controller'],
                    'action' => $route['action'],
                    'middleware' => $route['middleware'],
                    'params' => $params,
                ];
            }
        }

        return null;
    }

    private function runMiddleware(?string $middleware): void
    {
        if ($middleware === null || $middleware === '') {
            return;
        }

        $middlewareFile = BASE_PATH . '/middleware/' . $middleware . '.php';

        if (is_file($middlewareFile)) {
            require_once $middlewareFile;
        }

        if (!class_exists($middleware)) {
            throw new RuntimeException("Middleware não encontrado: {$middleware}");
        }

        $instance = new $middleware();

        if (method_exists($instance, 'handle')) {
            $instance->handle();
        }
    }

    /**
     * @param array<string, string> $params
     */
    private function invoke(string $controller, string $action, array $params = []): void
    {
        $controllerFile = BASE_PATH . '/controllers/' . $controller . '.php';

        if (!is_file($controllerFile)) {
            throw new RuntimeException("Controller não encontrado: {$controller}");
        }

        require_once $controllerFile;

        if (!class_exists($controller)) {
            throw new RuntimeException("Classe do controller não encontrada: {$controller}");
        }

        $instance = new $controller();
        $method = new ReflectionMethod($instance, $action);

        if (!$method->isPublic()) {
            throw new RuntimeException("Ação não encontrada: {$controller}@{$action}");
        }

        $args = $this->buildActionArguments($method, $params);
        $method->invokeArgs($instance, $args);
    }

    /**
     * @param array<string, string> $params
     * @return list<mixed>
     */
    private function buildActionArguments(ReflectionMethod $method, array $params): array
    {
        $args = [];

        foreach ($method->getParameters() as $parameter) {
            $name = $parameter->getName();
            $value = $params[$name] ?? null;

            if ($value === null && $parameter->isDefaultValueAvailable()) {
                $args[] = $parameter->getDefaultValue();
                continue;
            }

            if ($value === null) {
                throw new RuntimeException("Parâmetro obrigatório ausente na rota: {$name}");
            }

            $type = $parameter->getType();

            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $args[] = $value;
                continue;
            }

            $args[] = match ($type instanceof ReflectionNamedType ? $type->getName() : null) {
                'int' => (int) $value,
                'float' => (float) $value,
                'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
                default => $value,
            };
        }

        return $args;
    }

    public function notFound(): void
    {
        http_response_code(404);
        require BASE_PATH . '/views/errors/404.php';
    }

    private function normalizePath(string $path): string
    {
        $path = strip_front_script($path);
        $path = '/' . trim($path, '/');

        return $path === '/' ? '/' : rtrim($path, '/');
    }
}
