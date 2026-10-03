<?php

abstract class Model
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::get();
    }

    protected function all(string $sql, array $params = []): array
    {
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    protected function one(string $sql, array $params = []): ?array
    {
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->fetch() ?: null;
    }

    protected function run(string $sql, array $params = []): int
    {
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->rowCount();
    }

    protected function value(string $sql, array $params = [])
    {
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->fetchColumn();
    }
}
