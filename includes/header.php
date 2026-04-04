<?php
/**
 * header.php — Site-wide HTML <head> and navigation.
 * Expects $page_title to be set by the including file.
 */
$site_name  = 'Jobros Wood & Fab';
$page_title = isset($page_title) ? e($page_title) . ' | ' . $site_name : $site_name;

// Determine the current page for nav active states
$current_page = basename($_SERVER['SCRIPT_NAME'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= isset($meta_description) ? e($meta_description) : 'Jobros Wood &amp; Fab — handcrafted custom wood furniture built to last generations.' ?>">
    <title><?= $page_title ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<!-- ===================== SITE HEADER / NAV ===================== -->
<header class="site-header">
    <div class="container header-inner">

        <!-- Logo / Wordmark -->
        <a href="/index.php" class="logo" aria-label="<?= $site_name ?> — Home">
            <span class="logo-icon" aria-hidden="true">&#9776;</span><!-- decorative lumber glyph -->
            <span class="logo-text">
                <span class="logo-name">Jobros</span>
                <span class="logo-sub">Wood &amp; Fab</span>
            </span>
        </a>

        <!-- Hamburger toggle (mobile) -->
        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation" aria-expanded="false" aria-controls="primaryNav">
            <span class="hamburger-bar"></span>
            <span class="hamburger-bar"></span>
            <span class="hamburger-bar"></span>
        </button>

        <!-- Primary navigation -->
        <nav class="primary-nav" id="primaryNav" aria-label="Primary navigation">
            <ul class="nav-list">
                <li class="nav-item <?= $current_page === 'index'    ? 'active' : '' ?>">
                    <a href="/index.php">Home</a>
                </li>
                <li class="nav-item <?= $current_page === 'products' ? 'active' : '' ?>">
                    <a href="/products.php">Products</a>
                </li>
                <li class="nav-item <?= $current_page === 'about'    ? 'active' : '' ?>">
                    <a href="/about.php">About</a>
                </li>
                <li class="nav-item <?= $current_page === 'contact'  ? 'active' : '' ?>">
                    <a href="/contact.php" class="nav-cta">Get a Quote</a>
                </li>
            </ul>
        </nav>

    </div>
</header>

<!-- Page content begins below -->
<main id="main-content">
