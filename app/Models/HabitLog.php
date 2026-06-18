<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class HabitLog extends Model
{
    protected $table = 'habit_logs';

    public function findByHabit($habitId, $startDate = null, $endDate = null)
    {
        $this->validateTableName();
        $sql = "SELECT * FROM {$this->table} WHERE habit_id = ?";
        $params = [$habitId];
        
        if ($startDate && $endDate) {
            $sql .= " AND date BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
        }
        
        $sql .= " ORDER BY date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findByDate($habitId, $date)
    {
        $this->validateTableName();
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE habit_id = ? AND date = ?");
        $stmt->execute([$habitId, $date]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function deleteByHabitAndDate($habitId, $date)
    {
        $this->validateTableName();
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE habit_id = ? AND date = ?");
        return $stmt->execute([$habitId, $date]);
    }

    public function log($habitId, $date, $data)
    {
        $existing = $this->findByDate($habitId, $date);
        
        if ($existing) {
            $this->update($existing['id'], array_merge($data, ['date' => $date]));
            return $existing['id'];
        }
        
        return $this->create(array_merge($data, ['habit_id' => $habitId, 'date' => $date]));
    }

    public function getStreak($habitId)
    {
        $this->validateTableName();
        $stmt = $this->db->prepare("
            SELECT date FROM {$this->table} 
            WHERE habit_id = ? 
            ORDER BY date DESC 
            LIMIT 365
        ");
        $stmt->execute([$habitId]);
        $logs = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        
        if (empty($logs)) {
            return 0;
        }
        
        $streak = 0;
        $today = new \DateTime('today');
        $checkDate = clone $today;
        
        foreach ($logs as $logDateStr) {
            $logDate = new \DateTime($logDateStr);
            $diff = (int)$today->diff($logDate)->days;
            
            // Первый лог: проверяем, что он сегодня или вчера
            if ($streak === 0) {
                if ($diff === 0 || $diff === 1) {
                    $streak = 1;
                    $checkDate = clone $logDate;
                } else {
                    // Сегодня нет выполнения — streak = 0
                    return 0;
                }
                continue;
            }
            
            // Остальные логи: проверяем, что дата на 1 день раньше предыдущей
            $expectedDate = clone $checkDate;
            $expectedDate->modify('-1 day');
            
            if ($logDate->format('Y-m-d') === $expectedDate->format('Y-m-d')) {
                $streak++;
                $checkDate = clone $logDate;
            } else {
                break;
            }
        }
        
        return $streak;
    }
}
