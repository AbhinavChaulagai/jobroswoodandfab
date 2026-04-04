<?php
/**
 * partials/sidebar.php — Shared admin sidebar navigation.
 * Expects auth_check.php to have already been included.
 */
$admin_page = basename($_SERVER['SCRIPT_NAME'], '.php');
?>
<aside class="admin-sidebar">
    <div class="sidebar-logo">
        <span class="sidebar-logo-name">Jobros</span>
        <span class="sidebar-logo-sub">Admin Panel</span>
    </div>

    <nav class="sidebar-nav" aria-label="Admin navigation">
        <a href="/admin/dashboard.php"    class="sidebar-link <?= $admin_page === 'dashboard'    ? 'active' : '' ?>">
            <span class="sidebar-icon" aria-hidden="true">&#9783;</span> Dashboard
        </a>
        <a href="/admin/edit.php"         class="sidebar-link <?= $admin_page === 'edit'          ? 'active' : '' ?>">
            <span class="sidebar-icon" aria-hidden="true">&#43;</span> Add Product
        </a>
        <a href="/admin/submissions.php"  class="sidebar-link <?= $admin_page === 'submissions'   ? 'active' : '' ?>">
            <span class="sidebar-icon" aria-hidden="true">&#9993;</span> Submissions
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="/" target="_blank" class="sidebar-link">
            <span class="sidebar-icon" aria-hidden="true">&#127968;</span> View Site
        </a>
        <a href="/admin/logout.php" class="sidebar-link sidebar-logout">
            <span class="sidebar-icon" aria-hidden="true">&#10006;</span> Log Out
        </a>
    </div>
</aside>
