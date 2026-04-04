<?php
/**
 * admin/product.php — Add or edit a product.
 *
 * GET  (no id)  → blank Add form
 * GET  ?id=X    → Edit form pre-filled
 * POST          → Save and redirect to dashboard
 */
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../includes/functions.php';

/* ── Flash helper ─────────────────────────────────────────────────────────── */
function flash(string $type, string $msg): void {
    setcookie('jwf_flash', json_encode(['type' => $type, 'msg' => $msg]), [
        'expires' => time() + 30, 'path' => '/admin',
        'samesite' => 'Strict', 'httponly' => true,
    ]);
}

/* ── POST: save ───────────────────────────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $id      = isset($_POST['id']) && trim($_POST['id']) !== '' ? (int)$_POST['id'] : null;
    $is_edit = $id !== null;

    // Images come as a JSON array from the hidden field managed by JS
    $images = json_decode(trim($_POST['images_json'] ?? '[]'), true);
    if (!is_array($images)) $images = [];
    $images = array_values(array_filter(array_map('trim', $images)));

    $product = [
        'id'                => $id ?? 0,
        'name'              => trim($_POST['name']              ?? ''),
        'category'          => trim($_POST['category']          ?? ''),
        'short_description' => trim($_POST['short_desc']        ?? ''),
        'long_description'  => trim($_POST['long_desc']         ?? ''),
        'price'             => trim($_POST['price']             ?? ''),
        'materials'         => array_values(array_filter(
                                    array_map('trim', explode(',', $_POST['materials'] ?? ''))
                               )),
        'images'            => $images,
        'image'             => $images[0] ?? '',   // legacy primary field
        'dimensions'        => trim($_POST['dimensions']        ?? ''),
        'lead_time'         => trim($_POST['lead_time']         ?? ''),
    ];

    $errors = [];
    if ($product['name'] === '')              $errors[] = 'Product name is required.';
    if ($product['category'] === '')          $errors[] = 'Category is required.';
    if ($product['short_description'] === '') $errors[] = 'Short description is required.';
    if ($product['price'] === '')             $errors[] = 'Price is required.';

    if (empty($errors)) {
        $all = get_all_products();

        if ($is_edit) {
            $found = false;
            foreach ($all as &$p) {
                if ((int)$p['id'] === $id) {
                    $product['id'] = $id;
                    $p = $product;
                    $found = true;
                    break;
                }
            }
            unset($p);
            if (!$found) $errors[] = 'Product not found.';
        } else {
            $product['id'] = next_product_id($all);
            $all[] = $product;
        }

        if (empty($errors)) {
            if (save_products($all)) {
                $deploy_note = getenv('GITHUB_TOKEN') ? ' Changes will appear on the live site in ~1 minute.' : '';
                flash('success', $is_edit
                    ? ""{$product['name']}" updated successfully.{$deploy_note}"
                    : ""{$product['name']}" added successfully.{$deploy_note}");
                header('Location: /admin/dashboard');
                exit;
            }
            $errors[] = 'Could not save products. On Vercel you must set GITHUB_TOKEN and GITHUB_REPO environment variables — see the README or ask your developer.';
        }
    }

    // Fall through to re-render form with errors
    $is_edit = $id !== null;

/* ── GET: load ────────────────────────────────────────────────────────────── */
} else {
    $errors  = [];
    $is_edit = isset($_GET['id']);
    $id      = $is_edit ? (int)$_GET['id'] : null;

    if ($is_edit) {
        $product = get_product_by_id($id);
        if ($product === null) {
            flash('error', 'Product not found.');
            header('Location: /admin/dashboard');
            exit;
        }
    } else {
        $product = null;
    }
}

/* ── Helpers ──────────────────────────────────────────────────────────────── */
// Safe value getter for form fields
$v = function (string $key, $default = '') use ($product): string {
    if ($product === null) return (string)$default;
    $val = $product[$key] ?? $default;
    if (is_array($val)) $val = implode(', ', $val);
    return htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8');
};

// Existing images for the JS image manager
$existing_images = ($product !== null) ? get_product_images($product) : [];

$page_title = $is_edit ? 'Edit Product' : 'Add New Product';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> — Jobros Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body class="admin-layout">

<?php require __DIR__ . '/partials/sidebar.php'; ?>

<main class="admin-main">

    <div class="admin-topbar">
        <h1 class="admin-page-title"><?= htmlspecialchars($page_title) ?></h1>
        <a href="/admin/dashboard" class="btn btn-ghost">&larr; Back</a>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <strong>Please fix the following:</strong>
        <ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
    </div>
    <?php endif; ?>

    <form method="POST" action="/admin/product" id="productForm">
        <input type="hidden" name="csrf_token"  value="<?= htmlspecialchars(csrf_token()) ?>">
        <input type="hidden" name="images_json" id="imagesJson" value="<?= htmlspecialchars(json_encode($existing_images)) ?>">
        <?php if ($is_edit): ?>
        <input type="hidden" name="id" value="<?= (int)$id ?>">
        <?php endif; ?>

        <div class="product-edit-grid">

            <!-- ═══ LEFT: Product info ═══ -->
            <div>

                <!-- Basic info -->
                <div class="admin-card" style="margin-bottom:20px">
                    <div class="admin-card-header"><h2>Product Info</h2></div>
                    <div class="product-form">
                        <div class="form-grid">

                            <div class="form-group form-group--full">
                                <label for="name">Name <span class="req">*</span></label>
                                <input type="text" id="name" name="name" required
                                       value="<?= $v('name') ?>"
                                       placeholder="e.g. Farmhouse Dining Table">
                            </div>

                            <div class="form-group">
                                <label for="category">Category <span class="req">*</span></label>
                                <input type="text" id="category" name="category" required
                                       value="<?= $v('category') ?>"
                                       list="cat-list" placeholder="e.g. Dining Tables">
                                <datalist id="cat-list">
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
                                <input type="text" id="price" name="price" required
                                       value="<?= $v('price') ?>"
                                       placeholder='$1,250  or  "Contact for Quote"'>
                            </div>

                            <div class="form-group form-group--full">
                                <label for="short_desc">Short Description <span class="req">*</span></label>
                                <input type="text" id="short_desc" name="short_desc" required
                                       value="<?= $v('short_description') ?>"
                                       placeholder="One sentence shown on product cards">
                            </div>

                            <div class="form-group form-group--full">
                                <label for="long_desc">Full Description</label>
                                <textarea id="long_desc" name="long_desc" rows="5"
                                          placeholder="Detailed description on the product page"><?= $v('long_description') ?></textarea>
                            </div>

                            <div class="form-group form-group--full">
                                <label for="materials">Materials <small>(comma-separated)</small></label>
                                <input type="text" id="materials" name="materials"
                                       value="<?= $v('materials') ?>"
                                       placeholder="White Oak, Linseed Oil, Steel Hardware">
                            </div>

                            <div class="form-group">
                                <label for="dimensions">Dimensions</label>
                                <input type="text" id="dimensions" name="dimensions"
                                       value="<?= $v('dimensions') ?>"
                                       placeholder='72"L × 36"W × 30"H'>
                            </div>

                            <div class="form-group">
                                <label for="lead_time">Lead Time</label>
                                <input type="text" id="lead_time" name="lead_time"
                                       value="<?= $v('lead_time') ?>"
                                       placeholder="4–6 weeks">
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <button type="submit" class="btn btn-primary btn-lg btn-block">
                    <?= $is_edit ? 'Save Changes' : 'Add Product' ?>
                </button>
                <a href="/admin/dashboard" class="btn btn-ghost btn-block" style="margin-top:8px">Cancel</a>

            </div><!-- /left -->

            <!-- ═══ RIGHT: Image manager ═══ -->
            <div>
                <div class="admin-card img-manager-card">
                    <div class="admin-card-header">
                        <h2>Images</h2>
                        <span class="img-count" id="imgCount">0 images</span>
                    </div>

                    <!-- Current images list -->
                    <ul class="img-list" id="imgList"></ul>

                    <!-- Upload zone -->
                    <div class="upload-zone" id="uploadZone">
                        <input type="file" id="fileInput" accept="image/jpeg,image/png,image/webp" multiple hidden>
                        <div id="uploadIdle">
                            <p class="uz-icon">📷</p>
                            <p><strong>Drop images here</strong> or</p>
                            <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('fileInput').click()">
                                Choose Files
                            </button>
                            <p class="uz-hint">JPEG · PNG · WebP · max 8 MB each</p>
                        </div>
                        <div id="uploadBusy" hidden>
                            <p id="uploadStatus">Uploading…</p>
                            <div class="uz-bar"><div class="uz-fill" id="uzFill"></div></div>
                        </div>
                    </div>

                    <!-- Add by URL -->
                    <div class="url-add-row">
                        <input type="url" id="urlInput" placeholder="https://… paste image URL" class="url-input">
                        <button type="button" class="btn btn-outline btn-sm" id="addUrlBtn">Add</button>
                    </div>

                    <p class="img-tip">First image = primary photo shown on product cards. Drag rows to reorder.</p>
                </div>
            </div><!-- /right -->

        </div><!-- /grid -->

    </form>

</main>

<script>
/* ── Image manager ─────────────────────────────────────────────────────────── */
(function () {
    'use strict';

    let imgs = <?= json_encode($existing_images, JSON_UNESCAPED_SLASHES) ?>;
    let dragging = null;

    const list       = document.getElementById('imgList');
    const countEl    = document.getElementById('imgCount');
    const jsonInput  = document.getElementById('imagesJson');
    const fileInput  = document.getElementById('fileInput');
    const uploadZone = document.getElementById('uploadZone');
    const uploadIdle = document.getElementById('uploadIdle');
    const uploadBusy = document.getElementById('uploadBusy');
    const statusEl   = document.getElementById('uploadStatus');
    const fillEl     = document.getElementById('uzFill');
    const urlInput   = document.getElementById('urlInput');
    const addUrlBtn  = document.getElementById('addUrlBtn');

    /* render */
    function render() {
        list.innerHTML = '';
        countEl.textContent = imgs.length + (imgs.length === 1 ? ' image' : ' images');
        jsonInput.value = JSON.stringify(imgs);

        imgs.forEach(function (src, i) {
            const li = document.createElement('li');
            li.className = 'img-row';
            li.draggable = true;
            li.dataset.i = i;

            li.innerHTML =
                '<span class="drag-handle" title="Drag to reorder">⠿</span>' +
                '<img class="img-row-thumb" src="' + esc(src) + '" ' +
                     'onerror="this.src=\'https://placehold.co/60x45/8B6343/F5ECD7?text=img\'" ' +
                     'width="60" height="45" alt="">' +
                '<span class="img-row-label" title="' + esc(src) + '">' + esc(short(src)) + '</span>' +
                (i === 0 ? '<span class="primary-badge">Primary</span>' : '') +
                '<button type="button" class="img-remove" data-i="' + i + '" title="Remove">✕</button>';

            /* remove */
            li.querySelector('.img-remove').addEventListener('click', function () {
                imgs.splice(parseInt(this.dataset.i), 1);
                render();
            });

            /* drag */
            li.addEventListener('dragstart', function (e) {
                dragging = parseInt(this.dataset.i);
                e.dataTransfer.effectAllowed = 'move';
                setTimeout(() => this.classList.add('dragging'), 0);
            });
            li.addEventListener('dragend', function () {
                this.classList.remove('dragging');
                dragging = null;
                document.querySelectorAll('.img-row').forEach(r => r.classList.remove('drag-over'));
            });
            li.addEventListener('dragover', function (e) {
                e.preventDefault();
                document.querySelectorAll('.img-row').forEach(r => r.classList.remove('drag-over'));
                this.classList.add('drag-over');
            });
            li.addEventListener('drop', function (e) {
                e.preventDefault();
                this.classList.remove('drag-over');
                const to = parseInt(this.dataset.i);
                if (dragging === null || dragging === to) return;
                const moved = imgs.splice(dragging, 1)[0];
                imgs.splice(to, 0, moved);
                render();
            });

            list.appendChild(li);
        });
    }

    /* file upload */
    fileInput.addEventListener('change', () => handleFiles(fileInput.files));

    uploadZone.addEventListener('dragover', function (e) {
        e.preventDefault();
        uploadZone.classList.add('dz-hover');
    });
    uploadZone.addEventListener('dragleave', () => uploadZone.classList.remove('dz-hover'));
    uploadZone.addEventListener('drop', function (e) {
        e.preventDefault();
        uploadZone.classList.remove('dz-hover');
        handleFiles(e.dataTransfer.files);
    });

    async function handleFiles(files) {
        const list = Array.from(files).filter(f => f.type.startsWith('image/'));
        if (!list.length) return;

        uploadIdle.hidden = true;
        uploadBusy.hidden = false;

        for (let i = 0; i < list.length; i++) {
            const pct = Math.round((i / list.length) * 100);
            fillEl.style.width = pct + '%';
            statusEl.textContent = 'Uploading ' + (i + 1) + ' of ' + list.length + '…';

            const url = await uploadFile(list[i]);
            if (url) { imgs.push(url); render(); }
        }

        fillEl.style.width = '100%';
        statusEl.textContent = 'Done!';
        await wait(700);
        uploadIdle.hidden = false;
        uploadBusy.hidden = true;
        fillEl.style.width = '0';
        fileInput.value = '';
    }

    async function uploadFile(file) {
        const fd = new FormData();
        fd.append('image', file);
        try {
            const r = await fetch('/api/upload', { method: 'POST', body: fd });
            const d = await r.json();
            if (d.success && d.url) return d.url;
            alert('Upload failed: ' + (d.error || 'Unknown error'));
        } catch (ex) {
            alert('Upload error: ' + ex.message);
        }
        return null;
    }

    /* add by URL */
    addUrlBtn.addEventListener('click', addUrl);
    urlInput.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); addUrl(); } });
    function addUrl() {
        const v = urlInput.value.trim();
        if (!v) return;
        imgs.push(v); render(); urlInput.value = '';
    }

    /* sync before submit */
    document.getElementById('productForm').addEventListener('submit', () => {
        jsonInput.value = JSON.stringify(imgs);
    });

    /* utils */
    function esc(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
    function short(s) {
        return s.length > 45 ? '…' + s.slice(-42) : s;
    }
    function wait(ms) { return new Promise(r => setTimeout(r, ms)); }

    render();
})();
</script>

</body>
</html>
