<?php
/**
 * contact.php — Contact / quote request form page.
 */
require_once __DIR__ . '/includes/functions.php';

$page_title       = 'Contact & Quote Request';
$meta_description = 'Get in touch with Jobros Wood & Fab to request a quote for custom wood furniture or ask any questions.';

// Pre-fill product field if passed via query string (from product detail page)
$prefill_product = isset($_GET['product']) ? trim($_GET['product']) : '';

require_once __DIR__ . '/includes/header.php';
?>

<!-- ===================== PAGE BANNER ===================== -->
<section class="page-banner" aria-label="Page title">
    <div class="container">
        <h1 class="page-banner-title">Contact &amp; Quotes</h1>
        <p class="page-banner-sub">Tell us what you have in mind — we'll respond within one business day.</p>
    </div>
</section>

<!-- ===================== CONTACT LAYOUT ===================== -->
<section class="section contact-section" aria-labelledby="contact-form-heading">
    <div class="container contact-layout">

        <!-- LEFT — Form -->
        <div class="contact-form-wrap">
            <h2 id="contact-form-heading" class="section-title-sm">Send Us a Message</h2>

            <!-- Success / error messages injected by JS after fetch -->
            <div id="form-feedback" role="alert" aria-live="polite"></div>

            <form
                id="contactForm"
                class="contact-form"
                action="/api/contact"
                method="POST"
                novalidate
            >
                <!-- Honeypot anti-spam field — hidden from real users -->
                <div style="display:none" aria-hidden="true">
                    <label for="website">Leave this blank</label>
                    <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                </div>

                <div class="form-row form-row--half">
                    <div class="form-group">
                        <label for="name">Your Name <span class="required" aria-hidden="true">*</span></label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            required
                            autocomplete="name"
                            placeholder="Jane Smith"
                        >
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address <span class="required" aria-hidden="true">*</span></label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            required
                            autocomplete="email"
                            placeholder="jane@example.com"
                        >
                    </div>
                </div>

                <div class="form-row form-row--half">
                    <div class="form-group">
                        <label for="phone">Phone (optional)</label>
                        <input
                            type="tel"
                            id="phone"
                            name="phone"
                            autocomplete="tel"
                            placeholder="(555) 555-0100"
                        >
                    </div>
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input
                            type="text"
                            id="subject"
                            name="subject"
                            placeholder="Custom dining table quote"
                            value="<?= $prefill_product !== '' ? e('Quote request: ' . $prefill_product) : '' ?>"
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="message">Message <span class="required" aria-hidden="true">*</span></label>
                    <textarea
                        id="message"
                        name="message"
                        required
                        rows="6"
                        placeholder="Describe what you're looking for — dimensions, wood species preferences, finish ideas, timeline, etc."
                    ></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                    Send Message
                </button>
                <p class="form-note">Fields marked <span class="required">*</span> are required.</p>
            </form>
        </div>

        <!-- RIGHT — Contact info & map placeholder -->
        <aside class="contact-info" aria-label="Contact information">
            <h2 class="section-title-sm">Find Us</h2>

            <div class="contact-info-block">
                <h3>Email</h3>
                <p><a href="mailto:jobroswoodandfab@gmail.com">jobroswoodandfab@gmail.com</a></p>
            </div>

            <div class="contact-info-block">
                <h3>Phone</h3>
                <p><a href="tel:+15154998920">(515) 499-8920</a></p>
                <p><a href="tel:+15156571983">(515) 657-1983</a></p>
                <p class="contact-hours">Mon–Fri, 8 am – 5 pm</p>
            </div>

            <div class="contact-info-block">
                <h3>Workshop</h3>
                <!-- TODO: replace with your city/state -->
                <address>
                    <p>Pacific Northwest, USA</p>
                    <p><em>By appointment only</em></p>
                </address>
            </div>

            <div class="contact-info-block">
                <h3>Response Time</h3>
                <p>We respond to all inquiries within one business day. For urgent requests, please call.</p>
            </div>
        </aside>

    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
