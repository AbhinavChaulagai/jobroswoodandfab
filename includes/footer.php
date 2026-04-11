<?php
/**
 * footer.php — Site-wide footer and closing HTML tags.
 */
?>

</main><!-- /#main-content -->

<!-- ===================== SITE FOOTER ===================== -->
<footer class="site-footer">
    <div class="container footer-grid">

        <!-- Brand column -->
        <div class="footer-brand">
            <p class="footer-logo-text">Jobros Wood &amp; Fab</p>
            <p class="footer-tagline">Handcrafted furniture built<br>to last generations.</p>
        </div>

        <!-- Quick links -->
        <nav class="footer-nav" aria-label="Footer navigation">
            <h3 class="footer-heading">Quick Links</h3>
            <ul>
                <li><a href="/index.php">Home</a></li>
                <li><a href="/products.php">Products</a></li>
                <li><a href="/about.php">About Us</a></li>
                <li><a href="/contact.php">Contact &amp; Quotes</a></li>
            </ul>
        </nav>

        <!-- Contact info -->
        <div class="footer-contact">
            <h3 class="footer-heading">Get in Touch</h3>
            <address>
                <p><a href="mailto:hello@jobroswoodandfab.com">hello@jobroswoodandfab.com</a></p>
                <p><a href="tel:+15154998920">(515) 499-8920</a></p>
                <p><a href="tel:+15156571983">(515) 657-1983</a></p>
                <p>Locally made in the Pacific Northwest</p>
            </address>
        </div>

    </div>

    <div class="footer-bottom">
        <div class="container">
            <p>&copy; <?= date('Y') ?> Jobros Wood &amp; Fab. All rights reserved.</p>
        </div>
    </div>
</footer>

<script src="/assets/js/main.js"></script>
</body>
</html>
