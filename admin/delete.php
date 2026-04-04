<?php
/**
 * admin/delete.php — Delete a product by ID.
 * Accepts POST only. Redirects back to dashboard with a flash message.
 */
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /admin/dashboard.php');
    exit;
}

verify_csrf();

$id       = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$products = get_all_products();

$target = null;
foreach ($products as $p) {
    if ((int)$p['id'] === $id) {
        $target = $p;
        break;
    }
}

if ($target === null) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Product not found.'];
    header('Location: /admin/dashboard.php');
    exit;
}

// Remove the product
$updated = array_values(array_filter($products, fn($p) => (int)$p['id'] !== $id));

if (save_products($updated)) {
    $_SESSION['flash'] = ['type' => 'success', 'msg' => "Product \"{$target['name']}\" deleted."];
} else {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Failed to save products.json — check file permissions.'];
}

header('Location: /admin/dashboard.php');
exit;
