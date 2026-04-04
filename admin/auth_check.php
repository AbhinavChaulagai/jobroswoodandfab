<?php
/**
 * auth_check.php — Include at the top of every protected admin page.
 * Redirects to login if the signed cookie is missing or invalid.
 */

require_once __DIR__ . '/auth_functions.php';

$_cookie = $_COOKIE['jwf_admin'] ?? '';
if (!$_cookie || !verify_admin_token($_cookie)) {
    header('Location: /admin/');
    exit;
}

/** CSRF token derived from the auth cookie — no session needed. */
function csrf_token(): string {
    $secret = getenv('ADMIN_PASSWORD') ?: 'admin123';
    return hash_hmac('sha256', 'csrf:' . ($_COOKIE['jwf_admin'] ?? ''), $secret);
}

/** Verify CSRF on POST. Exits 403 if invalid. */
function verify_csrf(): void {
    $submitted = $_POST['csrf_token'] ?? '';
    if (!hash_equals(csrf_token(), $submitted)) {
        http_response_code(403);
        exit('Invalid CSRF token. Please go back and try again.');
    }
}
