<?php
/**
 * admin/dashboard.php — Main dashboard: stats + full product table.
 */
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../includes/functions.php';

$products   = get_all_products();
$categories = get_all_categories();

$log_file  = __DIR__ . '/../data/contact_submissions.log';
$sub_count = file_exists($log_file) ? count(file($log_file, FILE_SKIP_EMPTY_LINES)) : 0;

// Read and clear one-time flash cookie
$flash = null;
if (!empty($_COOKIE['jwf_flash'])) {
    $flash = json_decode($_COOKIE['jwf_flash'], true);
    setcookie('jwf_flash', '', ['expires' => time() - 3600, 'path' => '/admin',
        'samesite' => 'Strict', 'httponly' => true]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Jobros Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body class="admin-layout">

<?php require __DIR__ . '/partials/sidebar.php'; ?>

<main class="admin-main">

    <div class="admin-topbar">
        <h1 class="admin-page-title">Dashboard</h1>
        <a href="/admin/product" class="btn btn-primary">+ Add Product</a>
    </div>

    <?php if ($flash): ?>
    <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
        <?= htmlspecialchars($flash['msg']) ?>
    </div>
    <?php endif; ?>

    <?php if (!getenv('GITHUB_TOKEN') || !getenv('GITHUB_REPO')): ?>
    <div class="alert alert-error" style="font-size:0.85rem">
        <strong>⚠ Product saving is not configured.</strong>
        To add, edit, or delete products you must set two environment variables in your
        <a href="https://vercel.com/dashboard" target="_blank" rel="noopener" style="color:inherit;font-weight:700">Vercel Project Settings → Environment Variables</a>:<br>
        <code style="background:rgba(0,0,0,0.06);padding:2px 5px;border-radius:3px;margin:4px 0;display:inline-block">GITHUB_TOKEN</code> — a GitHub Personal Access Token with <em>Contents: Read &amp; Write</em> permission<br>
        <code style="background:rgba(0,0,0,0.06);padding:2px 5px;border-radius:3px;margin:4px 0;display:inline-block">GITHUB_REPO</code> — your repo in <code style="background:rgba(0,0,0,0.06);padding:2px 5px;border-radius:3px">owner/repo</code> format (e.g. <code style="background:rgba(0,0,0,0.06);padding:2px 5px;border-radius:3px">johndoe/jobroswoodandfab</code>)
    </div>
    <?php endif; ?>

    <!-- Stats row -->
    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-number"><?= count($products) ?></span>
            <span class="stat-label">Products</span>
        </div>
        <div class="stat-card">
            <span class="stat-number"><?= count($categories) ?></span>
            <span class="stat-label">Categories</span>
        </div>
        <div class="stat-card">
            <span class="stat-number"><?= $sub_count ?></span>
            <span class="stat-label">Quote Requests</span>
            <a href="/admin/submissions" class="stat-link">View →</a>
        </div>
    </div>

    <!-- Product table -->
    <div class="admin-card">
        <div class="admin-card-header"><h2>All Products</h2></div>

        <?php if (empty($products)): ?>
            <p class="empty-state">No products yet. <a href="/admin/product">Add your first one</a>.</p>
        <?php else: ?>
        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width:40px">ID</th>
                        <th style="width:80px">Photo</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th style="width:160px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($products as $p):
                    $thumb = get_primary_image($p, 80, 60);
                ?>
                    <tr>
                        <td class="td-muted"><?= (int)$p['id'] ?></td>
                        <td>
                            <img src="<?= htmlspecialchars($thumb) ?>"
                                 alt="<?= htmlspecialchars($p['name']) ?>"
                                 class="table-thumb" width="80" height="60">
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($p['name']) ?></strong>
                            <div class="td-sub"><?= htmlspecialchars($p['short_description']) ?></div>
                        </td>
                        <td><?= htmlspecialchars($p['category']) ?></td>
                        <td><?= htmlspecialchars($p['price']) ?></td>
                        <td class="td-actions">
                            <a href="/product/<?= (int)$p['id'] ?>" target="_blank"
                               class="btn btn-sm btn-ghost" title="View on site">👁</a>
                            <a href="/admin/product/<?= (int)$p['id'] ?>"
                               class="btn btn-sm btn-outline">Edit</a>
                            <form method="POST" action="/admin/delete" class="d-inline"
                                  onsubmit="return confirm('Delete \'<?= htmlspecialchars(addslashes($p['name'])) ?>\'?')">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
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
