<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Container;
use App\Services\StatisticsService;

class StatisticController extends Controller
{
    private $statsService;

    public function __construct(Container $container, StatisticsService $statsService)
    {
        parent::__construct($container);
        $this->statsService = $statsService;
    }

    public function index()
    {
        $user = $this->auth->user();
        $data = $this->statsService->getDashboardData($user['id']);

        return $this->view('stats.index', $data);
    }
}
