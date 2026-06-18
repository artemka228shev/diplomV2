<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Habit;
use App\Models\HabitLog;

class StatisticsService
{
    private $habitModel;
    private $habitLogModel;

    public function __construct(Habit $habitModel, HabitLog $habitLogModel)
    {
        $this->habitModel = $habitModel;
        $this->habitLogModel = $habitLogModel;
    }

    public function getDashboardData($userId)
    {
        $habits = $this->habitModel->findActiveByUser($userId);

        return [
            'habits' => $habits,
            'stats' => $this->calculateStats($habits),
            'weeklyData' => $this->getWeeklyData($habits),
            'monthlyData' => $this->getMonthlyData($habits),
            'topHabits' => $this->getTopHabits($habits)
        ];
    }

    public function calculateStats(array $habits)
    {
        $totalHabits = count($habits);
        $totalLogs = 0;
        $completedLogs = 0;
        $totalStreak = 0;

        foreach ($habits as $habit) {
            $logs = $this->habitLogModel->findByHabit($habit['id']);
            $totalLogs += count($logs);

            foreach ($logs as $log) {
                if (!empty($log['value'])) {
                    $completedLogs++;
                }
            }

            $totalStreak += $this->habitLogModel->getStreak($habit['id']);
        }

        $completionRate = $totalLogs > 0 ? round(($completedLogs / $totalLogs) * 100) : 0;
        $averageStreak = $totalHabits > 0 ? round($totalStreak / $totalHabits, 1) : 0;

        return [
            'total_habits' => $totalHabits,
            'total_logs' => $totalLogs,
            'completed_logs' => $completedLogs,
            'completion_rate' => $completionRate,
            'average_streak' => $averageStreak
        ];
    }

    public function getWeeklyData(array $habits)
    {
        $weeklyData = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $dayShort = date('D', strtotime($date));

            $completed = 0;
            $total = 0;

            foreach ($habits as $habit) {
                $log = $this->habitLogModel->findByDate($habit['id'], $date);
                $total++;
                if ($log && !empty($log['value'])) {
                    $completed++;
                }
            }

            $weeklyData[] = [
                'date' => $date,
                'day' => $dayShort,
                'completed' => $completed,
                'total' => $total,
                'percentage' => $total > 0 ? round(($completed / $total) * 100) : 0
            ];
        }

        return $weeklyData;
    }

    public function getMonthlyData(array $habits)
    {
        $monthlyData = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));

            $completed = 0;
            $total = 0;

            foreach ($habits as $habit) {
                $log = $this->habitLogModel->findByDate($habit['id'], $date);
                $total++;
                if ($log && !empty($log['value'])) {
                    $completed++;
                }
            }

            $monthlyData[] = [
                'date' => $date,
                'day' => (int)date('d', strtotime($date)),
                'completed' => $completed,
                'total' => $total,
                'percentage' => $total > 0 ? round(($completed / $total) * 100) : 0
            ];
        }

        return $monthlyData;
    }

    public function getTopHabits(array $habits, $limit = 5)
    {
        $habitStats = [];
        foreach ($habits as $habit) {
            $logs = $this->habitLogModel->findByHabit($habit['id']);
            $completed = 0;
            foreach ($logs as $log) {
                if (!empty($log['value'])) {
                    $completed++;
                }
            }
            $habitStats[] = [
                'name' => $habit['title'],
                'completed' => $completed
            ];
        }

        usort($habitStats, function ($a, $b) {
            return $b['completed'] - $a['completed'];
        });

        return array_slice($habitStats, 0, $limit);
    }
}
