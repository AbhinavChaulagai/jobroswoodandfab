<?php
/**
 * auth_check.php — Include at the top of every protected admin page.
 * Starts the session and redirects to login if the user is not authenticated.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: /admin/index.php');
    exit;
}

/**
 * Generate (or return existing) CSRF token stored in the session.
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify the CSRF token submitted with a POST form.
 * Kills the request with 403 if the token is missing or wrong.
 */
function verify_csrf(): void {
    $submitted = $_POST['csrf_token'] ?? '';
    if (!hash_equals(csrf_token(), $submitted)) {
        http_response_code(403);
        exit('Invalid CSRF token. Please go back and try again.');
    }
}
