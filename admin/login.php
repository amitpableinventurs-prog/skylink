<?php

declare(strict_types=1);

require __DIR__ . '/_bootstrap.php';

if (admin_count($pdo) === 0) {
    admin_redirect('setup.php');
}
if (blog_is_admin()) {
    admin_redirect('leads.php');
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $ip = client_ip();

    $pdo->prepare('DELETE FROM login_attempts WHERE attempted_at < :cutoff')
        ->execute([':cutoff' => time() - ADMIN_LOCKOUT_SECONDS]);
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM login_attempts WHERE ip = :ip');
    $stmt->execute([':ip' => $ip]);

    if ((int)$stmt->fetchColumn() >= ADMIN_MAX_ATTEMPTS) {
        $error = 'Too many failed attempts. Please wait 15 minutes and try again.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM admins WHERE username = :username');
        $stmt->execute([':username' => $username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            $pdo->prepare('DELETE FROM login_attempts WHERE ip = :ip')->execute([':ip' => $ip]);
            if (password_needs_rehash($admin['password_hash'], PASSWORD_DEFAULT)) {
                $pdo->prepare('UPDATE admins SET password_hash = :hash WHERE id = :id')
                    ->execute([':hash' => password_hash($password, PASSWORD_DEFAULT), ':id' => $admin['id']]);
            }
            admin_login($admin);
            admin_redirect('leads.php');
        }

        $pdo->prepare('INSERT INTO login_attempts (ip, attempted_at) VALUES (:ip, :t)')
            ->execute([':ip' => $ip, ':t' => time()]);
        usleep(400000);
        $error = 'Wrong username or password.';
    }
}

admin_header('Log in');
?>
    <div class="auth-card">
      <img class="auth-logo" src="../images/logo-5.webp" alt="Skylink Media" width="66" height="50">
      <p class="eyebrow"><span class="eyebrow-line"></span> Blog admin</p>
      <h1>Welcome <em>back</em>.</h1>
      <?php if ($error): ?><div class="flash flash-error" role="alert"><?= e($error) ?></div><?php endif; ?>
      <form method="post" class="stack" autocomplete="on">
        <?= csrf_field() ?>
        <label class="field"><span>Username</span><input type="text" name="username" value="<?= e($username) ?>" required autofocus autocomplete="username" maxlength="60"></label>
        <label class="field"><span>Password</span><input type="password" name="password" required autocomplete="current-password"></label>
        <button class="btn btn-primary btn-block" type="submit">Log in <span aria-hidden="true">↗</span></button>
      </form>
      <a class="auth-back" href="../">← Back to website</a>
    </div>
<?php admin_footer(); ?>
