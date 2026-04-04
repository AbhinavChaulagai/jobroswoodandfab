<?php
/**
 * products.php — Product listing page with category filter.
 */
require_once __DIR__ . '/includes/functions.php';

$page_title       = 'Our Products';
$meta_description = 'Browse all custom wood furniture — dining tables, coffee tables, benches, bed frames, shelving, and side tables.';

$all_products = get_all_products();
$categories   = get_all_categories();

$active_cat       = trim($_GET['category'] ?? '');
$display_products = $active_cat !== '' ? get_products_by_category($active_cat) : $all_products;

require_once __DIR__ . '/includes/header.php';
?>

<!-- Page banner -->
<section class="page-banner">
    <div class="container">
        <h1 class="page-banner-title">Our Products</h1>
        <p class="page-banner-sub">Every piece is made to order. These are examples of what we build.</p>
    </div>
</section>

<!-- Category filter -->
<div class="filter-bar">
    <div class="container filter-bar-inner">
        <a href="/products" class="filter-btn <?= $active_cat === '' ? 'active' : '' ?>">All</a>
        <?php foreach ($categories as $cat): ?>
        <a href="/products?category=<?= rawurlencode($cat) ?>"
           class="filter-btn <?= $active_cat === $cat ? 'active' : '' ?>">
            <?= htmlspecialchars($cat) ?>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- Product grid -->
<section class="section products-section">
    <div class="container">

        <?php if (empty($display_products)): ?>
            <p class="empty-message">No products in this category. <a href="/products">View all</a>.</p>
        <?php else: ?>

        <p class="results-count">
            <?php if ($active_cat): ?>
                Showing <?= count($display_products) ?> in <strong><?= htmlspecialchars($active_cat) ?></strong>
            <?php else: ?>
                Showing all <?= count($display_products) ?> products
            <?php endif; ?>
        </p>

        <div class="product-grid product-grid--large">
            <?php foreach ($display_products as $p): ?>
            <article class="product-card">
                <a href="/product/<?= (int)$p['id'] ?>" class="product-card-link">

                    <div class="product-card-img-wrap">
                        <img
                            src="<?= htmlspecialchars(get_primary_image($p, 600, 450)) ?>"
                            alt="<?= htmlspecialchars($p['name']) ?>"
                            loading="lazy" width="600" height="450"
                        >
                        <?php $imgCount = count(get_product_images($p));
                              if ($imgCount > 1): ?>
                        <span class="img-count-pill"><?= $imgCount ?> photos</span>
                        <?php endif; ?>
                    </div>

                    <div class="product-card-body">
                        <span class="product-card-category"><?= htmlspecialchars($p['category']) ?></span>
                        <h2 class="product-card-title"><?= htmlspecialchars($p['name']) ?></h2>
                        <p class="product-card-desc"><?= htmlspecialchars($p['short_description']) ?></p>
                        <div class="product-card-footer">
                            <span class="product-card-price"><?= htmlspecialchars($p['price']) ?></span>
                            <span class="btn btn-sm btn-outline">View Details →</span>
                        </div>
                    </div>

                </a>
            </article>
            <?php endforeach; ?>
        </div>

        <?php endif; ?>
    </div>
</section>

<!-- CTA -->
<section class="cta-banner">
    <div class="container cta-banner-inner">
        <h2>Don't see exactly what you need?</h2>
        <p>We build fully custom pieces. Describe your idea and we'll price it for free.</p>
        <a href="/contact" class="btn btn-primary btn-lg">Request a Custom Piece</a>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
