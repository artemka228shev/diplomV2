<?php
/**
 * Front Controller - Habitify
 * Точка входа для Apache/Nginx
 */

// Загрузка .env если есть
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if (!array_key_exists($name, $_ENV)) {
            $_ENV[$name] = $value;
            putenv("$name=$value");
        }
    }
}

// Настройка сессий
ini_set('session.save_path', __DIR__ . '/tmp/sessions');
if (!is_dir(__DIR__ . '/tmp/sessions')) {
    mkdir(__DIR__ . '/tmp/sessions', 0777, true);
}
session_start();

// Базовый URL
$scriptName = $_SERVER['SCRIPT_NAME'];
$basePath = dirname($scriptName);
if ($basePath === '\\' || $basePath === '/' || $basePath === '\\/') {
    $basePath = '';
}
define('BASE_URL', $basePath);

// PSR-4 автолоадер
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/app/';
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

// Загрузка ядра и роутера
use App\Core\Container;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;

$container = Container::getInstance();
$request = new Request();
$response = new Response();

$router = require __DIR__ . '/routes/web.php';

try {
    $result = $router->dispatch($request, $response);
    if ($result instanceof Response) {
        $result->send();
    } else {
        $response->send();
    }
} catch (\Throwable $e) {
    error_log('[Habitify] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    if (getenv('APP_DEBUG') === 'true') {
        echo '<pre>' . $e->getMessage() . "\n" . $e->getTraceAsString() . '</pre>';
    } else {
        http_response_code(500);
        echo 'Внутренняя ошибка сервера';
    }
}
