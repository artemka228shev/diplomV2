<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Container;

class SettingsController extends Controller
{
    public function __construct(Container $container)
    {
        parent::__construct($container);
    }

    public function index()
    {
        return $this->view('settings.index');
    }

    public function update()
    {
        $action = $this->request->input('action', '');

        if ($action === 'change_password') {
            return $this->changePassword();
        }
        if ($action === 'delete_account') {
            return $this->deleteAccount();
        }

        return $this->json(['error' => 'Неверное действие'], 422);
    }

    private function changePassword()
    {
        $currentPassword = $this->request->input('current_password', '');
        $newPassword = $this->request->input('new_password', '');

        if (empty($currentPassword) || empty($newPassword)) {
            return $this->json(['error' => 'Заполните все поля'], 422);
        }

        $user = $this->auth->user();
        $result = $this->auth->changePassword($user['id'], $currentPassword, $newPassword);

        if (!$result['success']) {
            return $this->json(['error' => $result['error']], 400);
        }
        return $this->json(['success' => true]);
    }

    private function deleteAccount()
    {
        $user = $this->auth->user();
        $userModel = $this->container->make(\App\Models\User::class);
        $userModel->delete($user['id']);
        $this->auth->logout();
        return $this->json(['success' => true]);
    }
}
