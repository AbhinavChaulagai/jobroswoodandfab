<?php
/**
 * about.php — Business story, craftsmanship philosophy, and team section.
 */
require_once __DIR__ . '/includes/functions.php';

$page_title       = 'About Us';
$meta_description = 'Learn the story behind Jobros Wood & Fab — a one-craftsman shop in the Pacific Northwest making heirloom-quality custom wood furniture.';

require_once __DIR__ . '/includes/header.php';
?>

<!-- ===================== PAGE BANNER ===================== -->
<section class="page-banner page-banner--about" aria-label="Page title">
    <div class="container">
        <h1 class="page-banner-title">Our Story</h1>
        <p class="page-banner-sub">A workbench, some hand tools, and a refusal to cut corners.</p>
    </div>
</section>

<!-- ===================== ORIGIN STORY ===================== -->
<section class="section about-story" aria-labelledby="story-heading">
    <div class="container about-story-inner">

        <div class="about-story-text">
            <h2 id="story-heading" class="section-title">How It Started</h2>
            <p>Jobros Wood &amp; Fab was born out of frustration with flat-pack furniture that wobbles, warps, and ends up at the curb inside two years. Founder Joe Brothers spent a decade building custom homes across the Pacific Northwest before he set up a small shop in his garage and started making furniture the same way he framed houses — overbuilt, honest, and meant to last.</p>
            <p>The first dining table he sold went to a neighbor. That neighbor told a friend. That friend told a colleague. Three years later, the garage became a proper workshop, and the waiting list grew from weeks to months.</p>
            <p>Every piece still goes through the same two sets of hands: Joe cuts and assembles, and his partner Maya handles finishing, staining, and final quality checks. That's it. No production line, no outsourced parts — just two people who care a lot about wood.</p>
        </div>

        <div class="about-story-img">
            <img
                src="https://placehold.co/640x480/6B4226/F5ECD7?text=The+Beginning"
                alt="Early days of the Jobros workshop"
                loading="lazy"
                width="640"
                height="480"
            >
        </div>

    </div>
</section>

<!-- ===================== CRAFTSMANSHIP VALUES ===================== -->
<section class="section craftsmanship-section" aria-labelledby="craft-heading">
    <div class="container">
        <h2 class="section-title text-center" id="craft-heading">How We Build</h2>
        <p class="section-subtitle text-center">The details that separate furniture you keep forever from furniture you replace.</p>

        <div class="values-grid">

            <div class="value-card">
                <div class="value-icon" aria-hidden="true">&#9834;</div>
                <h3>Solid Wood Only</h3>
                <p>We don't use plywood cores, MDF, or veneers in structural areas. Every leg, top, and apron is solid hardwood — the same species all the way through.</p>
            </div>

            <div class="value-card">
                <div class="value-icon" aria-hidden="true">&#9998;</div>
                <h3>Traditional Joinery</h3>
                <p>Mortise-and-tenon, dovetail, and wedged joints. Furniture joinery has been refined over centuries — we see no reason to swap it for pocket screws and glue.</p>
            </div>

            <div class="value-card">
                <div class="value-icon" aria-hidden="true">&#9670;</div>
                <h3>Hand-Rubbed Finishes</h3>
                <p>Oil, wax, and poly finishes are all applied by hand in multiple coats. No spray-gun shortcuts. You'll see the depth in the finish that only comes from time.</p>
            </div>

            <div class="value-card">
                <div class="value-icon" aria-hidden="true">&#9775;</div>
                <h3>Sustainably Sourced</h3>
                <p>All our lumber comes from FSC-certified sawyers or reclaimed sources. We choose domestic species whenever possible — walnut, oak, ash, cherry, pine.</p>
            </div>

            <div class="value-card">
                <div class="value-icon" aria-hidden="true">&#9728;</div>
                <h3>Kiln-Dried Lumber</h3>
                <p>Every board is kiln-dried to 6–8% moisture content before it enters the shop. That means no seasonal warping, no checking, no surprises after delivery.</p>
            </div>

            <div class="value-card">
                <div class="value-icon" aria-hidden="true">&#10003;</div>
                <h3>Lifetime Guarantee</h3>
                <p>If a joint fails due to craftsmanship — not abuse — we'll repair or replace it. No time limit, no hassle. That's what standing behind your work means.</p>
            </div>

        </div>
    </div>
</section>

<!-- ===================== PROCESS TIMELINE ===================== -->
<section class="section process-section" aria-labelledby="process-heading">
    <div class="container">
        <h2 class="section-title text-center" id="process-heading">From Idea to Delivery</h2>
        <ol class="process-steps" aria-label="Our build process">
            <li class="process-step">
                <span class="step-number" aria-hidden="true">01</span>
                <div class="step-content">
                    <h3>Consultation</h3>
                    <p>We talk through your space, your needs, and your aesthetic. You bring photos, sketches, or just a description. We figure out what works.</p>
                </div>
            </li>
            <li class="process-step">
                <span class="step-number" aria-hidden="true">02</span>
                <div class="step-content">
                    <h3>Quote &amp; Design</h3>
                    <p>We send a detailed written quote and a simple line drawing so you know exactly what you're getting. No hidden upgrades.</p>
                </div>
            </li>
            <li class="process-step">
                <span class="step-number" aria-hidden="true">03</span>
                <div class="step-content">
                    <h3>Lumber Selection</h3>
                    <p>We source and select boards specifically for your piece, looking for the grain pattern and character that matches the design.</p>
                </div>
            </li>
            <li class="process-step">
                <span class="step-number" aria-hidden="true">04</span>
                <div class="step-content">
                    <h3>Build &amp; Finish</h3>
                    <p>Your piece is built and finished in our shop. Lead times run 3–8 weeks depending on complexity. We send progress photos.</p>
                </div>
            </li>
            <li class="process-step">
                <span class="step-number" aria-hidden="true">05</span>
                <div class="step-content">
                    <h3>Delivery &amp; Setup</h3>
                    <p>We deliver locally and can ship nationwide for large pieces. White-glove delivery and in-home setup available in the greater PNW area.</p>
                </div>
            </li>
        </ol>
    </div>
</section>

<!-- ===================== CTA ===================== -->
<section class="cta-banner" aria-labelledby="about-cta-heading">
    <div class="container cta-banner-inner">
        <h2 id="about-cta-heading">Ready to start your project?</h2>
        <p>Reach out — consultations are free and there's no pressure.</p>
        <a href="/contact.php" class="btn btn-primary btn-lg">Get in Touch</a>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
