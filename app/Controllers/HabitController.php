<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Container;
use App\Services\HabitService;

class HabitController extends Controller
{
    private $habitService;

    public function __construct(Container $container, HabitService $habitService)
    {
        parent::__construct($container);
        $this->habitService = $habitService;
    }

    public function index()
    {
        $user = $this->auth->user();
        $habits = $this->habitService->getUserHabitsWithTodayLogs($user['id']);

        return $this->view('habits.index', [
            'habits' => $habits,
            'today' => date('Y-m-d')
        ]);
    }

    public function show($id)
    {
        $data = $this->habitService->getHabitWithLogs($id);
        if (!$data) {
            return $this->redirect('/habits');
        }

        return $this->view('habits.show', $data);
    }

    public function create()
    {
        $data = $this->request->only(['title', 'description', 'type', 'frequency', 'target_value', 'unit', 'days_of_week']);
        $result = $this->habitService->create($data);

        if (!$result['success']) {
            $status = !empty($result['upgrade_url']) ? 403 : 422;
            return $this->json($result, $status);
        }

        return $this->json($result);
    }

    public function update($id)
    {
        $data = $this->request->only(['title', 'description', 'type', 'frequency', 'target_value', 'unit', 'days_of_week']);
        $result = $this->habitService->update($id, $data);

        if (!$result['success']) {
            return $this->json($result, 404);
        }
        return $this->json($result);
    }

    public function delete($id)
    {
        $result = $this->habitService->delete($id);
        if (!$result['success']) {
            return $this->json($result, 404);
        }
        return $this->json($result);
    }

    public function log($id)
    {
        $data = $this->request->only(['date', 'completed', 'value', 'quality_rating', '_method']);
        $date = $data['date'] ?? date('Y-m-d');

        // Отмена выполнения
        if (($data['_method'] ?? '') === 'DELETE') {
            $result = $this->habitService->undoLog($id, $date);
            if (!$result['success']) {
                return $this->json($result, 404);
            }
            return $this->json($result);
        }

        unset($data['date'], $data['_method']);
        $result = $this->habitService->logCompletion($id, $date, $data);
        if (!$result['success']) {
            return $this->json($result, 404);
        }
        return $this->json($result);
    }
}
