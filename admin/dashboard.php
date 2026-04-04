<?php
/**
 * admin/dashboard.php — Main admin dashboard.
 */
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../includes/functions.php';

$products   = get_all_products();
$categories = get_all_categories();

$log_file  = __DIR__ . '/../data/contact_submissions.log';
$sub_count = file_exists($log_file) ? count(file($log_file, FILE_SKIP_EMPTY_LINES)) : 0;

// Read flash message from short-lived cookie, then clear it
$flash = null;
if (!empty($_COOKIE['jwf_flash'])) {
    $flash = json_decode($_COOKIE['jwf_flash'], true);
    setcookie('jwf_flash', '', ['expires' => time() - 3600, 'path' => '/admin']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Jobros Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body class="admin-layout">

<?php require __DIR__ . '/partials/sidebar.php'; ?>

<main class="admin-main">
    <div class="admin-topbar">
        <h1 class="admin-page-title">Dashboard</h1>
        <a href="/admin/edit" class="btn btn-primary">+ Add Product</a>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['msg']) ?></div>
    <?php endif; ?>

    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-number"><?= count($products) ?></span>
            <span class="stat-label">Total Products</span>
        </div>
        <div class="stat-card">
            <span class="stat-number"><?= count($categories) ?></span>
            <span class="stat-label">Categories</span>
        </div>
        <div class="stat-card">
            <span class="stat-number"><?= $sub_count ?></span>
            <span class="stat-label">Contact Submissions</span>
            <a href="/admin/submissions" class="stat-link">View all &rarr;</a>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header"><h2>Products</h2></div>

        <?php if (empty($products)): ?>
            <p class="empty-state">No products yet. <a href="/admin/edit">Add your first product</a>.</p>
        <?php else: ?>
        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th><th>Image</th><th>Name</th>
                        <th>Category</th><th>Price</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                    <tr>
                        <td class="td-id"><?= (int)$p['id'] ?></td>
                        <td class="td-img">
                            <img src="<?= e(product_image_url($p['image'], 80, 60)) ?>" alt="<?= e($p['name']) ?>" width="80" height="60">
                        </td>
                        <td>
                            <strong><?= e($p['name']) ?></strong>
                            <div class="td-sub"><?= e($p['short_description']) ?></div>
                        </td>
                        <td><?= e($p['category']) ?></td>
                        <td><?= e($p['price']) ?></td>
                        <td class="td-actions">
                            <a href="/product?id=<?= (int)$p['id'] ?>" target="_blank" class="btn btn-sm btn-ghost" title="View on site">&#128065;</a>
                            <a href="/admin/edit?id=<?= (int)$p['id'] ?>" class="btn btn-sm btn-outline">Edit</a>
                            <form method="POST" action="/admin/delete" class="inline-form"
                                  onsubmit="return confirm('Delete \'<?= e(addslashes($p['name'])) ?>\'? This cannot be undone.')">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</main>

</body>
</html>
