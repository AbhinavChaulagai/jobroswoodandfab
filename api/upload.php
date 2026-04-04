<?php
/**
 * api/upload.php — Image upload endpoint for the admin panel.
 *
 * POST multipart/form-data with field "image".
 * Returns JSON: { success, url, filename } or { success: false, error }
 *
 * Storage strategy (in order of preference):
 *  1. Vercel Blob  — if BLOB_READ_WRITE_TOKEN env var is set (persistent CDN)
 *  2. Local disk   — assets/images/ (works locally and is committed to git)
 *
 * Setup Vercel Blob:
 *   vercel.com → project → Storage → Create → Blob Store → copy token to
 *   Settings → Environment Variables → BLOB_READ_WRITE_TOKEN
 */

// Auth: only the admin can upload
require_once __DIR__ . '/../admin/auth_functions.php';
$token = $_COOKIE['jwf_admin'] ?? '';
if (!$token || !verify_admin_token($token)) {
    http_response_code(401);
    json_out(false, error: 'Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    json_out(false, error: 'Method not allowed');
}

// ── Validate uploaded file ────────────────────────────────────────────────────
$file = $_FILES['image'] ?? null;

if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
    $upload_errors = [
        UPLOAD_ERR_INI_SIZE   => 'File exceeds server upload limit.',
        UPLOAD_ERR_FORM_SIZE  => 'File exceeds form upload limit.',
        UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded.',
        UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Server temporary directory missing.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
    ];
    $msg = $upload_errors[$file['error'] ?? UPLOAD_ERR_NO_FILE] ?? 'Upload failed.';
    json_out(false, error: $msg);
}

// Allowed MIME types
$allowed_mimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
$finfo         = new finfo(FILEINFO_MIME_TYPE);
$mime          = $finfo->file($file['tmp_name']);

if (!in_array($mime, $allowed_mimes, true)) {
    json_out(false, error: 'Only JPEG, PNG, WebP, and GIF images are allowed.');
}

// Max 8 MB
if ($file['size'] > 8 * 1024 * 1024) {
    json_out(false, error: 'Image must be under 8 MB.');
}

// Sanitise filename
$ext      = match($mime) {
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    'image/gif'  => 'gif',
};
$base     = pathinfo($file['name'], PATHINFO_FILENAME);
$base     = preg_replace('/[^a-z0-9_-]/i', '-', $base);
$base     = strtolower(trim($base, '-'));
$base     = substr($base, 0, 60) ?: 'image';
$filename = $base . '-' . substr(bin2hex(random_bytes(4)), 0, 8) . '.' . $ext;

// ── 1. Try Vercel Blob ────────────────────────────────────────────────────────
$blob_token = getenv('BLOB_READ_WRITE_TOKEN');
if ($blob_token) {
    $blob_url = upload_to_vercel_blob($file['tmp_name'], $filename, $mime, $blob_token);
    if ($blob_url) {
        json_out(true, url: $blob_url, filename: $filename);
    }
}

// ── 2. Fall back to local assets/images/ ─────────────────────────────────────
$dest_dir = __DIR__ . '/../assets/images/';
if (!is_dir($dest_dir)) {
    mkdir($dest_dir, 0755, true);
}

$dest_path = $dest_dir . $filename;

if (move_uploaded_file($file['tmp_name'], $dest_path)) {
    json_out(true, url: '/assets/images/' . $filename, filename: $filename);
}

json_out(false, error: 'Failed to save image. Check server permissions.');

// ── Helpers ───────────────────────────────────────────────────────────────────

function upload_to_vercel_blob(string $tmp_path, string $filename, string $mime, string $token): ?string {
    $data = file_get_contents($tmp_path);
    if ($data === false) return null;

    $url = 'https://blob.vercel-storage.com/' . rawurlencode($filename);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => 'PUT',
        CURLOPT_POSTFIELDS     => $data,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $token,
            'Content-Type: ' . $mime,
            'x-content-type: ' . $mime,
            'Content-Length: ' . strlen($data),
            'x-api-version: 7',
        ],
    ]);

    $response = curl_exec($ch);
    $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status >= 200 && $status < 300) {
        $result = json_decode($response, true);
        return $result['url'] ?? null;
    }
    return null;
}

function json_out(bool $success, string $url = '', string $filename = '', string $error = ''): never {
    header('Content-Type: application/json; charset=UTF-8');
    $out = ['success' => $success];
    if ($url)      $out['url']      = $url;
    if ($filename) $out['filename'] = $filename;
    if ($error)    $out['error']    = $error;
    echo json_encode($out);
    exit;
}
