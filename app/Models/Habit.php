<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Habit extends Model
{
    protected $table = 'habits';

    public function findByUser($userId)
    {
        $this->validateTableName();
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function findActiveByUser($userId)
    {
        $this->validateTableName();
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE user_id = ? AND is_active = 1 ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function findByIdAndUser($id, $userId)
    {
        $this->validateTableName();
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $userId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
}
