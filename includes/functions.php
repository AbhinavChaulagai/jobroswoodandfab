<?php
/**
 * functions.php — Shared helper functions for Jobros Wood & Fab
 */

/**
 * Load all products from the JSON data file.
 * Returns an array of associative arrays, or an empty array on failure.
 */
function get_all_products(): array {
    $path = __DIR__ . '/../data/products.json';
    if (!file_exists($path)) {
        return [];
    }
    $json = file_get_contents($path);
    $products = json_decode($json, true);
    return is_array($products) ? $products : [];
}

/**
 * Find a single product by its numeric ID.
 * Returns the product array, or null if not found.
 */
function get_product_by_id(int $id): ?array {
    $products = get_all_products();
    foreach ($products as $product) {
        if ((int)$product['id'] === $id) {
            return $product;
        }
    }
    return null;
}

/**
 * Return products filtered by category name (case-insensitive).
 */
function get_products_by_category(string $category): array {
    $products = get_all_products();
    return array_values(array_filter($products, function ($p) use ($category) {
        return strcasecmp($p['category'], $category) === 0;
    }));
}

/**
 * Get all unique category names from the product list.
 */
function get_all_categories(): array {
    $products = get_all_products();
    $categories = array_column($products, 'category');
    return array_values(array_unique($categories));
}

/**
 * Safely escape a string for HTML output.
 */
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Build the URL for a product image. Falls back to a placeholder if the image
 * file doesn't exist in assets/images/.
 */
function product_image_url(string $filename, int $width = 600, int $height = 450): string {
    $local = __DIR__ . '/../assets/images/' . $filename;
    if (file_exists($local)) {
        return '/assets/images/' . $filename;
    }
    // Use placehold.co as a placeholder — brown/cream palette fits the brand
    return "https://placehold.co/{$width}x{$height}/8B6343/F5ECD7?text=" . rawurlencode(pathinfo($filename, PATHINFO_FILENAME));
}

/**
 * Validate and sanitize contact form data.
 * Returns ['valid' => bool, 'errors' => string[], 'data' => array].
 */
function validate_contact_form(array $post): array {
    $errors = [];
    $data   = [];

    $data['name']    = trim($post['name']    ?? '');
    $data['email']   = trim($post['email']   ?? '');
    $data['phone']   = trim($post['phone']   ?? '');
    $data['subject'] = trim($post['subject'] ?? '');
    $data['message'] = trim($post['message'] ?? '');

    if ($data['name'] === '') {
        $errors[] = 'Your name is required.';
    }

    if ($data['email'] === '' || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email address is required.';
    }

    if ($data['message'] === '') {
        $errors[] = 'A message is required.';
    } elseif (strlen($data['message']) < 10) {
        $errors[] = 'Please write a bit more in your message (at least 10 characters).';
    }

    return [
        'valid'  => empty($errors),
        'errors' => $errors,
        'data'   => $data,
    ];
}
