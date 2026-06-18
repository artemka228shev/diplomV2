<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class AuditLog extends Model
{
    protected $table = 'audit_log';

    /**
     * Записать действие администратора
     */
    public function log($adminId, $action, $targetUserId = null, $details = null)
    {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;

        return $this->create([
            'admin_id' => $adminId,
            'action' => $action,
            'target_user_id' => $targetUserId,
            'details' => $details ? json_encode($details) : null,
            'ip_address' => $ipAddress
        ]);
    }

    /**
     * Получить действия за период с пагинацией
     */
    public function getByPeriod($startDate, $endDate = null, $limit = null, $page = 1, $filters = [])
    {
        $this->validateTableName();
        $endDate = $endDate ?? date('Y-m-d');
        $offset = (int)(($page - 1) * $limit);

        // Приводим даты к формату DATETIME, чтобы BETWEEN корректно отрабатывал
        $startDateTime = $startDate . ' 00:00:00';
        $endDateTime = $endDate . ' 23:59:59';

        $whereClauses = ["al.created_at BETWEEN ? AND ?"];
        $params = [$startDateTime, $endDateTime];

        if (!empty($filters['action'])) {
            $whereClauses[] = "al.action = ?";
            $params[] = $filters['action'];
        }
        if (!empty($filters['admin_id'])) {
            $whereClauses[] = "al.admin_id = ?";
            $params[] = $filters['admin_id'];
        }

        $whereSql = implode(' AND ', $whereClauses);
        $limitSql = ($limit !== null && $limit !== false) ? "LIMIT " . (int)$limit . " OFFSET " . (int)$offset : "";

        $stmt = $this->db->prepare("
            SELECT al.*, u.email as admin_email, target.email as target_email
            FROM {$this->table} al
            LEFT JOIN users u ON al.admin_id = u.id
            LEFT JOIN users target ON al.target_user_id = target.id
            WHERE {$whereSql}
            ORDER BY al.created_at DESC
            {$limitSql}
        ");

        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Получить общее количество записей для пагинации
     */
    public function getCountByPeriod($startDate, $endDate = null, $filters = [])
    {
        $this->validateTableName();
        $endDate = $endDate ?? date('Y-m-d');

        $startDateTime = $startDate . ' 00:00:00';
        $endDateTime = $endDate . ' 23:59:59';

        $whereClauses = ["al.created_at BETWEEN ? AND ?"];
        $params = [$startDateTime, $endDateTime];

        if (!empty($filters['action'])) {
            $whereClauses[] = "al.action = ?";
            $params[] = $filters['action'];
        }
        if (!empty($filters['admin_id'])) {
            $whereClauses[] = "al.admin_id = ?";
            $params[] = $filters['admin_id'];
        }

        $whereSql = implode(' AND ', $whereClauses);

        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM {$this->table} al
            WHERE {$whereSql}
        ");

        $stmt->execute($params);
        $result = $stmt->fetch();
        return (int)$result['count'];
    }

    /**
     * Получить действия конкретного админа
     */
    public function getByAdmin($adminId, $limit = 100)
    {
        $this->validateTableName();
        $stmt = $this->db->prepare("
            SELECT al.*, u.email as admin_email, target.email as target_email
            FROM {$this->table} al
            LEFT JOIN users u ON al.admin_id = u.id
            LEFT JOIN users target ON al.target_user_id = target.id
            WHERE al.admin_id = ?
            ORDER BY al.created_at DESC
            LIMIT ?
        ");

        $stmt->execute([$adminId, (int)$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Статистика действий
     */
    public function getActionStats($startDate, $endDate = null)
    {
        $this->validateTableName();
        $endDate = $endDate ?? date('Y-m-d');

        $startDateTime = $startDate . ' 00:00:00';
        $endDateTime = $endDate . ' 23:59:59';

        $stmt = $this->db->prepare("
            SELECT action, COUNT(*) as count
            FROM {$this->table}
            WHERE created_at BETWEEN ? AND ?
            GROUP BY action
        ");

        $stmt->execute([$startDateTime, $endDateTime]);
        return $stmt->fetchAll();
    }
}
