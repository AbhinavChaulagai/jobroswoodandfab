<?php
/**
 * index.php — Homepage: hero, featured products, about blurb, CTA.
 */
require_once __DIR__ . '/includes/functions.php';

$page_title       = 'Handcrafted Custom Wood Furniture';
$meta_description = 'Jobros Wood & Fab builds heirloom-quality custom wood furniture by hand in the Pacific Northwest. Dining tables, live-edge slabs, benches, and more.';

// Pull first 3 products as "featured"
$all_products      = get_all_products();
$featured_products = array_slice($all_products, 0, 3);

require_once __DIR__ . '/includes/header.php';
?>

<!-- ===================== HERO ===================== -->
<section class="hero" aria-label="Hero banner">
    <div class="hero-overlay"></div>
    <div class="container hero-content">
        <p class="hero-eyebrow">Handcrafted in the Pacific Northwest</p>
        <h1 class="hero-title">Furniture Built<br>to Last Generations</h1>
        <p class="hero-subtitle">Every piece starts as a raw slab and ends as an heirloom. Custom sizes, custom finishes — built the way it used to be done.</p>
        <div class="hero-actions">
            <a href="/products.php" class="btn btn-primary">Browse Products</a>
            <a href="/contact.php" class="btn btn-outline">Request a Quote</a>
        </div>
    </div>
    <!-- Decorative wood-grain texture bars -->
    <div class="hero-grain" aria-hidden="true"></div>
</section>

<!-- ===================== TRUST BAR ===================== -->
<section class="trust-bar" aria-label="Key features">
    <div class="container trust-bar-grid">
        <div class="trust-item">
            <span class="trust-icon" aria-hidden="true">&#9998;</span>
            <div>
                <strong>Custom Made</strong>
                <p>Every piece built to your spec</p>
            </div>
        </div>
        <div class="trust-item">
            <span class="trust-icon" aria-hidden="true">&#9753;</span>
            <div>
                <strong>Solid Hardwoods</strong>
                <p>No veneers, no shortcuts</p>
            </div>
        </div>
        <div class="trust-item">
            <span class="trust-icon" aria-hidden="true">&#10003;</span>
            <div>
                <strong>Lifetime Guarantee</strong>
                <p>We stand behind our joinery</p>
            </div>
        </div>
        <div class="trust-item">
            <span class="trust-icon" aria-hidden="true">&#9787;</span>
            <div>
                <strong>Small-Batch Shop</strong>
                <p>One craftsman, full attention</p>
            </div>
        </div>
    </div>
</section>

<!-- ===================== FEATURED PRODUCTS ===================== -->
<section class="section featured-section" aria-labelledby="featured-heading">
    <div class="container">
        <h2 class="section-title" id="featured-heading">Featured Work</h2>
        <p class="section-subtitle">A few pieces that left the shop recently — each one a little different.</p>

        <div class="product-grid">
            <?php foreach ($featured_products as $product): ?>
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
                        <h3 class="product-card-title"><?= e($product['name']) ?></h3>
                        <p class="product-card-desc"><?= e($product['short_description']) ?></p>
                        <p class="product-card-price"><?= e($product['price']) ?></p>
                        <span class="btn btn-sm btn-outline">View Details &rarr;</span>
                    </div>
                </a>
            </article>
            <?php endforeach; ?>
        </div>

        <div class="section-cta">
            <a href="/products.php" class="btn btn-primary">See All Products</a>
        </div>
    </div>
</section>

<!-- ===================== ABOUT BLURB ===================== -->
<section class="section about-blurb" aria-labelledby="about-blurb-heading">
    <div class="container about-blurb-inner">
        <div class="about-blurb-img">
            <img
                src="https://placehold.co/640x480/6B4226/F5ECD7?text=The+Shop"
                alt="Inside the Jobros Wood & Fab workshop"
                loading="lazy"
                width="640"
                height="480"
            >
        </div>
        <div class="about-blurb-text">
            <h2 class="section-title" id="about-blurb-heading">Built by Hand.<br>Meant to Stay.</h2>
            <p>Jobros Wood &amp; Fab started with a workbench, a few hand tools, and a belief that furniture should outlast the people who buy it. Every joint is cut, every surface is planed, and every finish is rubbed on by hand.</p>
            <p>We work with sustainably sourced domestic hardwoods — white oak, black walnut, cherry, ash, and reclaimed pine — and we'll never use a veneer or a short-cut when the real thing is an option.</p>
            <a href="/about.php" class="btn btn-primary">Our Story</a>
        </div>
    </div>
</section>

<!-- ===================== CTA BANNER ===================== -->
<section class="cta-banner" aria-labelledby="cta-heading">
    <div class="container cta-banner-inner">
        <h2 id="cta-heading">Have something in mind?</h2>
        <p>Bring us your sketch, a napkin drawing, or just a description — we'll make it real.</p>
        <a href="/contact.php" class="btn btn-primary btn-lg">Start a Conversation</a>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
