<?php
/**
 * product.php — Single product detail page with image gallery.
 */
require_once __DIR__ . '/includes/functions.php';

$id      = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = $id > 0 ? get_product_by_id($id) : null;

if (!$product) {
    header('HTTP/1.1 404 Not Found');
    $page_title = 'Product Not Found';
    require_once __DIR__ . '/includes/header.php';
    echo '<section class="page-banner page-banner--error"><div class="container">
          <h1 class="page-banner-title">Product Not Found</h1>
          <p><a href="/products">← Browse all products</a></p>
          </div></section>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$page_title       = $product['name'];
$meta_description = $product['short_description'];

// Build gallery array: resolve each image to a full URL
$raw    = get_product_images($product);
$photos = [];
foreach ($raw as $img) {
    $photos[] = [
        'full'  => product_image_url($img, 900, 675),
        'thumb' => product_image_url($img, 120, 90),
    ];
}
if (empty($photos)) {
    $photos[] = [
        'full'  => product_image_url('placeholder.jpg', 900, 675),
        'thumb' => product_image_url('placeholder.jpg', 120, 90),
    ];
}

// Related products (same category, excluding current)
$related = array_slice(
    array_values(array_filter(
        get_products_by_category($product['category']),
        fn($p) => (int)$p['id'] !== $id
    )),
    0, 3
);

require_once __DIR__ . '/includes/header.php';
?>

<!-- Breadcrumb -->
<nav class="breadcrumb">
    <div class="container">
        <ol class="breadcrumb-list">
            <li><a href="/">Home</a></li>
            <li><a href="/products">Products</a></li>
            <li><a href="/products?category=<?= rawurlencode($product['category']) ?>"><?= htmlspecialchars($product['category']) ?></a></li>
            <li aria-current="page"><?= htmlspecialchars($product['name']) ?></li>
        </ol>
    </div>
</nav>

<!-- Product detail -->
<section class="section product-detail">
    <div class="container product-detail-inner">

        <!-- ── Gallery ── -->
        <div class="gallery" id="gallery">

            <!-- Main image -->
            <div class="gallery-main">
                <img id="galleryMain"
                     src="<?= htmlspecialchars($photos[0]['full']) ?>"
                     alt="<?= htmlspecialchars($product['name']) ?>"
                     width="900" height="675">

                <?php if (count($photos) > 1): ?>
                <button class="g-arrow g-prev" id="gPrev" aria-label="Previous">&#8249;</button>
                <button class="g-arrow g-next" id="gNext" aria-label="Next">&#8250;</button>
                <?php endif; ?>
            </div>

            <!-- Thumbnails -->
            <?php if (count($photos) > 1): ?>
            <div class="gallery-thumbs">
                <?php foreach ($photos as $i => $ph): ?>
                <button class="g-thumb <?= $i === 0 ? 'active' : '' ?>"
                        data-full="<?= htmlspecialchars($ph['full']) ?>"
                        data-i="<?= $i ?>"
                        aria-label="Photo <?= $i + 1 ?>">
                    <img src="<?= htmlspecialchars($ph['thumb']) ?>"
                         alt="Thumbnail <?= $i + 1 ?>"
                         loading="lazy" width="120" height="90">
                </button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        </div><!-- /gallery -->

        <!-- ── Product info ── -->
        <div class="product-detail-info">

            <span class="product-card-category"><?= htmlspecialchars($product['category']) ?></span>
            <h1 class="product-detail-title"><?= htmlspecialchars($product['name']) ?></h1>
            <p class="product-detail-price"><?= htmlspecialchars($product['price']) ?></p>
            <p class="product-detail-short-desc"><?= htmlspecialchars($product['short_description']) ?></p>

            <!-- Specs -->
            <div class="product-specs">
                <?php if (!empty($product['dimensions'])): ?>
                <div class="spec-row">
                    <span class="spec-label">Dimensions</span>
                    <span class="spec-value"><?= htmlspecialchars($product['dimensions']) ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($product['materials'])): ?>
                <div class="spec-row">
                    <span class="spec-label">Materials</span>
                    <span class="spec-value"><?= htmlspecialchars(implode(', ', $product['materials'])) ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($product['lead_time'])): ?>
                <div class="spec-row">
                    <span class="spec-label">Lead Time</span>
                    <span class="spec-value"><?= htmlspecialchars($product['lead_time']) ?></span>
                </div>
                <?php endif; ?>
            </div>

            <div class="product-detail-actions">
                <a href="/contact?product=<?= rawurlencode($product['name']) ?>"
                   class="btn btn-primary btn-lg">Request a Quote</a>
                <a href="/products" class="btn btn-outline">← All Products</a>
            </div>

        </div><!-- /info -->
    </div>

    <!-- Long description -->
    <?php if (!empty($product['long_description'])): ?>
    <div class="container product-long-desc">
        <h2 class="section-title-sm">About This Piece</h2>
        <p><?= nl2br(htmlspecialchars($product['long_description'])) ?></p>
    </div>
    <?php endif; ?>
</section>

<!-- Related products -->
<?php if (!empty($related)): ?>
<section class="section related-section">
    <div class="container">
        <h2 class="section-title">More <?= htmlspecialchars($product['category']) ?></h2>
        <div class="product-grid">
            <?php foreach ($related as $r): ?>
            <article class="product-card">
                <a href="/product?id=<?= (int)$r['id'] ?>" class="product-card-link">
                    <div class="product-card-img-wrap">
                        <img src="<?= htmlspecialchars(get_primary_image($r, 600, 450)) ?>"
                             alt="<?= htmlspecialchars($r['name']) ?>"
                             loading="lazy" width="600" height="450">
                    </div>
                    <div class="product-card-body">
                        <span class="product-card-category"><?= htmlspecialchars($r['category']) ?></span>
                        <h3 class="product-card-title"><?= htmlspecialchars($r['name']) ?></h3>
                        <p class="product-card-price"><?= htmlspecialchars($r['price']) ?></p>
                        <span class="btn btn-sm btn-outline">View Details →</span>
                    </div>
                </a>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Gallery data for JS -->
<script>
window.GALLERY = <?= json_encode(array_column($photos, 'full'), JSON_UNESCAPED_SLASHES) ?>;
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
