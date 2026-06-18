<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\User;

class Auth
{
    private $userModel;

    public function __construct(User $userModel = null)
    {
        $this->userModel = $userModel ?? new User();
    }

    public function attempt($email, $password)
    {
        $user = $this->userModel->findByEmailOrUsername($email);

        if (empty($user)) {
            return ['success' => false, 'error' => 'Неверный логин или пароль'];
        }

        $user = $user[0];

        if (!password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'error' => 'Неверный логин или пароль'];
        }

        if (!empty($user['is_banned'])) {
            return ['success' => false, 'error' => 'Аккаунт заблокирован'];
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_username'] = $user['username'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['subscription_type'] = $user['subscription_type'];

        return ['success' => true, 'user' => $user];
    }

    public function register($username, $email, $password)
    {
        $existing = $this->userModel->findByEmailOrUsername($email);
        if (!empty($existing)) {
            return ['success' => false, 'error' => 'Email или логин уже зарегистрирован'];
        }

        $existingUsername = $this->userModel->findByEmailOrUsername($username);
        if (!empty($existingUsername)) {
            return ['success' => false, 'error' => 'Логин уже занят'];
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $userId = $this->userModel->create([
            'username' => $username,
            'email' => $email,
            'password_hash' => $hash,
            'role' => 'user',
            'subscription_type' => 'free'
        ]);

        return ['success' => true, 'user_id' => $userId];
    }

    public function logout()
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    public function user()
    {
        if (empty($_SESSION['user_id'])) {
            return null;
        }
        
        // Всегда загружаем свежие данные из БД, чтобы подписка/роль были актуальны
        $user = $this->userModel->find($_SESSION['user_id']);
        if (!$user) {
            return null;
        }
        
        // Обновляем сессию, если данные изменились
        if (($user['role'] ?? '') !== ($_SESSION['user_role'] ?? '')
            || ($user['subscription_type'] ?? '') !== ($_SESSION['subscription_type'] ?? '')) {
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['subscription_type'] = $user['subscription_type'];
        }
        
        return [
            'id' => (int)$user['id'],
            'email' => $user['email'],
            'username' => $user['username'],
            'role' => $user['role'],
            'subscription_type' => $user['subscription_type']
        ];
    }

    public function check()
    {
        return !empty($_SESSION['user_id']);
    }

    public function guest()
    {
        return !$this->check();
    }

    public function isAdmin()
    {
        return $this->check() && ($_SESSION['user_role'] ?? '') === 'admin';
    }

    public function subscriptionType()
    {
        return $_SESSION['subscription_type'] ?? 'free';
    }

    public function hasSubscription($type)
    {
        return $this->subscriptionType() === $type;
    }

    public function canCreateHabits()
    {
        $currentCount = $this->getHabitCount();
        $limit = $this->getHabitLimit();
        return $currentCount < $limit;
    }

    public function getHabitCount()
    {
        if (empty($_SESSION['user_id'])) {
            return 0;
        }
        return $this->userModel->getHabitCount($_SESSION['user_id']);
    }

    public function getHabitLimit()
    {
        $limits = [
            'free' => 5,
            'basic' => 15,
            'premium' => PHP_INT_MAX
        ];
        return $limits[$this->subscriptionType()] ?? 5;
    }

    public function changePassword($userId, $currentPassword, $newPassword)
    {
        $user = $this->userModel->find($userId);
        if (!$user) {
            return ['success' => false, 'error' => 'Пользователь не найден'];
        }
        if (!password_verify($currentPassword, $user['password_hash'])) {
            return ['success' => false, 'error' => 'Неверный текущий пароль'];
        }
        $this->userModel->update($userId, [
            'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT)
        ]);
        return ['success' => true];
    }

    public function verifyCsrf($token)
    {
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}
