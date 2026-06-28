<?php

declare(strict_types=1);

ini_set('session.save_path', __DIR__ . '/../tmp/sessions');
if (!is_dir(__DIR__ . '/../tmp/sessions')) {
    mkdir(__DIR__ . '/../tmp/sessions', 0777, true);
}
session_start();

$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$basePath = dirname($scriptName);
if ($basePath === '\\' || $basePath === '/') {
    $basePath = '';
}
define('BASE_URL', $basePath);

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

use App\Core\Container;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;

$container = Container::getInstance();
$container->instance(Container::class, $container);

$request = new Request();
$response = new Response();

$router = require __DIR__ . '/../routes/web.php';

try {
    $result = $router->dispatch($request, $response);
    if ($result instanceof Response) {
        $result->send();
    } else {
        $response->send();
    }
} catch (\Throwable $e) {
    error_log('[Habitify] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    echo 'Внутренняя ошибка сервера';
}
