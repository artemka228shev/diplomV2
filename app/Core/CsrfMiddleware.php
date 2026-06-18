<?php

namespace App\Core;

class CsrfMiddleware extends Middleware
{
    public function handle(Request $request, Response $response, callable $next)
    {
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $auth = Container::getInstance()->make(Auth::class);
            $token = $request->input('_csrf') ?? $request->header('X-CSRF-TOKEN');
            if (!$auth->verifyCsrf($token)) {
                if ($request->isAjax() || $request->isJson()) {
                    return $response->json(['error' => 'Неверный CSRF токен'], 403);
                }
                return $response->redirect('/');
            }
        }
        return $next($request, $response);
    }
}