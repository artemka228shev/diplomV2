<?php

namespace App\Core;

use PDO;

class Model
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function all($columns = ['*'])
    {
        $cols = $columns === ['*'] ? '*' : implode(', ', $columns);
        $stmt = $this->db->query("SELECT {$cols} FROM {$this->table}");
        return $stmt->fetchAll();
    }

    public function find($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function create($data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $stmt = $this->db->prepare("INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})");
        $stmt->execute($data);
        
        return (int) $this->db->lastInsertId();
    }

    public function update($id, $data)
    {
        $set = [];
        foreach (array_keys($data) as $column) {
            $set[] = "{$column} = :{$column}";
        }
        $setSql = implode(', ', $set);
        
        $stmt = $this->db->prepare("UPDATE {$this->table} SET {$setSql} WHERE {$this->primaryKey} = :id");
        $data['id'] = $id;
        
        return $stmt->execute($data);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?");
        return $stmt->execute([$id]);
    }

    public function where($column, $operator, $value, $columns = ['*'])
    {
        $cols = $columns === ['*'] ? '*' : implode(', ', $columns);
        $stmt = $this->db->prepare("SELECT {$cols} FROM {$this->table} WHERE {$column} {$operator} ?");
        $stmt->execute([$value]);
        return $stmt->fetchAll();
    }
}
