<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Container;
use App\Models\User;

class PaymentController extends Controller
{
    private $userModel;

    public function __construct(Container $container, User $userModel)
    {
        parent::__construct($container);
        $this->userModel = $userModel;
    }

    public function subscribe()
    {
        $plan = $this->request->input('plan', '');
        $validPlans = ['free', 'basic', 'premium'];

        if (!in_array($plan, $validPlans)) {
            return $this->json(['error' => 'Неверный тариф'], 422);
        }

        $user = $this->auth->user();
        $this->userModel->updateSubscription($user['id'], $plan);
        $_SESSION['subscription_type'] = $plan;

        return $this->json([
            'success' => true,
            'subscription_type' => $plan,
            'redirect' => '/habits'
        ]);
    }

    public function getStatus()
    {
        $user = $this->auth->user();
        return $this->json([
            'subscription_type' => $user['subscription_type'] ?? 'free',
            'limits' => [
                'max_habits' => $this->auth->getHabitLimit()
            ]
        ]);
    }
}
