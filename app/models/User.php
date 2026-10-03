<?php

class User extends Model
{
    public function findByEmail(string $email): ?array
    {
        return $this->one('SELECT * FROM users WHERE email = ?', [$email]);
    }

    public function create(string $name, string $email, string $password): int
    {
        $this->run(
            'INSERT INTO users (name, email, password) VALUES (?, ?, ?)',
            [$name, $email, password_hash($password, PASSWORD_DEFAULT)]
        );
        return (int) $this->db->lastInsertId();
    }

    public function count(): int
    {
        return (int) $this->value("SELECT COUNT(*) FROM users WHERE role = 'customer'");
    }
}
