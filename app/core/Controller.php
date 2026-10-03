<?php

abstract class Controller
{
    protected function view(string $name, array $data = [], string $layout = 'layout/main'): void
    {
        extract($data);
        $flashes = pull_flash();
        ob_start();
        require APP . "/views/$name.php";
        $content = ob_get_clean();
        require APP . "/views/$layout.php";
        unset($_SESSION['old']);
    }

    protected function redirect(string $path): never
    {
        header('Location: ' . url($path));
        exit;
    }

    protected function back(string $fallback = '/'): never
    {
        $ref = $_SERVER['HTTP_REFERER'] ?? '';
        $refHost = parse_url($ref, PHP_URL_HOST);
        $myHost = explode(':', $_SERVER['HTTP_HOST'] ?? '')[0];
        if ($ref !== '' && $refHost === $myHost) {
            header('Location: ' . $ref);
            exit;
        }
        $this->redirect($fallback);
    }

    protected function notFound(): never
    {
        http_response_code(404);
        $this->view('errors/404');
        exit;
    }

    protected function requireLogin(): array
    {
        if (!auth_user()) {
            $_SESSION['intended'] = request_path();
            flash('info', 'Please log in to continue.');
            $this->redirect('/login');
        }
        return auth_user();
    }

    protected function requireAdmin(): array
    {
        $user = $this->requireLogin();
        if (!is_admin()) {
            http_response_code(403);
            $this->view('errors/403');
            exit;
        }
        return $user;
    }

    protected function input(string $key, $default = ''): string
    {
        return trim((string) ($_POST[$key] ?? $default));
    }

    /** Remember submitted fields so forms can repopulate. */
    protected function withOld(array $keys): void
    {
        foreach ($keys as $k) {
            $_SESSION['old'][$k] = $_POST[$k] ?? '';
        }
    }
}
