<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected $container;
    protected $request;
    protected $response;
    protected $auth;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->request = $container->make(Request::class);
        $this->response = new Response();
        $this->auth = $container->make(Auth::class);
    }

    protected function view($template, $data = [], $status = 200, $layout = 'layouts.app')
    {
        $csrf = $this->csrfToken();
        View::share('csrf', $csrf);
        $data['csrf'] = $csrf;
        $data['user'] = $data['user'] ?? ($this->auth->check() ? $this->auth->user() : null);
        $data['currentUser'] = $data['user'];
        $data['content'] = View::render($template, $data);
        $result = $layout ? View::render($layout, $data) : $data['content'];
        return $this->response->content($result)->status($status);
    }

    protected function raw($content, $status = 200, $contentType = 'text/html; charset=utf-8')
    {
        return $this->response
            ->status($status)
            ->withHeaders(['Content-Type' => $contentType])
            ->content($content);
    }

    protected function json($data, $status = 200)
    {
        return $this->response->json($data, $status);
    }

    protected function redirect($url, $status = 302)
    {
        return $this->response->redirect($url, $status);
    }

    protected function validate(array $data, array $rules, array $messages = [])
    {
        $validator = new Validator($data, $rules, $messages);
        $validator->validate();
        if (!empty($validator->errors())) {
            $_SESSION['_errors'] = $validator->errors();
            $_SESSION['_old'] = $data;
        }
        return $validator->errors();
    }

    protected function csrfToken()
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    protected function verifyCsrf($token = null)
    {
        $token = $token ?? $this->request->input('_csrf') ?? $this->request->header('X-CSRF-TOKEN');
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    protected function back()
    {
        $referer = $this->request->header('Referer', '/');
        return $this->response->redirect($referer);
    }

    protected function input($key, $default = null)
    {
        return $this->request->input($key, $default);
    }

    protected function send(Response $response = null)
    {
        $response = $response ?? $this->response;
        $response->send();
        return $this;
    }
}
