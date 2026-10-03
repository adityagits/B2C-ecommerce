<?php

/** Encrypted store for third-party credentials (payment gateway, carrier, SMTP...). */
class Credential extends Model
{
    public function list(): array
    {
        return $this->all('SELECT id, name, hint, updated_at FROM credentials ORDER BY name');
    }

    public function save(string $name, string $value): void
    {
        $this->run(
            'INSERT INTO credentials (name, value_enc, hint) VALUES (?,?,?)
             ON DUPLICATE KEY UPDATE value_enc = VALUES(value_enc), hint = VALUES(hint)',
            [$name, Crypto::encrypt($value), substr($value, -4)]
        );
    }

    public function delete(int $id): void
    {
        $this->run('DELETE FROM credentials WHERE id = ?', [$id]);
    }

    /** Decrypted value for use by gateway/carrier integrations. */
    public static function get(string $name): ?string
    {
        $row = (new self())->one('SELECT value_enc FROM credentials WHERE name = ?', [$name]);
        return $row ? Crypto::decrypt($row['value_enc']) : null;
    }
}
