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
 * Also accepts fully-qualified URLs (http/https) and returns them as-is.
 */
function product_image_url(string $filename, int $width = 600, int $height = 450): string {
    // If a full URL was stored, use it directly
    if (str_starts_with($filename, 'http://') || str_starts_with($filename, 'https://')) {
        return $filename;
    }
    $local = __DIR__ . '/../assets/images/' . $filename;
    if (file_exists($local)) {
        return '/assets/images/' . $filename;
    }
    // Use placehold.co as a placeholder — brown/cream palette fits the brand
    return "https://placehold.co/{$width}x{$height}/8B6343/F5ECD7?text=" . rawurlencode(pathinfo($filename, PATHINFO_FILENAME));
}

/**
 * Return all images for a product as an array of URLs/filenames.
 * Falls back to the legacy `image` field for backward compatibility.
 */
function get_product_images(array $product): array {
    if (!empty($product['images']) && is_array($product['images'])) {
        return array_values(array_filter($product['images']));
    }
    if (!empty($product['image'])) {
        return [$product['image']];
    }
    return [];
}

/**
 * Return the primary (first) image URL for a product.
 */
function get_primary_image(array $product, int $w = 600, int $h = 450): string {
    $images = get_product_images($product);
    $first  = $images[0] ?? ($product['image'] ?? '');
    return $first !== '' ? product_image_url($first, $w, $h) : product_image_url('placeholder.jpg', $w, $h);
}

/**
 * Save the full products array back to the JSON file.
 * Returns true on success, false on failure.
 */
function save_products(array $products): bool {
    $path = __DIR__ . '/../data/products.json';
    $json = json_encode(array_values($products), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    return file_put_contents($path, $json, LOCK_EX) !== false;
}

/**
 * Return the next available integer product ID.
 */
function next_product_id(array $products): int {
    if (empty($products)) return 1;
    return max(array_column($products, 'id')) + 1;
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
