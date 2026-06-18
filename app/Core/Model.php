<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

class Model
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';

    /** Разрешённые имена таблиц для защиты от SQL-инъекции */
    private const ALLOWED_TABLES = [
        'users', 'habits', 'habit_logs', 'reminders', 'subscriptions', 'ads_clicks', 'audit_log'
    ];

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Проверяет, что имя таблицы безопасно для использования в SQL
     */
    protected function validateTableName(): void
    {
        if (!in_array($this->table, self::ALLOWED_TABLES, true)) {
            throw new \RuntimeException("Table '{$this->table}' is not allowed");
        }
    }

    /**
     * Экранирует имя колонки для безопасной подстановки в SQL
     */
    private function quoteColumn(string $column): string
    {
        // Разрешены только буквы, цифры и подчёркивания
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $column)) {
            throw new \RuntimeException("Invalid column name: {$column}");
        }
        return "`{$column}`";
    }

    public function all($columns = ['*'])
    {
        $this->validateTableName();
        $cols = $columns === ['*'] ? '*' : implode(', ', array_map([$this, 'quoteColumn'], $columns));
        $stmt = $this->db->query("SELECT {$cols} FROM {$this->table}");
        return $stmt->fetchAll();
    }

    public function find($id)
    {
        $this->validateTableName();
        $pk = $this->quoteColumn($this->primaryKey);
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$pk} = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function create($data)
    {
        $this->validateTableName();
        $columns = implode(', ', array_map([$this, 'quoteColumn'], array_keys($data)));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $stmt = $this->db->prepare("INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})");
        $stmt->execute($data);
        
        return (int) $this->db->lastInsertId();
    }

    public function update($id, $data)
    {
        $this->validateTableName();
        $pk = $this->quoteColumn($this->primaryKey);
        $set = [];
        foreach (array_keys($data) as $column) {
            $col = $this->quoteColumn($column);
            $set[] = "{$col} = :{$column}";
        }
        $setSql = implode(', ', $set);
        
        $stmt = $this->db->prepare("UPDATE {$this->table} SET {$setSql} WHERE {$pk} = :id");
        $data['id'] = $id;
        
        return $stmt->execute($data);
    }

    public function delete($id)
    {
        $this->validateTableName();
        $pk = $this->quoteColumn($this->primaryKey);
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$pk} = ?");
        return $stmt->execute([$id]);
    }

    public function where($column, $operator, $value, $columns = ['*'])
    {
        $this->validateTableName();
        $col = $this->quoteColumn($column);
        // Только безопасные операторы сравнения
        $allowedOperators = ['=', '<', '>', '<=', '>=', '<>', '!=', 'LIKE', 'NOT LIKE'];
        if (!in_array(strtoupper($operator), $allowedOperators, true)) {
            throw new \RuntimeException("Invalid operator: {$operator}");
        }
        $cols = $columns === ['*'] ? '*' : implode(', ', array_map([$this, 'quoteColumn'], $columns));
        $stmt = $this->db->prepare("SELECT {$cols} FROM {$this->table} WHERE {$col} {$operator} ?");
        $stmt->execute([$value]);
        return $stmt->fetchAll();
    }
}
