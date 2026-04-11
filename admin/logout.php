<?php
/**
 * admin/logout.php — Clear the auth cookie and redirect to login.
 */
setcookie('jwf_admin', '', [
    'expires'  => time() - 3600,
    'path'     => '/',
    'secure'   => true,
    'httponly' => true,
    'samesite' => 'Strict',
]);
header('Location: /admin/');
exit;
