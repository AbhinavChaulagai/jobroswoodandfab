<?php
/**
 * product.php — Single product detail page with image gallery.
 */
require_once __DIR__ . '/includes/functions.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product    = $product_id > 0 ? get_product_by_id($product_id) : null;

if ($product === null) {
    header('HTTP/1.1 404 Not Found');
    $page_title = 'Product Not Found';
    require_once __DIR__ . '/includes/header.php';
    ?>
    <section class="page-banner page-banner--error">
        <div class="container">
            <h1 class="page-banner-title">Product Not Found</h1>
            <p>We couldn't find that product. <a href="/products">Browse all products &rarr;</a></p>
        </div>
    </section>
    <?php
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$page_title       = $product['name'];
$meta_description = $product['short_description'];

// Build gallery: resolve every image to a full URL now
$raw_images = get_product_images($product);
$gallery    = [];
foreach ($raw_images as $img) {
    $gallery[] = [
        'src'   => product_image_url($img, 900, 675),
        'thumb' => product_image_url($img, 160, 120),
        'alt'   => $product['name'],
    ];
}
if (empty($gallery)) {
    $gallery[] = [
        'src'   => product_image_url('placeholder.jpg', 900, 675),
        'thumb' => product_image_url('placeholder.jpg', 160, 120),
        'alt'   => $product['name'],
    ];
}

$related = array_filter(
    get_products_by_category($product['category']),
    fn($p) => (int)$p['id'] !== $product_id
);
$related = array_slice(array_values($related), 0, 3);

require_once __DIR__ . '/includes/header.php';
?>

<!-- ===================== BREADCRUMB ===================== -->
<nav class="breadcrumb" aria-label="Breadcrumb">
    <div class="container">
        <ol class="breadcrumb-list">
            <li><a href="/">Home</a></li>
            <li><a href="/products">Products</a></li>
            <li><a href="/products?category=<?= rawurlencode($product['category']) ?>"><?= e($product['category']) ?></a></li>
            <li aria-current="page"><?= e($product['name']) ?></li>
        </ol>
    </div>
</nav>

<!-- ===================== PRODUCT DETAIL ===================== -->
<section class="section product-detail" aria-labelledby="product-name">
    <div class="container product-detail-inner">

        <!-- ── Gallery ── -->
        <div class="gallery" id="productGallery">

            <!-- Main image -->
            <div class="gallery-main">
                <img
                    id="galleryMain"
                    src="<?= e($gallery[0]['src']) ?>"
                    alt="<?= e($gallery[0]['alt']) ?>"
                    width="900"
                    height="675"
                >
                <?php if (count($gallery) > 1): ?>
                <!-- Prev / Next arrows -->
                <button class="gallery-arrow gallery-prev" id="galleryPrev" aria-label="Previous image">&#8249;</button>
                <button class="gallery-arrow gallery-next" id="galleryNext" aria-label="Next image">&#8250;</button>
                <?php endif; ?>
            </div>

            <!-- Thumbnails -->
            <?php if (count($gallery) > 1): ?>
            <div class="gallery-thumbs" id="galleryThumbs" role="list" aria-label="Product images">
                <?php foreach ($gallery as $i => $img): ?>
                <button
                    class="gallery-thumb <?= $i === 0 ? 'active' : '' ?>"
                    data-src="<?= e($img['src']) ?>"
                    data-index="<?= $i ?>"
                    role="listitem"
                    aria-label="View image <?= $i + 1 ?>"
                >
                    <img src="<?= e($img['thumb']) ?>" alt="<?= e($img['alt']) ?> thumbnail" loading="lazy" width="160" height="120">
                </button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        </div>

        <!-- ── Product info ── -->
        <div class="product-detail-info">
            <span class="product-card-category"><?= e($product['category']) ?></span>
            <h1 id="product-name" class="product-detail-title"><?= e($product['name']) ?></h1>
            <p class="product-detail-price"><?= e($product['price']) ?></p>
            <p class="product-detail-short-desc"><?= e($product['short_description']) ?></p>

            <div class="product-specs">
                <?php if (!empty($product['dimensions'])): ?>
                <div class="spec-row">
                    <span class="spec-label">Dimensions</span>
                    <span class="spec-value"><?= e($product['dimensions']) ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($product['materials'])): ?>
                <div class="spec-row">
                    <span class="spec-label">Materials</span>
                    <span class="spec-value"><?= e(implode(', ', $product['materials'])) ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($product['lead_time'])): ?>
                <div class="spec-row">
                    <span class="spec-label">Lead Time</span>
                    <span class="spec-value"><?= e($product['lead_time']) ?></span>
                </div>
                <?php endif; ?>
            </div>

            <div class="product-detail-actions">
                <a href="/contact?product=<?= rawurlencode($product['name']) ?>" class="btn btn-primary btn-lg">
                    Request a Quote
                </a>
                <a href="/products" class="btn btn-outline">&larr; All Products</a>
            </div>
        </div>
    </div>

    <!-- Long description -->
    <div class="container product-long-desc">
        <h2 class="section-title-sm">About This Piece</h2>
        <p><?= nl2br(e($product['long_description'])) ?></p>
    </div>
</section>

<!-- ===================== RELATED PRODUCTS ===================== -->
<?php if (!empty($related)): ?>
<section class="section related-section" aria-labelledby="related-heading">
    <div class="container">
        <h2 class="section-title" id="related-heading">More <?= e($product['category']) ?></h2>
        <div class="product-grid">
            <?php foreach ($related as $rel): ?>
            <article class="product-card">
                <a href="/product?id=<?= (int)$rel['id'] ?>" class="product-card-link" aria-label="View <?= e($rel['name']) ?>">
                    <div class="product-card-img-wrap">
                        <img
                            src="<?= e(get_primary_image($rel, 600, 450)) ?>"
                            alt="<?= e($rel['name']) ?>"
                            loading="lazy"
                            width="600"
                            height="450"
                        >
                    </div>
                    <div class="product-card-body">
                        <span class="product-card-category"><?= e($rel['category']) ?></span>
                        <h3 class="product-card-title"><?= e($rel['name']) ?></h3>
                        <p class="product-card-price"><?= e($rel['price']) ?></p>
                        <span class="btn btn-sm btn-outline">View Details &rarr;</span>
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
window.GALLERY_IMAGES = <?= json_encode(array_column($gallery, 'src'), JSON_UNESCAPED_SLASHES) ?>;
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
