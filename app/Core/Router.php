<?php
declare(strict_types=1);

namespace App\Core;

use App\Core\Exceptions\HttpException;

/**
 * Simple regex router with {param} placeholders and automatic HEAD support.
 */
final class Router
{
    /** @var array<string,array<int,array{pattern:string,params:string[],action:mixed,regex:string}>> */
    private array $routes = [];

    /** @var callable[] */
    private array $groupMiddleware = [];

    /** @var callable[] */
    private array $matchedMiddleware = [];

    public function get(string $path, mixed $action): void
    {
        $this->add('GET', $path, $action);
        $this->add('HEAD', $path, $action);
    }

    public function post(string $path, mixed $action): void
    {
        $this->add('POST', $path, $action);
    }

    public function any(string $path, mixed $action): void
    {
        foreach (['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE'] as $method) {
            $this->add($method, $path, $action);
        }
    }

    /** Register a callback that runs for every matched route. */
    public function middleware(callable $callback): void
    {
        $this->groupMiddleware[] = $callback;
    }

    private function add(string $method, string $path, mixed $action): void
    {
        $path = '/' . trim($path, '/');
        if ($path === '/') {
            $path = '/';
        }

        $params = [];
        if (preg_match_all('#\{([A-Za-z_][A-Za-z0-9_]*)\}#', $path, $matches)) {
            $params = $matches[1];
        }

        $regex = preg_replace_callback(
            '#\{([A-Za-z_][A-Za-z0-9_]*)\}#',
            static fn (array $m): string => ($m[1] === 'id' ? '(\d+)' : '([^/]+)'),
            $path
        );
        $regex = '#^' . $regex . '$#';

        $this->routes[strtoupper($method)][] = [
            'pattern' => $path,
            'params'  => $params,
            'action'  => $action,
            'regex'   => $regex,
        ];
    }

    public function dispatch(Request $request): Response
    {
        $method  = $request->method();
        $path    = rtrim($request->path(), '/');
        $path    = $path === '' ? '/' : $path;
        $allowed = [];

        foreach (['GET', 'POST', 'PUT', 'PATCH', 'DELETE'] as $candidate) {
            foreach ($this->routes[$candidate] ?? [] as $route) {
                if (preg_match($route['regex'], $path, $matches)) {
                    array_shift($matches);
                    $request->setAttribute('_route_pattern', $route['pattern']);
                    $this->matchedMiddleware = $this->groupMiddleware;

                    if ($candidate === $method || ($method === 'HEAD' && $candidate === 'GET')) {
                        return $this->invoke($route, $request, $matches);
                    }

                    $allowed[] = $candidate;
                }
            }
        }

        if ($allowed !== []) {
            throw new HttpException(405, 'Method not allowed', ['Allow' => implode(', ', array_unique($allowed))]);
        }

        throw new HttpException(404, 'Page not found');
    }

    /** @param string[] $matches */
    private function invoke(array $route, Request $request, array $matches): Response
    {
        foreach ($route['params'] as $index => $name) {
            $request->setRouteParam($name, (string) ($matches[$index] ?? ''));
        }

        $action = $route['action'];

        if (is_string($action) && str_contains($action, '@')) {
            [$class, $method] = explode('@', $action, 2);
            $fqcn              = $class::class === $class
                ? $class
                : 'App\\Controllers\\' . $class;

            if (!class_exists($fqcn)) {
                throw new \RuntimeException('Controller not found: ' . $fqcn);
            }

            $controller = new $fqcn();
            $callable   = [$controller, $method];
        } elseif (is_callable($action)) {
            $callable = $action;
        } elseif (is_array($action) && count($action) === 2) {
            [$class, $method] = $action;
            $fqcn              = is_object($class) ? $class::class : (class_exists($class) ? $class : 'App\\Controllers\\' . $class);
            $controller        = is_object($class) ? $class : new $fqcn();
            $callable          = [$controller, $method];
        } else {
            throw new \RuntimeException('Invalid route action.');
        }

        $result = $callable(...$this->resolveArguments($callable, $request, $matches, $route['params']));
        $this->matchedMiddleware = [];

        if ($result instanceof Response) {
            return $result;
        }
        if (is_string($result)) {
            return new Response($result, 200);
        }
        if ($result === null) {
            return new Response('', 204);
        }
        if (is_array($result)) {
            return (new Response())->json($result);
        }

        throw new \RuntimeException('Controller returned an unsupported value.');
    }

    /**
     * Build the argument list for a route action from its signature.
     *
     * Actions are free to declare whatever they need: the Request is injected
     * only where the signature asks for it, and route placeholders are matched
     * by parameter name so `article(string $slug)` and
     * `updateStatus(Request $request, string $id)` both work.
     *
     * @param callable     $callable
     * @param string[]     $matches
     * @param string[]     $paramNames
     *
     * @return array<int,mixed>
     */
    private function resolveArguments(callable $callable, Request $request, array $matches, array $paramNames): array
    {
        try {
            $reflection = is_array($callable)
                ? new \ReflectionMethod($callable[0], (string) $callable[1])
                : new \ReflectionFunction(\Closure::fromCallable($callable));
        } catch (\ReflectionException $e) {
            return [$request];
        }

        $arguments = [];
        $index     = 0;

        foreach ($reflection->getParameters() as $parameter) {
            if ($parameter->isVariadic()) {
                break;
            }

            $name = $parameter->getName();
            $type = $parameter->getType();

            // 1. A parameter typed as Request gets the current request.
            if ($type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
                $class = $type->getName();
                if (is_a($class, Request::class, true)) {
                    $arguments[] = $request;
                    continue;
                }
            }

            // 2. A parameter named after a route placeholder gets its value.
            if (in_array($name, $paramNames, true)) {
                $position = array_search($name, $paramNames, true);
                $arguments[] = (string) ($matches[$position] ?? '');
                $index++;
                continue;
            }

            // 3. Fall back to positional values for untyped legacy signatures.
            if ($type === null && $index < count($matches) && in_array($index, $paramNames, true)) {
                $arguments[] = (string) $matches[$index];
                $index++;
                continue;
            }

            // 4. Anything else must have a default or be nullable.
            if ($parameter->isDefaultValueAvailable()) {
                $arguments[] = $parameter->getDefaultValue();
                continue;
            }
            if ($type !== null && $type->allowsNull()) {
                $arguments[] = null;
                continue;
            }

            throw new \RuntimeException(sprintf(
                'Cannot resolve argument $%s of %s.',
                $name,
                $reflection instanceof \ReflectionMethod
                    ? $reflection->getDeclaringClass()->getName() . '::' . $reflection->getName()
                    : 'closure'
            ));
        }

        return $arguments;
    }

    /** @return string[] */
    public function patternMethods(): array
    {
        $patterns = [];
        foreach ($this->routes as $methodRoutes) {
            foreach ($methodRoutes as $route) {
                $patterns[$route['pattern']] = true;
            }
        }

        return array_keys($patterns);
    }
}
