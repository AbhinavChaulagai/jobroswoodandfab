<?php
/**
 * auth_functions.php — Token helpers shared by login page and auth_check.
 * Does NOT redirect — safe to include on the login page itself.
 */

function make_admin_token(int $ttl = 604800): string {
    $secret  = getenv('ADMIN_PASSWORD') ?: 'admin123';
    $expiry  = time() + $ttl;
    $payload = 'admin:' . $expiry;
    $sig     = hash_hmac('sha256', $payload, $secret);
    return base64_encode($payload . ':' . $sig);
}

function verify_admin_token(string $token): bool {
    $decoded = base64_decode($token, true);
    if ($decoded === false) return false;
    $parts = explode(':', $decoded, 3);
    if (count($parts) !== 3) return false;
    [$user, $expiry, $sig] = $parts;
    if ($user !== 'admin' || time() > (int)$expiry) return false;
    $secret   = getenv('ADMIN_PASSWORD') ?: 'admin123';
    $expected = hash_hmac('sha256', "{$user}:{$expiry}", $secret);
    return hash_equals($expected, $sig);
}
