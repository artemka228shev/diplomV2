<?php

namespace App\Core;

class AuthMiddleware extends Middleware
{
    public function handle(Request $request, Response $response, callable $next)
    {
        $auth = Container::getInstance()->make(Auth::class);
        if (!$auth->check()) {
            if ($request->isAjax() || $request->isJson()) {
                return $response->json(['error' => 'Требуется авторизация'], 401);
            }
            return $response->redirect('/login');
        }
        return $next($request, $response);
    }
}