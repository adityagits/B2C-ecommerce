<?php

/** AES-256-GCM encryption for secrets stored in the database (credential vault). */
class Crypto
{
    private static function key(): string
    {
        $env = getenv('APP_KEY');
        if ($env !== false && $env !== '') {
            return hash('sha256', $env, true);
        }
        $file = ROOT . '/storage/app.key';
        if (!is_file($file)) {
            if (!is_dir(dirname($file))) {
                mkdir(dirname($file), 0700, true);
            }
            file_put_contents($file, bin2hex(random_bytes(32)));
            chmod($file, 0600);
        }
        return hash('sha256', trim((string) file_get_contents($file)), true);
    }

    public static function encrypt(string $plain): string
    {
        $iv = random_bytes(12);
        $cipher = openssl_encrypt($plain, 'aes-256-gcm', self::key(), OPENSSL_RAW_DATA, $iv, $tag);
        return base64_encode($iv . $tag . $cipher);
    }

    public static function decrypt(string $payload): ?string
    {
        $raw = base64_decode($payload, true);
        if ($raw === false || strlen($raw) < 28) {
            return null;
        }
        $plain = openssl_decrypt(substr($raw, 28), 'aes-256-gcm', self::key(), OPENSSL_RAW_DATA, substr($raw, 0, 12), substr($raw, 12, 16));
        return $plain === false ? null : $plain;
    }
}
