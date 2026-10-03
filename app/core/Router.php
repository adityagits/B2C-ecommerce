<?php

class Router
{
    private array $routes = [];

    public function get(string $path, string $handler): void  { $this->add('GET', $path, $handler); }
    public function post(string $path, string $handler): void { $this->add('POST', $path, $handler); }

    private function add(string $method, string $path, string $handler): void
    {
        $regex = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $path) . '$#';
        $this->routes[] = [$method, $regex, $handler];
    }

    public function dispatch(string $method, string $path): void
    {
        foreach ($this->routes as [$m, $regex, $handler]) {
            if ($m !== $method || !preg_match($regex, $path, $matches)) {
                continue;
            }
            if ($method === 'POST') {
                $token = $_POST['_token'] ?? '';
                if (!hash_equals(csrf_token(), (string) $token)) {
                    http_response_code(419);
                    exit('Invalid or expired form token. Go back, refresh and try again.');
                }
            }
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            [$class, $action] = explode('@', $handler);
            (new $class())->$action(...array_values($params));
            return;
        }
        http_response_code(404);
        (new ErrorController())->show();
    }
}
