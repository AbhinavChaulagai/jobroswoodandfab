<?php
/**
 * admin/logout.php — Destroy the admin session and redirect to login.
 */
if (session_status() === PHP_SESSION_NONE) session_start();
$_SESSION = [];
session_destroy();
header('Location: /admin/index.php');
exit;
