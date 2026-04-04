<?php
/**
 * product.php — Single product detail page.
 * Usage: /product.php?id=1
 */
require_once __DIR__ . '/includes/functions.php';

// Validate the ID from the query string
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product    = $product_id > 0 ? get_product_by_id($product_id) : null;

// 404 handling — redirect to products page with a message
if ($product === null) {
    header('HTTP/1.1 404 Not Found');
    $page_title       = 'Product Not Found';
    $meta_description = 'The requested product could not be found.';
    require_once __DIR__ . '/includes/header.php';
    ?>
    <section class="page-banner page-banner--error">
        <div class="container">
            <h1 class="page-banner-title">Product Not Found</h1>
            <p>We couldn't find that product. <a href="/products.php">Browse all products &rarr;</a></p>
        </div>
    </section>
    <?php
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$page_title       = $product['name'];
$meta_description = $product['short_description'];

// Get related products (same category, excluding current)
$related = array_filter(
    get_products_by_category($product['category']),
    fn($p) => (int)$p['id'] !== $product_id
);
$related = array_slice(array_values($related), 0, 2);

require_once __DIR__ . '/includes/header.php';
?>

<!-- ===================== BREADCRUMB ===================== -->
<nav class="breadcrumb" aria-label="Breadcrumb">
    <div class="container">
        <ol class="breadcrumb-list">
            <li><a href="/index.php">Home</a></li>
            <li><a href="/products.php">Products</a></li>
            <li><a href="/products.php?category=<?= rawurlencode($product['category']) ?>"><?= e($product['category']) ?></a></li>
            <li aria-current="page"><?= e($product['name']) ?></li>
        </ol>
    </div>
</nav>

<!-- ===================== PRODUCT DETAIL ===================== -->
<section class="section product-detail" aria-labelledby="product-name">
    <div class="container product-detail-inner">

        <!-- Product image -->
        <div class="product-detail-gallery">
            <img
                src="<?= e(product_image_url($product['image'], 800, 600)) ?>"
                alt="<?= e($product['name']) ?>"
                width="800"
                height="600"
                class="product-detail-main-img"
            >
        </div>

        <!-- Product info -->
        <div class="product-detail-info">
            <span class="product-card-category"><?= e($product['category']) ?></span>
            <h1 id="product-name" class="product-detail-title"><?= e($product['name']) ?></h1>
            <p class="product-detail-price"><?= e($product['price']) ?></p>

            <p class="product-detail-short-desc"><?= e($product['short_description']) ?></p>

            <!-- Specs table -->
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

            <!-- CTA buttons -->
            <div class="product-detail-actions">
                <a href="/contact.php?product=<?= rawurlencode($product['name']) ?>" class="btn btn-primary btn-lg">
                    Request a Quote
                </a>
                <a href="/products.php" class="btn btn-outline">
                    &larr; Back to Products
                </a>
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
                <a href="/product.php?id=<?= (int)$rel['id'] ?>" class="product-card-link" aria-label="View <?= e($rel['name']) ?>">
                    <div class="product-card-img-wrap">
                        <img
                            src="<?= e(product_image_url($rel['image'], 600, 450)) ?>"
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
