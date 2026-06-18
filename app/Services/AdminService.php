<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\AuditLog;

class AdminService
{
    private $userModel;
    private $auditLogModel;

    public function __construct(User $userModel, AuditLog $auditLogModel)
    {
        $this->userModel = $userModel;
        $this->auditLogModel = $auditLogModel;
    }

    public function getDashboardStats()
    {
        $totalUsers = count($this->userModel->all());
        $subscriptions = [
            'free' => count($this->userModel->where('subscription_type', '=', 'free')),
            'basic' => count($this->userModel->where('subscription_type', '=', 'basic')),
            'premium' => count($this->userModel->where('subscription_type', '=', 'premium'))
        ];

        $thisWeek = date('Y-m-d', strtotime('-7 days'));
        $thisMonth = date('Y-m-01');
        $thisYear = date('Y-01-01');

        return [
            'total_users' => $totalUsers,
            'free_users' => $subscriptions['free'],
            'basic_users' => $subscriptions['basic'],
            'premium_users' => $subscriptions['premium'],
            'registrations_week' => count($this->userModel->where('created_at', '>=', $thisWeek)),
            'registrations_month' => count($this->userModel->where('created_at', '>=', $thisMonth)),
            'registrations_year' => count($this->userModel->where('created_at', '>=', $thisYear)),
            'subscriptions' => $subscriptions
        ];
    }

    public function getRecentAdminActions($limit = 20)
    {
        return $this->auditLogModel->getByPeriod(
            date('Y-m-d', strtotime('-7 days')),
            date('Y-m-d H:i:s'),
            $limit
        );
    }

    public function getUsersWithPagination($page, $limit, $search)
    {
        $page = max(1, (int)$page);
        $limit = max(1, (int)$limit);
        $users = $this->userModel->getAllWithPagination($page, $limit, $search);
        $totalCount = $this->userModel->getCount($search);
        $totalPages = max(1, (int)ceil($totalCount / $limit));

        return [
            'users' => $users,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalCount' => $totalCount
        ];
    }

    public function getAuditLogs($startDate, $endDate, $page, $limit, $filters)
    {
        $page = max(1, (int)$page);
        $limit = max(1, (int)$limit);

        $logs = $this->auditLogModel->getByPeriod($startDate, $endDate, $limit, $page, $filters);
        $totalCount = $this->auditLogModel->getCountByPeriod($startDate, $endDate, $filters);
        $totalPages = max(1, (int)ceil($totalCount / $limit));

        $allLogsForFilters = $this->auditLogModel->getByPeriod($startDate, $endDate, null, 1, []);
        $actions = !empty($allLogsForFilters) ? array_unique(array_column($allLogsForFilters, 'action')) : [];
        $admins = $this->userModel->where('role', '=', 'admin');

        return [
            'logs' => $logs,
            'actions' => $actions,
            'admins' => $admins,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalCount' => $totalCount
        ];
    }

    public function updateUserSubscription($adminId, $userId, $subscriptionType)
    {
        $validTypes = ['free', 'basic', 'premium'];
        if (!in_array($subscriptionType, $validTypes)) {
            return ['success' => false, 'error' => 'Неверный тип подписки'];
        }

        $user = $this->userModel->find($userId);
        if (!$user) {
            return ['success' => false, 'error' => 'Пользователь не найден'];
        }

        $oldSubscription = $user['subscription_type'];
        $this->userModel->updateSubscription($userId, $subscriptionType);

        $this->auditLogModel->log(
            $adminId,
            'subscription_change',
            $userId,
            ['old' => $oldSubscription, 'new' => $subscriptionType]
        );

        return ['success' => true, 'subscription_type' => $subscriptionType];
    }

    public function toggleUserBan($adminId, $userId)
    {
        $user = $this->userModel->find($userId);
        if (!$user) {
            return ['success' => false, 'error' => 'Пользователь не найден'];
        }

        $isBanned = $user['is_banned'] ?? 0;
        if ($isBanned) {
            $this->userModel->unban($userId);
            $this->auditLogModel->log($adminId, 'user_unban', $userId);
            return ['success' => true, 'is_banned' => 0];
        }

        $this->userModel->ban($userId);
        $this->auditLogModel->log($adminId, 'user_ban', $userId);
        return ['success' => true, 'is_banned' => 1];
    }

    public function makeAdmin($adminId, $userId)
    {
        $user = $this->userModel->find($userId);
        if (!$user) {
            return ['success' => false, 'error' => 'Пользователь не найден'];
        }
        $this->userModel->update($userId, ['role' => 'admin']);
        $this->auditLogModel->log($adminId, 'user_make_admin', $userId);
        return ['success' => true];
    }

    public function removeAdmin($adminId, $userId)
    {
        $user = $this->userModel->find($userId);
        if (!$user) {
            return ['success' => false, 'error' => 'Пользователь не найден'];
        }
        if ($user['id'] == $adminId) {
            return ['success' => false, 'error' => 'Нельзя убрать админа у себя'];
        }
        $this->userModel->update($userId, ['role' => 'user']);
        $this->auditLogModel->log($adminId, 'user_remove_admin', $userId);
        return ['success' => true];
    }
}
