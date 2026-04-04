<?php
/**
 * admin/edit.php — Add a new product or edit an existing one.
 * GET  ?id=X  → load existing product into form
 * GET  (no id)→ blank form for new product
 * POST        → save and redirect
 */
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../includes/functions.php';

$is_edit = false;
$product = null;
$errors  = [];

// ── POST: save ────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $id      = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;
    $is_edit = $id !== null;

    $product = [
        'id'                => $id ?? 0,
        'name'              => trim($_POST['name']              ?? ''),
        'category'          => trim($_POST['category']          ?? ''),
        'short_description' => trim($_POST['short_description'] ?? ''),
        'long_description'  => trim($_POST['long_description']  ?? ''),
        'price'             => trim($_POST['price']             ?? ''),
        'materials'         => array_values(array_filter(array_map('trim', explode(',', $_POST['materials'] ?? '')))),
        'image'             => trim($_POST['image']             ?? ''),
        'dimensions'        => trim($_POST['dimensions']        ?? ''),
        'lead_time'         => trim($_POST['lead_time']         ?? ''),
    ];

    if ($product['name'] === '')              $errors[] = 'Product name is required.';
    if ($product['category'] === '')          $errors[] = 'Category is required.';
    if ($product['short_description'] === '') $errors[] = 'Short description is required.';
    if ($product['price'] === '')             $errors[] = 'Price is required.';
    if ($product['image'] === '')             $errors[] = 'Image filename or URL is required.';

    if (empty($errors)) {
        $all = get_all_products();

        if ($is_edit) {
            foreach ($all as &$p) {
                if ((int)$p['id'] === $id) { $product['id'] = $id; $p = $product; break; }
            }
            unset($p);
        } else {
            $product['id'] = next_product_id($all);
            $all[] = $product;
        }

        if (save_products($all)) {
            flash_set('success', $is_edit
                ? "Product \"{$product['name']}\" updated."
                : "Product \"{$product['name']}\" added.");
            header('Location: /admin/dashboard');
            exit;
        }
        $errors[] = 'Failed to save products.json — check file permissions.';
    }

// ── GET: load form ────────────────────────────────────────────────────────────
} elseif (isset($_GET['id'])) {
    $id      = (int)$_GET['id'];
    $product = get_product_by_id($id);
    $is_edit = true;

    if ($product === null) {
        flash_set('error', 'Product not found.');
        header('Location: /admin/dashboard');
        exit;
    }
}

$v = function (string $key, string $default = '') use ($product): string {
    if ($product === null) return $default;
    $val = $product[$key] ?? $default;
    return htmlspecialchars(is_array($val) ? implode(', ', $val) : (string)$val, ENT_QUOTES, 'UTF-8');
};

$page_heading = $is_edit ? 'Edit Product' : 'Add New Product';

function flash_set(string $type, string $msg): void {
    setcookie('jwf_flash', json_encode(['type' => $type, 'msg' => $msg]), [
        'expires'  => time() + 30,
        'path'     => '/admin',
        'samesite' => 'Strict',
        'httponly' => true,
    ]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_heading ?> — Jobros Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body class="admin-layout">

<?php require __DIR__ . '/partials/sidebar.php'; ?>

<main class="admin-main">
    <div class="admin-topbar">
        <h1 class="admin-page-title"><?= $page_heading ?></h1>
        <a href="/admin/dashboard" class="btn btn-ghost">&larr; Back to Dashboard</a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <strong>Please fix the following:</strong>
            <ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <div class="admin-card">
        <form method="POST" action="/admin/edit" class="product-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <?php if ($is_edit && $product): ?>
                <input type="hidden" name="id" value="<?= (int)$product['id'] ?>">
            <?php endif; ?>

            <div class="form-grid">

                <div class="form-group form-group--full">
                    <label for="name">Product Name <span class="req">*</span></label>
                    <input type="text" id="name" name="name" required value="<?= $v('name') ?>" placeholder="e.g. Farmhouse Dining Table">
                </div>

                <div class="form-group">
                    <label for="category">Category <span class="req">*</span></label>
                    <input type="text" id="category" name="category" required value="<?= $v('category') ?>" placeholder="e.g. Dining Tables" list="category-list">
                    <datalist id="category-list">
                        <option value="Dining Tables">
                        <option value="Coffee Tables">
                        <option value="Shelving">
                        <option value="Benches">
                        <option value="Bed Frames">
                        <option value="Side Tables">
                    </datalist>
                </div>

                <div class="form-group">
                    <label for="price">Price <span class="req">*</span></label>
                    <input type="text" id="price" name="price" required value="<?= $v('price') ?>" placeholder='e.g. $1,250 or "Contact for Quote"'>
                </div>

                <div class="form-group form-group--full">
                    <label for="short_description">Short Description <span class="req">*</span></label>
                    <input type="text" id="short_description" name="short_description" required value="<?= $v('short_description') ?>" placeholder="One-line summary shown on product cards">
                </div>

                <div class="form-group form-group--full">
                    <label for="long_description">Long Description</label>
                    <textarea id="long_description" name="long_description" rows="6" placeholder="Detailed description for the product detail page"><?= $v('long_description') ?></textarea>
                </div>

                <div class="form-group form-group--full">
                    <label for="materials">Materials <span class="field-hint">(comma-separated)</span></label>
                    <input type="text" id="materials" name="materials" value="<?= $v('materials') ?>" placeholder="e.g. White Oak, Linseed Oil Finish, Steel Hardware">
                </div>

                <div class="form-group form-group--full">
                    <label for="image">Image <span class="req">*</span></label>
                    <input type="text" id="image" name="image" required value="<?= $v('image') ?>" placeholder="filename.jpg  or  https://example.com/image.jpg">
                    <p class="field-note">Enter a filename (from assets/images/) or a full URL. A branded placeholder is shown automatically if the file is missing.</p>
                    <?php if ($product && !empty($product['image'])): ?>
                    <div class="image-preview">
                        <img src="<?= e(product_image_url($product['image'], 300, 200)) ?>" alt="Preview" width="300" height="200">
                    </div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="dimensions">Dimensions</label>
                    <input type="text" id="dimensions" name="dimensions" value="<?= $v('dimensions') ?>" placeholder='e.g. 72"L x 36"W x 30"H'>
                </div>

                <div class="form-group">
                    <label for="lead_time">Lead Time</label>
                    <input type="text" id="lead_time" name="lead_time" value="<?= $v('lead_time') ?>" placeholder="e.g. 4–6 weeks">
                </div>

            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-lg">
                    <?= $is_edit ? 'Save Changes' : 'Add Product' ?>
                </button>
                <a href="/admin/dashboard" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</main>

</body>
</html>
