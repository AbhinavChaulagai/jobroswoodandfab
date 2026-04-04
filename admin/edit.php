<?php
/**
 * admin/edit.php — Add or edit a product, including multi-image management.
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

    // Images come as JSON string from the hidden field managed by JS
    $images_raw = trim($_POST['images_json'] ?? '[]');
    $images     = json_decode($images_raw, true);
    if (!is_array($images)) $images = [];
    $images = array_values(array_filter(array_map('trim', $images)));

    $product = [
        'id'                => $id ?? 0,
        'name'              => trim($_POST['name']              ?? ''),
        'category'          => trim($_POST['category']          ?? ''),
        'short_description' => trim($_POST['short_description'] ?? ''),
        'long_description'  => trim($_POST['long_description']  ?? ''),
        'price'             => trim($_POST['price']             ?? ''),
        'materials'         => array_values(array_filter(array_map('trim', explode(',', $_POST['materials'] ?? '')))),
        'images'            => $images,
        'image'             => $images[0] ?? '',   // keep legacy field in sync
        'dimensions'        => trim($_POST['dimensions']        ?? ''),
        'lead_time'         => trim($_POST['lead_time']         ?? ''),
    ];

    if ($product['name'] === '')              $errors[] = 'Product name is required.';
    if ($product['category'] === '')          $errors[] = 'Category is required.';
    if ($product['short_description'] === '') $errors[] = 'Short description is required.';
    if ($product['price'] === '')             $errors[] = 'Price is required.';

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

// ── GET: load ─────────────────────────────────────────────────────────────────
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

// Existing images for JS bootstrap
$existing_images = [];
if ($product !== null) {
    $existing_images = get_product_images($product);
}

$v = function (string $key, string $default = '') use ($product): string {
    if ($product === null) return $default;
    $val = $product[$key] ?? $default;
    return htmlspecialchars(is_array($val) ? implode(', ', $val) : (string)$val, ENT_QUOTES, 'UTF-8');
};

$page_heading = $is_edit ? 'Edit Product' : 'Add New Product';

function flash_set(string $type, string $msg): void {
    setcookie('jwf_flash', json_encode(['type' => $type, 'msg' => $msg]), [
        'expires' => time() + 30, 'path' => '/admin',
        'samesite' => 'Strict', 'httponly' => true,
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

    <form method="POST" action="/admin/edit" id="productForm" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <?php if ($is_edit && $product): ?>
            <input type="hidden" name="id" value="<?= (int)$product['id'] ?>">
        <?php endif; ?>
        <!-- Images list is serialised into this field by JS before submit -->
        <input type="hidden" name="images_json" id="imagesJson" value="<?= e(json_encode($existing_images)) ?>">

        <div class="edit-layout">

            <!-- ── Left column: product details ── -->
            <div class="edit-main">
                <div class="admin-card">
                    <div class="admin-card-header"><h2>Product Details</h2></div>
                    <div class="product-form">
                        <div class="form-grid">

                            <div class="form-group form-group--full">
                                <label for="name">Product Name <span class="req">*</span></label>
                                <input type="text" id="name" name="name" required value="<?= $v('name') ?>" placeholder="e.g. Farmhouse Dining Table">
                            </div>

                            <div class="form-group">
                                <label for="category">Category <span class="req">*</span></label>
                                <input type="text" id="category" name="category" required value="<?= $v('category') ?>" list="category-list" placeholder="e.g. Dining Tables">
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
                                <textarea id="long_description" name="long_description" rows="6" placeholder="Full description on the product detail page"><?= $v('long_description') ?></textarea>
                            </div>

                            <div class="form-group form-group--full">
                                <label for="materials">Materials <span class="field-hint">(comma-separated)</span></label>
                                <input type="text" id="materials" name="materials" value="<?= $v('materials') ?>" placeholder="White Oak, Linseed Oil Finish, Steel Hardware">
                            </div>

                            <div class="form-group">
                                <label for="dimensions">Dimensions</label>
                                <input type="text" id="dimensions" name="dimensions" value="<?= $v('dimensions') ?>" placeholder='72"L x 36"W x 30"H'>
                            </div>

                            <div class="form-group">
                                <label for="lead_time">Lead Time</label>
                                <input type="text" id="lead_time" name="lead_time" value="<?= $v('lead_time') ?>" placeholder="4–6 weeks">
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Right column: image manager ── -->
            <div class="edit-sidebar">
                <div class="admin-card img-manager-card">
                    <div class="admin-card-header">
                        <h2>Images</h2>
                        <span class="img-count-badge" id="imgCount">0</span>
                    </div>

                    <!-- Drag-and-drop upload zone -->
                    <div class="drop-zone" id="dropZone">
                        <input type="file" id="fileInput" accept="image/jpeg,image/png,image/webp" multiple hidden>
                        <div class="drop-zone-inner" id="dropZoneInner">
                            <span class="drop-zone-icon">&#128247;</span>
                            <p><strong>Drop images here</strong><br>or</p>
                            <button type="button" class="btn btn-outline btn-sm" id="browseBtn">Browse Files</button>
                            <p class="drop-zone-hint">JPEG, PNG, WebP · max 8 MB each</p>
                        </div>
                        <div class="drop-zone-progress" id="uploadProgress" hidden>
                            <div class="progress-bar"><div class="progress-fill" id="progressFill"></div></div>
                            <p id="progressLabel">Uploading…</p>
                        </div>
                    </div>

                    <!-- Add by URL -->
                    <div class="img-url-row">
                        <input type="url" id="urlInput" placeholder="https://example.com/image.jpg" class="img-url-input">
                        <button type="button" class="btn btn-outline btn-sm" id="addUrlBtn">Add URL</button>
                    </div>

                    <!-- Image list (managed by JS) -->
                    <ul class="img-list" id="imgList" aria-label="Product images">
                        <!-- populated by JS -->
                    </ul>

                    <p class="img-hint">First image is the primary display photo. Drag to reorder.</p>
                </div>

                <!-- Submit -->
                <div class="edit-submit">
                    <button type="submit" class="btn btn-primary btn-lg btn-block" id="submitBtn">
                        <?= $is_edit ? 'Save Changes' : 'Add Product' ?>
                    </button>
                    <a href="/admin/dashboard" class="btn btn-ghost btn-block" style="margin-top:8px">Cancel</a>
                </div>
            </div>

        </div><!-- /.edit-layout -->
    </form>
</main>

<!-- Pass existing images to JS -->
<script>
window.EXISTING_IMAGES   = <?= json_encode($existing_images, JSON_UNESCAPED_SLASHES) ?>;
window.UPLOAD_ENDPOINT   = '/api/upload';
window.CSRF_TOKEN        = <?= json_encode(csrf_token()) ?>;
</script>
<script src="/assets/js/image-manager.js"></script>

</body>
</html>
