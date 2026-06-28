<?php

declare(strict_types=1);

use App\Core\Router;
use App\Core\Container;
use App\Controllers\AuthController;
use App\Controllers\HabitController;
use App\Controllers\AdminController;
use App\Controllers\HomeController;
use App\Controllers\PricingController;
use App\Controllers\PaymentController;
use App\Controllers\StatisticController;
use App\Controllers\SettingsController;
use App\Core\AuthMiddleware;
use App\Core\AdminMiddleware;
use App\Core\GuestMiddleware;
use App\Core\CsrfMiddleware;

$container = Container::getInstance();

$router = new Router($container);
$router->setBasePath('');

$router->get('/', [HomeController::class, 'index']);

$router->group(['middleware' => [GuestMiddleware::class]], function (Router $r) {
    $r->get('/login', [AuthController::class, 'showLogin']);
    $r->get('/register', [AuthController::class, 'showRegister']);
});

$router->post('/login', [AuthController::class, 'login'], [CsrfMiddleware::class]);
$router->post('/register', [AuthController::class, 'register'], [CsrfMiddleware::class]);
$router->get('/logout', [AuthController::class, 'logout']);
$router->get('/pricing', [PricingController::class, 'index']);
$router->post('/api/subscribe', [PaymentController::class, 'subscribe'], [CsrfMiddleware::class]);

$router->group(['middleware' => [AuthMiddleware::class, CsrfMiddleware::class]], function (Router $r) {
    $r->get('/habits', [HabitController::class, 'index']);
    $r->post('/habits', [HabitController::class, 'create']);
    $r->get('/habits/{id}', [HabitController::class, 'show']);
    $r->put('/habits/{id}', [HabitController::class, 'update']);
    $r->delete('/habits/{id}', [HabitController::class, 'delete']);
    $r->post('/habits/{id}/log', [HabitController::class, 'log']);

    $r->get('/stats', [StatisticController::class, 'index']);

    $r->get('/settings', [SettingsController::class, 'index']);
    $r->post('/api/settings', [SettingsController::class, 'update']);
});

$router->group(['middleware' => [AuthMiddleware::class, AdminMiddleware::class, CsrfMiddleware::class]], function (Router $r) {
    $r->get('/admin', [AdminController::class, 'dashboard']);
    $r->get('/admin/users', [AdminController::class, 'users']);
    $r->get('/admin/audit', [AdminController::class, 'audit']);
    $r->put('/admin/users/{id}/subscription', [AdminController::class, 'updateUserSubscription']);
    $r->post('/admin/users/{id}/toggle-ban', [AdminController::class, 'toggleBan']);
    $r->post('/admin/users/{id}/make-admin', [AdminController::class, 'makeAdmin']);
    $r->post('/admin/users/{id}/remove-admin', [AdminController::class, 'removeAdmin']);
});

return $router;
