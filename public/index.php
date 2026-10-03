<?php
// Front controller. Dev server: php -S localhost:8000 -t public public/index.php
if (PHP_SAPI === 'cli-server') {
    $file = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (is_file($file)) {
        return false;
    }
}

define('ROOT', dirname(__DIR__));
define('APP', ROOT . '/app');

session_start();

require APP . '/core/helpers.php';
spl_autoload_register(function (string $class) {
    foreach (['core', 'controllers', 'models'] as $dir) {
        $file = APP . "/$dir/$class.php";
        if (is_file($file)) {
            require $file;
            return;
        }
    }
});

$router = new Router();
require APP . '/routes.php';
$router->dispatch($_SERVER['REQUEST_METHOD'], request_path());
