<?php
/**
 * admin/submissions.php — View contact form submissions from the log file.
 */
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../includes/functions.php';

$log_file    = __DIR__ . '/../data/contact_submissions.log';
$submissions = [];

if (file_exists($log_file)) {
    $lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    // Parse each log line into structured data and show newest first
    foreach (array_reverse($lines) as $line) {
        // Format: [YYYY-MM-DD HH:MM:SS] NAME: ... | EMAIL: ... | PHONE: ... | SUBJECT: ... | MESSAGE: ...
        $parsed = ['raw' => $line];
        if (preg_match('/^\[(.+?)\]/', $line, $m))                    $parsed['date']    = $m[1];
        if (preg_match('/NAME: (.+?)(?= \|)/', $line, $m))            $parsed['name']    = $m[1];
        if (preg_match('/EMAIL: (.+?)(?= \|)/', $line, $m))           $parsed['email']   = $m[1];
        if (preg_match('/PHONE: (.+?)(?= \|)/', $line, $m))           $parsed['phone']   = $m[1];
        if (preg_match('/SUBJECT: (.+?)(?= \|)/', $line, $m))         $parsed['subject'] = $m[1];
        if (preg_match('/MESSAGE: (.+)$/', $line, $m))                 $parsed['message'] = $m[1];
        $submissions[] = $parsed;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submissions — Jobros Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body class="admin-layout">

<?php require __DIR__ . '/partials/sidebar.php'; ?>

<main class="admin-main">
    <div class="admin-topbar">
        <h1 class="admin-page-title">Contact Submissions</h1>
        <span class="badge"><?= count($submissions) ?> total</span>
    </div>

    <?php if (empty($submissions)): ?>
        <div class="admin-card">
            <p class="empty-state">No submissions yet. They'll appear here once someone fills out the contact form.</p>
        </div>
    <?php else: ?>
        <div class="submissions-list">
            <?php foreach ($submissions as $s): ?>
            <div class="submission-card">
                <div class="submission-header">
                    <div class="submission-from">
                        <strong><?= e($s['name'] ?? 'Unknown') ?></strong>
                        <a href="mailto:<?= e($s['email'] ?? '') ?>"><?= e($s['email'] ?? '') ?></a>
                        <?php if (!empty($s['phone'])): ?>
                            <span class="submission-phone"><?= e($s['phone']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="submission-meta">
                        <?php if (!empty($s['date'])): ?>
                            <time><?= e($s['date']) ?></time>
                        <?php endif; ?>
                        <?php if (!empty($s['email'])): ?>
                            <a href="mailto:<?= e($s['email']) ?>?subject=Re: <?= rawurlencode($s['subject'] ?? 'Your inquiry') ?>" class="btn btn-sm btn-outline">Reply</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if (!empty($s['subject'])): ?>
                    <p class="submission-subject"><strong>Subject:</strong> <?= e($s['subject']) ?></p>
                <?php endif; ?>
                <?php if (!empty($s['message'])): ?>
                    <p class="submission-message"><?= e($s['message']) ?></p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

</body>
</html>
