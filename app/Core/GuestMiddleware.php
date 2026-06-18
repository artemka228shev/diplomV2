<?php

namespace App\Core;

class GuestMiddleware extends Middleware
{
    public function handle(Request $request, Response $response, callable $next)
    {
        $auth = Container::getInstance()->make(Auth::class);
        if ($auth->check()) {
            return $response->redirect('/');
        }
        return $next($request, $response);
    }
}