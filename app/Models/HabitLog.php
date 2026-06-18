<?php

namespace App\Models;

use App\Core\Model;

class HabitLog extends Model
{
    protected $table = 'habit_logs';

    public function findByHabit($habitId, $startDate = null, $endDate = null)
    {
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
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE habit_id = ? AND date = ?");
        $stmt->execute([$habitId, $date]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function deleteByHabitAndDate($habitId, $date)
    {
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
        $stmt = $this->db->prepare("
            SELECT date FROM {$this->table} 
            WHERE habit_id = ? 
            ORDER BY date DESC 
            LIMIT 100
        ");
        $stmt->execute([$habitId]);
        $logs = $stmt->fetchAll();
        
        if (empty($logs)) {
            return 0;
        }
        
        $streak = 0;
        $currentDate = date('Y-m-d');
        
        foreach ($logs as $log) {
            $logDate = strtotime($log['date']);
            $expectedDate = strtotime("-{$streak} days", strtotime($currentDate));
            
            if (strtotime($log['date']) === $expectedDate || 
                strtotime($log['date']) === strtotime("-" . ($streak - 1) . " days", strtotime($currentDate))) {
                $streak++;
            } elseif (strtotime($log['date']) < $expectedDate) {
                break;
            }
        }
        
        return $streak;
    }
}
