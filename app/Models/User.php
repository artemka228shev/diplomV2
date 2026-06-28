<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected $table = 'users';

    public function findByEmailOrUsername($login)
    {
        $this->validateTableName();
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = ? OR username = ? LIMIT 1");
        $stmt->execute([$login, $login]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function getHabitCount($userId)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM habits WHERE user_id = ? AND is_active = 1");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return (int) $result['count'];
    }

    public function updateSubscription($userId, $type)
    {
        return $this->update($userId, ['subscription_type' => $type]);
    }

    public function ban($userId)
    {
        return $this->update($userId, ['is_banned' => 1]);
    }

    public function unban($userId)
    {
        return $this->update($userId, ['is_banned' => 0]);
    }

    /**
     * Получить всех пользователей с пагинацией и поиском
     */
    public function getAllWithPagination($page = 1, $limit = 25, $search = '')
    {
        $this->validateTableName();
        $offset = ($page - 1) * $limit;
        
        $whereSql = '';
        $params = [];
        
        if (!empty($search)) {
            $whereSql = "WHERE email LIKE ? OR username LIKE ?";
            $searchParam = "%{$search}%";
            $params = [$searchParam, $searchParam];
        }
        
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table}
            {$whereSql}
            ORDER BY id DESC
            LIMIT ? OFFSET ?
        ");
        
        $stmt->execute(array_merge($params, [$limit, $offset]));
        return $stmt->fetchAll();
    }

    /**
     * Получить общее количество пользователей для пагинации
     */
    public function getCount($search = '')
    {
        $this->validateTableName();
        $whereSql = '';
        $params = [];
        
        if (!empty($search)) {
            $whereSql = "WHERE email LIKE ? OR username LIKE ?";
            $searchParam = "%{$search}%";
            $params = [$searchParam, $searchParam];
        }
        
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM {$this->table} {$whereSql}
        ");
        
        $stmt->execute($params);
        $result = $stmt->fetch();
        return (int)$result['count'];
    }
}

