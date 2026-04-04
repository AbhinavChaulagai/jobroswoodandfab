<?php
/**
 * admin/index.php — Admin login page.
 *
 * Set your password via the ADMIN_PASSWORD environment variable in the
 * Vercel dashboard (Project → Settings → Environment Variables).
 * Default fallback password: admin123  ← CHANGE THIS before going live.
 */

if (session_status() === PHP_SESSION_NONE) session_start();

// Already logged in — go straight to dashboard
if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: /admin/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_password = getenv('ADMIN_PASSWORD') ?: 'admin123';
    $submitted      = $_POST['password'] ?? '';

    if ($submitted !== '' && hash_equals($admin_password, $submitted)) {
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_login_time'] = time();
        header('Location: /admin/dashboard.php');
        exit;
    } else {
        // Small delay to slow brute-force attempts
        sleep(1);
        $error = 'Incorrect password. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — Jobros Wood & Fab</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body class="login-page">

<div class="login-card">
    <div class="login-logo">
        <span class="login-logo-name">Jobros</span>
        <span class="login-logo-sub">Wood &amp; Fab — Admin</span>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="/admin/index.php" class="login-form">
        <div class="form-group">
            <label for="password">Admin Password</label>
            <input
                type="password"
                id="password"
                name="password"
                required
                autofocus
                autocomplete="current-password"
                placeholder="Enter your password"
            >
        </div>
        <button type="submit" class="btn btn-primary btn-block">Sign In</button>
    </form>

    <p class="login-back"><a href="/">&larr; Back to website</a></p>
</div>

</body>
</html>
