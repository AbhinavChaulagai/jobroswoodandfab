<?php
/**
 * products.php — Full product grid with category filter.
 */
require_once __DIR__ . '/includes/functions.php';

$page_title       = 'Products';
$meta_description = 'Browse all custom wood furniture from Jobros Wood & Fab — dining tables, coffee tables, benches, bed frames, shelving, and side tables.';

$all_products = get_all_products();
$categories   = get_all_categories();

// Category filter from query string (optional)
$active_category = isset($_GET['category']) ? trim($_GET['category']) : '';

// Apply filter
if ($active_category !== '') {
    $display_products = get_products_by_category($active_category);
} else {
    $display_products = $all_products;
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- ===================== PAGE BANNER ===================== -->
<section class="page-banner" aria-label="Page title">
    <div class="container">
        <h1 class="page-banner-title">Our Products</h1>
        <p class="page-banner-sub">Every piece is made to order — these are examples of what we build.</p>
    </div>
</section>

<!-- ===================== CATEGORY FILTER ===================== -->
<section class="filter-bar" aria-label="Filter products by category">
    <div class="container filter-bar-inner">
        <a
            href="/products.php"
            class="filter-btn <?= $active_category === '' ? 'active' : '' ?>"
        >All</a>
        <?php foreach ($categories as $cat): ?>
        <a
            href="/products.php?category=<?= rawurlencode($cat) ?>"
            class="filter-btn <?= $active_category === $cat ? 'active' : '' ?>"
        ><?= e($cat) ?></a>
        <?php endforeach; ?>
    </div>
</section>

<!-- ===================== PRODUCT GRID ===================== -->
<section class="section products-section" aria-label="Product list">
    <div class="container">

        <?php if (empty($display_products)): ?>
            <p class="empty-message">No products found in that category. <a href="/products.php">View all</a>.</p>
        <?php else: ?>
        <p class="results-count">
            <?php if ($active_category !== ''): ?>
                Showing <?= count($display_products) ?> product<?= count($display_products) !== 1 ? 's' : '' ?> in <strong><?= e($active_category) ?></strong>
            <?php else: ?>
                Showing all <?= count($display_products) ?> products
            <?php endif; ?>
        </p>

        <div class="product-grid product-grid--large">
            <?php foreach ($display_products as $product): ?>
            <article class="product-card">
                <a href="/product.php?id=<?= (int)$product['id'] ?>" class="product-card-link" aria-label="View <?= e($product['name']) ?>">
                    <div class="product-card-img-wrap">
                        <img
                            src="<?= e(get_primary_image($product, 600, 450)) ?>"
                            alt="<?= e($product['name']) ?>"
                            loading="lazy"
                            width="600"
                            height="450"
                        >
                    </div>
                    <div class="product-card-body">
                        <span class="product-card-category"><?= e($product['category']) ?></span>
                        <h2 class="product-card-title"><?= e($product['name']) ?></h2>
                        <p class="product-card-desc"><?= e($product['short_description']) ?></p>
                        <p class="product-card-price"><?= e($product['price']) ?></p>
                        <span class="btn btn-sm btn-outline">View Details &rarr;</span>
                    </div>
                </a>
            </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </div>
</section>

<!-- ===================== CUSTOM ORDER PROMPT ===================== -->
<section class="cta-banner" aria-labelledby="products-cta-heading">
    <div class="container cta-banner-inner">
        <h2 id="products-cta-heading">Don't see exactly what you need?</h2>
        <p>We build fully custom pieces. Bring us your idea and we'll price it out for free.</p>
        <a href="/contact.php" class="btn btn-primary btn-lg">Request Custom Piece</a>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
