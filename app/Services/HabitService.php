<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Habit;
use App\Models\HabitLog;
use App\Core\Auth;
use App\Core\Database;
use PDO;

class HabitService
{
    private $habitModel;
    private $habitLogModel;
    private $auth;

    public function __construct(Habit $habitModel, HabitLog $habitLogModel, Auth $auth)
    {
        $this->habitModel = $habitModel;
        $this->habitLogModel = $habitLogModel;
        $this->auth = $auth;
    }

    public function getUserHabitsWithTodayLogs($userId)
    {
        $habits = $this->habitModel->findByUser($userId);
        $today = date('Y-m-d');

        foreach ($habits as &$habit) {
            $habit['today_log'] = $this->habitLogModel->findByDate($habit['id'], $today);
        }

        return $habits;
    }

    public function create(array $data)
    {
        $user = $this->auth->user();
        if (!$user) {
            throw new \Exception('Unauthorized');
        }

        $errors = $this->validateHabitData($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Транзакция для предотвращения race condition при проверке лимита
        $db = Database::getConnection();
        try {
            $db->beginTransaction();

            // Блокируем строку пользователя для атомарной проверки
            $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM habits WHERE user_id = ? FOR UPDATE");
            $stmt->execute([$user['id']]);
            $currentCount = (int)$stmt->fetchColumn();

            $limit = $this->auth->getHabitLimit();
            if ($currentCount >= $limit) {
                $db->rollBack();
                return [
                    'success' => false,
                    'error' => 'Достигнут лимит привычек',
                    'limit' => $limit,
                    'current' => $currentCount,
                    'upgrade_url' => '/pricing'
                ];
            }

            $habitId = $this->habitModel->create([
                'user_id' => $user['id'],
                'title' => $data['title'],
                'description' => $data['description'] ?? '',
                'type' => $data['type'] ?? 'boolean',
                'frequency' => $data['frequency'] ?? 'daily',
                'target_value' => ($data['type'] ?? 'boolean') === 'quantitative' ? (int)($data['target_value'] ?? 1) : null,
                'unit' => ($data['type'] ?? 'boolean') === 'quantitative' ? ($data['unit'] ?? '') : null,
                'days_of_week' => !empty($data['days_of_week']) ? json_encode($data['days_of_week']) : null,
                'is_active' => 1
            ]);

            $db->commit();
            return ['success' => true, 'habit_id' => $habitId];
        } catch (\Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public function update($id, array $data)
    {
        $user = $this->auth->user();
        $habit = $this->habitModel->findByIdAndUser($id, $user['id']);
        if (!$habit) {
            return ['success' => false, 'error' => 'Привычка не найдена'];
        }

        // Сливаем новые данные с существующими для валидации
        $mergedData = array_merge($habit, $data);
        $errors = $this->validateHabitData($mergedData);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $this->habitModel->update($id, [
            'title' => $data['title'] ?? $habit['title'],
            'description' => $data['description'] ?? $habit['description'],
            'type' => $data['type'] ?? $habit['type'],
            'frequency' => $data['frequency'] ?? $habit['frequency'],
            'target_value' => ($data['type'] ?? $habit['type']) === 'quantitative' ? (int)($data['target_value'] ?? $habit['target_value']) : null,
            'unit' => ($data['type'] ?? $habit['type']) === 'quantitative' ? ($data['unit'] ?? $habit['unit']) : null,
            'days_of_week' => !empty($data['days_of_week']) ? json_encode($data['days_of_week']) : null
        ]);

        return ['success' => true];
    }

    public function delete($id)
    {
        $user = $this->auth->user();
        $habit = $this->habitModel->findByIdAndUser($id, $user['id']);
        if (!$habit) {
            return ['success' => false, 'error' => 'Привычка не найдена'];
        }
        $this->habitModel->delete($id);
        return ['success' => true];
    }

    public function undoLog($habitId, $date)
    {
        $user = $this->auth->user();
        $habit = $this->habitModel->findByIdAndUser($habitId, $user['id']);
        if (!$habit) {
            return ['success' => false, 'error' => 'Привычка не найдена'];
        }

        $this->habitLogModel->deleteByHabitAndDate($habitId, $date);
        return ['success' => true];
    }

    public function logCompletion($habitId, $date, $data)
    {
        $user = $this->auth->user();
        $habit = $this->habitModel->findByIdAndUser($habitId, $user['id']);
        if (!$habit) {
            return ['success' => false, 'error' => 'Привычка не найдена'];
        }

        $logData = [];
        if (!empty($data['completed'])) {
            $logData['value'] = $data['value'] ?? 1;
            $logData['quality_rating'] = $data['quality_rating'] ?? null;
        }

        $this->habitLogModel->log($habitId, $date, $logData);
        return ['success' => true];
    }

    public function getHabitWithLogs($habitId)
    {
        $user = $this->auth->user();
        $habit = $this->habitModel->findByIdAndUser($habitId, $user['id']);
        if (!$habit) {
            return null;
        }
        $logs = $this->habitLogModel->findByHabit($habitId);
        $streak = $this->habitLogModel->getStreak($habitId);
        return compact('habit', 'logs', 'streak');
    }

    private function validateHabitData(array $data)
    {
        $errors = [];

        $title = $data['title'] ?? '';
        if (empty($title) || mb_strlen($title) < 3) {
            $errors['title'][] = 'Название должно быть не короче 3 символов';
        }

        $type = $data['type'] ?? 'boolean';
        if (!in_array($type, ['boolean', 'quantitative'])) {
            $errors['type'][] = 'Неверный тип привычки';
        }

        $frequency = $data['frequency'] ?? 'daily';
        if (!in_array($frequency, ['daily', 'weekly', 'custom'])) {
            $errors['frequency'][] = 'Неверная частота';
        }

        return $errors;
    }
}
