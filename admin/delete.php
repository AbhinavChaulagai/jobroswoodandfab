<?php
/**
 * admin/delete.php — Delete a product by ID. POST only.
 */
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /admin/dashboard');
    exit;
}

verify_csrf();

$id       = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$products = get_all_products();

$target = null;
foreach ($products as $p) {
    if ((int)$p['id'] === $id) { $target = $p; break; }
}

if ($target === null) {
    flash_set('error', 'Product not found.');
    header('Location: /admin/dashboard');
    exit;
}

$updated = array_values(array_filter($products, fn($p) => (int)$p['id'] !== $id));

if (save_products($updated)) {
    flash_set('success', "Product \"{$target['name']}\" deleted.");
} else {
    flash_set('error', 'Could not save products. On Vercel you must set GITHUB_TOKEN and GITHUB_REPO environment variables.');
}

header('Location: /admin/dashboard');
exit;

/** Store a one-time flash message in a short-lived cookie. */
function flash_set(string $type, string $msg): void {
    setcookie('jwf_flash', json_encode(['type' => $type, 'msg' => $msg]), [
        'expires'  => time() + 30,
        'path'     => '/admin',
        'samesite' => 'Strict',
        'httponly' => true,
    ]);
}
