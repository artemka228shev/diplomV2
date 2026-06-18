<?php

declare(strict_types=1);

namespace App\Core;

class AdminMiddleware extends Middleware
{
    public function handle(Request $request, Response $response, callable $next)
    {
        $auth = Container::getInstance()->make(Auth::class);
        if (!$auth->isAdmin()) {
            if ($request->isAjax() || $request->isJson()) {
                return $response->json(['error' => 'Доступ запрещен. Требуются права администратора'], 403);
            }
            return $response->redirect('/');
        }
        return $next($request, $response);
    }
}