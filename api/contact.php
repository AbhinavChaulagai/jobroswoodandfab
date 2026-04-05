<?php
/**
 * api/contact.php — Contact form submission handler.
 *
 * Validates the form, then sends an email via the Resend API.
 * Set these environment variables in Vercel Project Settings:
 *   RESEND_API_KEY  — get a free key at https://resend.com
 *   MAIL_FROM       — verified sender address (e.g. hello@yourdomain.com)
 *                     OR leave unset to use the Resend shared domain
 *   MAIL_TO         — where to receive messages (your inbox)
 */

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

require_once __DIR__ . '/../includes/functions.php';

// ── Honeypot anti-spam ────────────────────────────────────────────────────────
if (!empty($_POST['website'])) {
    echo json_encode(['success' => true, 'message' => "Thanks! We'll be in touch soon."]);
    exit;
}

// ── Validate ──────────────────────────────────────────────────────────────────
$result = validate_contact_form($_POST);

if (!$result['valid']) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Please correct the errors below.',
        'errors'  => array_values($result['errors']),
    ]);
    exit;
}

$data = $result['data'];

// ── Send email via Resend API ─────────────────────────────────────────────────
$api_key  = getenv('RESEND_API_KEY') ?: '';
$mail_to  = getenv('MAIL_TO')  ?: 'hello@jobroswoodandfab.com';
$mail_from = getenv('MAIL_FROM') ?: 'Jobros Wood & Fab <onboarding@resend.dev>';

if ($api_key !== '') {
    $subject = '[JWF Quote] ' . ($data['subject'] ?: 'New inquiry from ' . $data['name']);

    $html_body = '
    <div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto">
      <h2 style="color:#3E2008">New Quote Request — Jobros Wood &amp; Fab</h2>
      <table style="width:100%;border-collapse:collapse">
        <tr><td style="padding:8px 0;color:#6B4226;font-weight:bold;width:110px">Name</td>
            <td style="padding:8px 0">' . htmlspecialchars($data['name']) . '</td></tr>
        <tr><td style="padding:8px 0;color:#6B4226;font-weight:bold">Email</td>
            <td style="padding:8px 0"><a href="mailto:' . htmlspecialchars($data['email']) . '">' . htmlspecialchars($data['email']) . '</a></td></tr>
        <tr><td style="padding:8px 0;color:#6B4226;font-weight:bold">Phone</td>
            <td style="padding:8px 0">' . htmlspecialchars($data['phone'] ?: '—') . '</td></tr>
        <tr><td style="padding:8px 0;color:#6B4226;font-weight:bold">Subject</td>
            <td style="padding:8px 0">' . htmlspecialchars($data['subject'] ?: '—') . '</td></tr>
      </table>
      <hr style="border:1px solid #D4B896;margin:16px 0">
      <h3 style="color:#3E2008">Message</h3>
      <p style="line-height:1.6;color:#333">' . nl2br(htmlspecialchars($data['message'])) . '</p>
      <hr style="border:1px solid #D4B896;margin:16px 0">
      <p style="font-size:12px;color:#888">
        Reply directly to: <a href="mailto:' . htmlspecialchars($data['email']) . '">' . htmlspecialchars($data['email']) . '</a>
      </p>
    </div>';

    $payload = [
        'from'       => $mail_from,
        'to'         => [$mail_to],
        'reply_to'   => $data['email'],
        'subject'    => $subject,
        'html'       => $html_body,
    ];

    $ctx = stream_context_create([
        'http' => [
            'method'        => 'POST',
            'header'        => "Authorization: Bearer {$api_key}\r\nContent-Type: application/json",
            'content'       => json_encode($payload),
            'ignore_errors' => true,
            'timeout'       => 10,
        ]
    ]);

    $resp = @file_get_contents('https://api.resend.com/emails', false, $ctx);
    // We don't block success on email delivery — log but continue
    // (If Resend returns an error the message is still acknowledged to the user)
}

// ── Success ───────────────────────────────────────────────────────────────────
echo json_encode([
    'success' => true,
    'message' => "Thanks, {$data['name']}! We've received your message and will get back to you within one business day.",
]);
exit;
