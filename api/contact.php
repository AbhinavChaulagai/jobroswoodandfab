<?php
/**
 * api/contact.php — Contact form submission endpoint.
 *
 * Accepts POST requests with: name, email, phone, subject, message, website (honeypot).
 * Returns JSON: { success: bool, message: string, errors?: string[] }
 *
 * To enable email delivery, configure MAIL_TO and SMTP settings in a .env file
 * and uncomment the mail() / phpmailer block below.
 */

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    json_response(false, 'Method not allowed.');
}

require_once __DIR__ . '/../includes/functions.php';

// ── Honeypot anti-spam check ──────────────────────────────────────────────────
// Real users never fill in the hidden "website" field; bots often do.
if (!empty($_POST['website'])) {
    // Silently succeed so bots don't know they were caught
    json_response(true, 'Message received! We\'ll be in touch soon.');
}

// ── Validate ──────────────────────────────────────────────────────────────────
$result = validate_contact_form($_POST);

if (!$result['valid']) {
    http_response_code(422);
    json_response(false, 'Please correct the errors below.', $result['errors']);
}

$data = $result['data'];

// ── Rate-limit check (basic, session-based) ───────────────────────────────────
// Prevents someone hammering the form repeatedly without a real rate-limiter.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$now          = time();
$last_submit  = $_SESSION['last_contact_submit'] ?? 0;
$submit_count = $_SESSION['contact_submit_count'] ?? 0;

if (($now - $last_submit) < 60 && $submit_count >= 3) {
    http_response_code(429);
    json_response(false, 'Too many submissions. Please wait a minute and try again.');
}

// Update rate-limit counters
$_SESSION['last_contact_submit']   = $now;
$_SESSION['contact_submit_count']  = ($now - $last_submit < 300) ? $submit_count + 1 : 1;

// ── Send Email ────────────────────────────────────────────────────────────────
// For production, replace this block with an SMTP library (PHPMailer / Symfony Mailer).
// The built-in mail() function requires a local MTA — fine for VPS, not for serverless.
//
// $to      = getenv('MAIL_TO') ?: 'hello@jobroswoodandfab.com';
// $subject = '[JWF Quote] ' . ($data['subject'] ?: 'New contact form submission');
// $body    = "Name:    {$data['name']}\n"
//          . "Email:   {$data['email']}\n"
//          . "Phone:   {$data['phone']}\n"
//          . "Subject: {$data['subject']}\n\n"
//          . "Message:\n{$data['message']}\n";
// $headers = "From: noreply@jobroswoodandfab.com\r\n"
//          . "Reply-To: {$data['email']}\r\n"
//          . "Content-Type: text/plain; charset=UTF-8\r\n";
// mail($to, $subject, $body, $headers);

// ── Log to file (development / backup) ───────────────────────────────────────
// Write submissions to a local log file. In production, consider a database or
// a transactional email service (Resend, SendGrid, Postmark) instead.
$log_dir  = __DIR__ . '/../data';
$log_file = $log_dir . '/contact_submissions.log';

$log_entry = sprintf(
    "[%s] NAME: %s | EMAIL: %s | PHONE: %s | SUBJECT: %s | MESSAGE: %s\n",
    date('Y-m-d H:i:s'),
    $data['name'],
    $data['email'],
    $data['phone'],
    $data['subject'],
    str_replace(["\r", "\n"], ' ', $data['message'])
);

// Attempt to write; silently continue if the directory isn't writable
if (is_writable($log_dir)) {
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

// ── Success ───────────────────────────────────────────────────────────────────
json_response(true, "Thanks, {$data['name']}! We've received your message and will get back to you within one business day.");

// ── Helper ────────────────────────────────────────────────────────────────────
/**
 * Emit a JSON response and exit.
 *
 * @param bool     $success
 * @param string   $message  Human-readable status message
 * @param string[] $errors   Validation errors (optional)
 */
function json_response(bool $success, string $message, array $errors = []): never {
    header('Content-Type: application/json; charset=UTF-8');
    $payload = ['success' => $success, 'message' => $message];
    if (!empty($errors)) {
        $payload['errors'] = array_values($errors);
    }
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
