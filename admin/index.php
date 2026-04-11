<?php
/**
 * admin/index.php — Admin login page.
 *
 * Set your password via the ADMIN_PASSWORD environment variable in the
 * Vercel dashboard (Project → Settings → Environment Variables).
 */

require_once __DIR__ . '/auth_functions.php'; // token helpers without auth check

// Already logged in — go straight to dashboard
if (!empty($_COOKIE['jwf_admin']) && verify_admin_token($_COOKIE['jwf_admin'])) {
    header('Location: /admin/dashboard');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_password = getenv('ADMIN_PASSWORD') ?: 'admin123';
    $submitted      = $_POST['password'] ?? '';

    if ($submitted !== '' && hash_equals($admin_password, $submitted)) {
        // Set signed cookie — no session needed
        $token = make_admin_token();
        setcookie('jwf_admin', $token, [
            'expires'  => time() + 604800, // 7 days
            'path'     => '/',
            'secure'   => true,
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        header('Location: /admin/dashboard');
        exit;
    } else {
        sleep(1); // slow brute force
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

    <form method="POST" action="/admin/" class="login-form">
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
