<?php
/**
 * header.php — Site-wide HTML <head> and navigation.
 */
$site_name    = 'Jobros Wood & Fab';
$page_title   = isset($page_title) ? e($page_title) . ' | ' . $site_name : $site_name;
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

<header class="site-header">
    <div class="container header-inner">

        <a href="/" class="logo" aria-label="<?= $site_name ?> — Home">
            <span class="logo-icon" aria-hidden="true">&#9776;</span>
            <span class="logo-text">
                <span class="logo-name">Jobros</span>
                <span class="logo-sub">Wood &amp; Fab</span>
            </span>
        </a>

        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation" aria-expanded="false" aria-controls="primaryNav">
            <span class="hamburger-bar"></span>
            <span class="hamburger-bar"></span>
            <span class="hamburger-bar"></span>
        </button>

        <nav class="primary-nav" id="primaryNav" aria-label="Primary navigation">
            <ul class="nav-list">
                <li class="nav-item <?= $current_page === 'index'    ? 'active' : '' ?>">
                    <a href="/">Home</a>
                </li>
                <li class="nav-item <?= $current_page === 'products' ? 'active' : '' ?>">
                    <a href="/products">All Products</a>
                </li>
                <li class="nav-item <?= $current_page === 'about'    ? 'active' : '' ?>">
                    <a href="/about">About</a>
                </li>
                <li class="nav-item <?= $current_page === 'contact'  ? 'active' : '' ?>">
                    <a href="/contact" class="nav-cta">Get a Quote</a>
                </li>
            </ul>
        </nav>

    </div>
</header>

<main id="main-content">
