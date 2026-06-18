<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Container;

class AuthController extends Controller
{
    public function __construct(Container $container)
    {
        parent::__construct($container);
    }

    public function showLogin()
    {
        if ($this->auth->check()) {
            return $this->redirect('/');
        }
        return $this->view('auth.login');
    }

    public function showRegister()
    {
        if ($this->auth->check()) {
            return $this->redirect('/');
        }
        return $this->view('auth.register');
    }

    public function login()
    {
        $data = $this->request->only(['login', 'password']);

        $errors = $this->validate($data, [
            'login' => 'required',
            'password' => 'required|min:6'
        ]);

        if (!empty($errors)) {
            return $this->json(['errors' => $errors], 422);
        }

        $result = $this->auth->attempt($data['login'], $data['password']);

        if (!$result['success']) {
            return $this->json(['error' => $result['error']], 401);
        }

        return $this->json(['redirect' => '/']);
    }

    public function register()
    {
        $data = $this->request->only(['username', 'email', 'password', 'password_confirm']);

        $errors = $this->validate($data, [
            'username' => 'required|min:3',
            'email' => 'required|email',
            'password' => 'required|min:6',
            'password_confirm' => 'required'
        ]);

        if (!empty($errors)) {
            return $this->json(['errors' => $errors], 422);
        }

        if ($data['password'] !== $data['password_confirm']) {
            return $this->json(['errors' => ['password_confirm' => ['Пароли не совпадают']]], 422);
        }

        $result = $this->auth->register($data['username'], $data['email'], $data['password']);

        if (!$result['success']) {
            return $this->json(['error' => $result['error']], 400);
        }

        $this->auth->attempt($data['email'], $data['password']);
        return $this->json(['redirect' => '/habits']);
    }

    public function logout()
    {
        $this->auth->logout();
        return $this->redirect('/login');
    }
}
