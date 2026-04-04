<?php
$cur = basename($_SERVER['SCRIPT_NAME'], '.php');
?>
<aside class="admin-sidebar">
    <div class="sidebar-logo">
        <span class="sidebar-name">Jobros</span>
        <span class="sidebar-sub">Admin Panel</span>
    </div>
    <nav class="sidebar-nav">
        <a href="/admin/dashboard"   class="sidebar-link <?= $cur === 'dashboard'   ? 'active' : '' ?>">
            🗂 Dashboard
        </a>
        <a href="/admin/product"     class="sidebar-link <?= $cur === 'product'     ? 'active' : '' ?>">
            ➕ Add Product
        </a>
        <a href="/admin/submissions" class="sidebar-link <?= $cur === 'submissions' ? 'active' : '' ?>">
            ✉ Quote Requests
        </a>
    </nav>
    <div class="sidebar-footer">
        <a href="/" target="_blank" class="sidebar-link">🏠 View Site</a>
        <a href="/admin/logout"      class="sidebar-link sidebar-logout">✕ Log Out</a>
    </div>
</aside>
