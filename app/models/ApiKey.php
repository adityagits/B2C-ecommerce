<?php

class ApiKey extends Model
{
    public function list(): array
    {
        return $this->all('SELECT id, name, key_prefix, last_used_at, revoked_at, created_at FROM api_keys ORDER BY id DESC');
    }

    /** Create a key; the plaintext is returned once and only its SHA-256 hash is stored. */
    public function create(string $name): string
    {
        $prefix = bin2hex(random_bytes(4));
        $key = "sk_{$prefix}_" . bin2hex(random_bytes(20));
        $this->run('INSERT INTO api_keys (name, key_prefix, key_hash) VALUES (?,?,?)', [$name, $prefix, hash('sha256', $key)]);
        return $key;
    }

    public function revoke(int $id): void
    {
        $this->run('UPDATE api_keys SET revoked_at = NOW() WHERE id = ? AND revoked_at IS NULL', [$id]);
    }

    public function authenticate(string $token): bool
    {
        if (!preg_match('/^sk_([0-9a-f]{8})_[0-9a-f]{40}$/', $token, $m)) {
            return false;
        }
        $row = $this->one('SELECT id, key_hash FROM api_keys WHERE key_prefix = ? AND revoked_at IS NULL', [$m[1]]);
        if (!$row || !hash_equals($row['key_hash'], hash('sha256', $token))) {
            return false;
        }
        $this->run('UPDATE api_keys SET last_used_at = NOW() WHERE id = ?', [$row['id']]);
        return true;
    }
}
