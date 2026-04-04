<?php
/**
 * api/upload.php — Upload an image to imgBB and return the permanent URL.
 *
 * Requires env variable: IMGBB_API_KEY
 * Get a free key at https://api.imgbb.com  (free account, no credit card)
 * Then add it in Vercel → Project → Settings → Environment Variables.
 *
 * POST  multipart/form-data  field: "image"
 * Returns JSON: { success: true, url: "https://..." }
 *           or  { success: false, error: "..." }
 */

header('Content-Type: application/json; charset=UTF-8');

// ── Auth: admin-only ──────────────────────────────────────────────────────────
require_once __DIR__ . '/../admin/auth_functions.php';
if (!verify_admin_token($_COOKIE['jwf_admin'] ?? '')) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// ── Method ────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'POST required']);
    exit;
}

// ── imgBB key ─────────────────────────────────────────────────────────────────
$api_key = getenv('IMGBB_API_KEY');
if (!$api_key) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'IMGBB_API_KEY is not set. Add it in Vercel → Settings → Environment Variables.',
    ]);
    exit;
}

// ── Validate upload ───────────────────────────────────────────────────────────
$file = $_FILES['image'] ?? null;
if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No file received or upload error.']);
    exit;
}

// Real MIME check (not just extension)
$finfo    = new finfo(FILEINFO_MIME_TYPE);
$mime     = $finfo->file($file['tmp_name']);
$allowed  = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
if (!in_array($mime, $allowed, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Only JPEG, PNG, WebP, and GIF are allowed.']);
    exit;
}

if ($file['size'] > 8 * 1024 * 1024) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Max file size is 8 MB.']);
    exit;
}

// ── Upload to imgBB ───────────────────────────────────────────────────────────
// imgBB expects the image as base64-encoded data posted as form field "image".
$b64 = base64_encode(file_get_contents($file['tmp_name']));
$name = preg_replace('/[^a-z0-9_-]/i', '-', pathinfo($file['name'], PATHINFO_FILENAME));

$ch = curl_init('https://api.imgbb.com/1/upload');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_POSTFIELDS     => [
        'key'   => $api_key,
        'image' => $b64,
        'name'  => $name,
    ],
]);

$body   = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err    = curl_error($ch);
curl_close($ch);

if ($err) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'cURL error: ' . $err]);
    exit;
}

$result = json_decode($body, true);

if (($result['success'] ?? false) && !empty($result['data']['display_url'])) {
    echo json_encode([
        'success' => true,
        'url'     => $result['data']['display_url'],
    ]);
} else {
    $msg = $result['error']['message'] ?? ('imgBB error (HTTP ' . $status . ')');
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $msg]);
}
