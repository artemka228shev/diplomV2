<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Container;
use App\Services\AdminService;

class AdminController extends Controller
{
    private $adminService;

    public function __construct(Container $container, AdminService $adminService)
    {
        parent::__construct($container);
        $this->adminService = $adminService;
    }

    public function dashboard()
    {
        $stats = $this->adminService->getDashboardStats();
        $recentActions = $this->adminService->getRecentAdminActions(20);

        return $this->view('admin.dashboard', [
            'stats' => $stats,
            'recentActions' => $recentActions
        ]);
    }

    public function users()
    {
        $page = $this->request->get('page', 1);
        $search = $this->request->get('search', '');
        $data = $this->adminService->getUsersWithPagination($page, 25, $search);

        return $this->view('admin.users', $data + ['search' => $search]);
    }

    public function audit()
    {
        $action = $this->request->get('action', '');
        $adminId = $this->request->get('admin_id', '');
        $startDate = $this->request->get('start_date', date('Y-m-d', strtotime('-30 days')));
        $endDate = $this->request->get('end_date', date('Y-m-d'));
        $page = $this->request->get('page', 1);

        $filters = [];
        if ($action !== '') $filters['action'] = $action;
        if ($adminId !== '' && ctype_digit((string)$adminId)) {
            $filters['admin_id'] = (int)$adminId;
        }

        $data = $this->adminService->getAuditLogs($startDate, $endDate, $page, 25, $filters);

        return $this->view('admin.audit', $data + [
            'selectedAction' => $action,
            'selectedAdminId' => $adminId,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }

    public function updateUserSubscription($id)
    {
        $admin = $this->auth->user();
        $subscriptionType = $this->request->input('subscription_type', '');

        $result = $this->adminService->updateUserSubscription($admin['id'], $id, $subscriptionType);
        $status = $result['success'] ? 200 : 422;
        return $this->json($result, $status);
    }

    public function toggleBan($id)
    {
        $admin = $this->auth->user();
        $result = $this->adminService->toggleUserBan($admin['id'], $id);
        return $this->json($result);
    }

    public function makeAdmin($id)
    {
        $admin = $this->auth->user();
        $result = $this->adminService->makeAdmin($admin['id'], $id);
        return $this->json($result);
    }

    public function removeAdmin($id)
    {
        $admin = $this->auth->user();
        $result = $this->adminService->removeAdmin($admin['id'], $id);
        return $this->json($result);
    }
}
