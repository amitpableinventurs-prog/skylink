<?php

declare(strict_types=1);

/**
 * Admin panel bootstrap: session, auth, CSRF and the shared page chrome.
 * Every admin page requires this first.
 */

require_once __DIR__ . '/../php/blog.php';

header('X-Robots-Tag: noindex, nofollow');
header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');
header('Cache-Control: no-store');

blog_session_start();

const ADMIN_IDLE_TIMEOUT = 4 * 3600;
const ADMIN_MAX_ATTEMPTS = 5;
const ADMIN_LOCKOUT_SECONDS = 15 * 60;
const LEAD_STATUSES = ['new' => 'New', 'contacted' => 'Contacted', 'converted' => 'Converted', 'closed' => 'Closed'];
// Where the old (pre-MySQL) contact form saved enquiries; see import-legacy.php.
const LEGACY_SQLITE_PATH = __DIR__ . '/../data/skylink.sqlite';

try {
    $pdo = blog_db();
} catch (PDOException $e) {
    http_response_code(500);
    admin_header('Database error');
    echo '<div class="auth-card"><h1>Database not connected</h1><p class="muted">Could not connect to MySQL. Check the details in <code>php/config.php</code> and make sure <code>database/skylink_blog.sql</code> has been imported in phpMyAdmin.</p></div>';
    admin_footer();
    exit;
}

if (blog_is_admin()) {
    if (time() - ($_SESSION['last_seen'] ?? 0) > ADMIN_IDLE_TIMEOUT) {
        $_SESSION = [];
        session_regenerate_id(true);
    } else {
        $_SESSION['last_seen'] = time();
    }
}

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function admin_redirect(string $to): never
{
    header('Location: ' . $to);
    exit;
}

function admin_count(PDO $pdo): int
{
    return (int)$pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn();
}

function require_admin(): void
{
    if (!blog_is_admin()) {
        admin_redirect('login.php');
    }
}

function admin_login(array $admin): void
{
    session_regenerate_id(true);
    $_SESSION['admin_id'] = (int)$admin['id'];
    $_SESSION['admin_name'] = $admin['name'] ?: $admin['username'];
    $_SESSION['last_seen'] = time();
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    $sent = $_POST['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
        http_response_code(403);
        exit('Your session expired. Go back, refresh the page and try again.');
    }
}

function flash(?string $message = null, string $type = 'success'): ?array
{
    if ($message !== null) {
        $_SESSION['flash'] = ['message' => $message, 'type' => $type];
        return null;
    }
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

/** Row counts still sitting in the old SQLite storage, or null when there is nothing to import. */
function legacy_sqlite_pending(): ?array
{
    if (!is_file(LEGACY_SQLITE_PATH) || !in_array('sqlite', PDO::getAvailableDrivers(), true)) {
        return null;
    }
    try {
        $old = new PDO('sqlite:' . LEGACY_SQLITE_PATH, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $tables = $old->query("SELECT name FROM sqlite_master WHERE type = 'table'")->fetchAll(PDO::FETCH_COLUMN);
        $counts = [
            'contacts' => in_array('contacts', $tables, true) ? (int)$old->query('SELECT COUNT(*) FROM contacts')->fetchColumn() : 0,
            'subscribers' => in_array('subscribers', $tables, true) ? (int)$old->query('SELECT COUNT(*) FROM subscribers')->fetchColumn() : 0,
        ];
    } catch (Throwable $e) {
        return null;
    }
    return $counts['contacts'] + $counts['subscribers'] > 0 ? $counts : null;
}

function admin_relative_time(string $datetime): string
{
    $diff = time() - (strtotime($datetime) ?: time());
    return match (true) {
        $diff < 60 => 'just now',
        $diff < 3600 => floor($diff / 60) . ' min ago',
        $diff < 86400 => floor($diff / 3600) . ' h ago',
        $diff < 7 * 86400 => floor($diff / 86400) . ' d ago',
        default => date('j M Y', strtotime($datetime)),
    };
}

function client_ip(): string
{
    return substr((string)($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 0, 45);
}

function admin_header(string $title, string $active = ''): void
{
    $loggedIn = blog_is_admin();
    $newLeads = 0;
    if ($loggedIn) {
        try {
            $newLeads = (int)blog_db()->query("SELECT COUNT(*) FROM leads WHERE status = 'new'")->fetchColumn();
        } catch (Throwable $e) {
            // leads table missing (database/add_leads.sql not imported yet) — show the nav without a badge.
        }
    }
    ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title><?= e($title) ?> · Skylink Admin</title>
  <link rel="icon" type="image/webp" href="../images/logo-5.webp">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="admin.css?v=20260925">
</head>
<body class="<?= $loggedIn ? 'is-app' : 'is-auth' ?>">
<?php if ($loggedIn): ?>
  <header class="admin-bar">
    <a class="admin-brand" href="leads.php"><img src="../images/logo-5.webp" alt="" width="45" height="34"><span>Admin</span></a>
    <nav class="admin-nav" aria-label="Admin">
      <a href="leads.php"<?= $active === 'leads' ? ' class="is-active"' : '' ?>>Leads<?php if ($newLeads): ?> <b class="nav-badge" title="New leads"><?= $newLeads ?></b><?php endif; ?></a>
      <a href="index.php"<?= $active === 'posts' ? ' class="is-active"' : '' ?>>Posts</a>
      <a href="edit.php"<?= $active === 'new' ? ' class="is-active"' : '' ?>>New post</a>
      <a href="subscribers.php"<?= $active === 'subscribers' ? ' class="is-active"' : '' ?>>Subscribers</a>
      <a href="account.php"<?= $active === 'account' ? ' class="is-active"' : '' ?>>Account</a>
      <a href="../blog/" target="_blank" rel="noopener">View blog ↗</a>
    </nav>
    <form class="admin-logout" action="logout.php" method="post">
      <?= csrf_field() ?>
      <span class="admin-user"><?= e($_SESSION['admin_name'] ?? '') ?></span>
      <button type="submit">Log out</button>
    </form>
  </header>
<?php endif; ?>
  <main class="admin-main">
<?php
    $flash = flash();
    if ($flash) {
        echo '<div class="flash flash-' . e($flash['type']) . '" role="status">' . e($flash['message']) . '</div>';
    }
}

function admin_footer(): void
{
    ?>
  </main>
</body>
</html>
<?php
}
