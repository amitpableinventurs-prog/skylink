<?php

declare(strict_types=1);

/**
 * First-run setup: creates the first admin account. Locks itself once any admin exists,
 * so run it right after deploying/importing the database.
 */

require __DIR__ . '/_bootstrap.php';

if (admin_count($pdo) > 0) {
    admin_redirect('login.php');
}

$errors = [];
$name = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name = trim((string)($_POST['name'] ?? ''));
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $confirm = (string)($_POST['confirm'] ?? '');

    if (!preg_match('/^[A-Za-z0-9._-]{3,60}$/', $username)) {
        $errors[] = 'Username must be 3–60 characters: letters, numbers, dot, dash or underscore.';
    }
    if (mb_strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }
    if ($password !== $confirm) {
        $errors[] = "The two passwords don't match.";
    }

    if (!$errors) {
        $pdo->prepare('INSERT INTO admins (username, name, password_hash, created_at) VALUES (:u, :n, :h, :c)')
            ->execute([':u' => $username, ':n' => mb_substr($name, 0, 120), ':h' => password_hash($password, PASSWORD_DEFAULT), ':c' => blog_now()]);
        $stmt = $pdo->prepare('SELECT * FROM admins WHERE username = :u');
        $stmt->execute([':u' => $username]);
        admin_login($stmt->fetch());
        flash('Admin account created. Write your first post!');
        admin_redirect('index.php');
    }
}

admin_header('Set up admin');
?>
    <div class="auth-card">
      <img class="auth-logo" src="../images/logo-5.webp" alt="Skylink Media" width="66" height="50">
      <p class="eyebrow"><span class="eyebrow-line"></span> First-time setup</p>
      <h1>Create your <em>admin</em> login.</h1>
      <p class="muted">This page works only once — after the first account is created it locks itself.</p>
      <?php foreach ($errors as $error): ?><div class="flash flash-error" role="alert"><?= e($error) ?></div><?php endforeach; ?>
      <form method="post" class="stack">
        <?= csrf_field() ?>
        <label class="field"><span>Your name <em>(shown as post author)</em></span><input type="text" name="name" value="<?= e($name) ?>" maxlength="120" autocomplete="name"></label>
        <label class="field"><span>Username</span><input type="text" name="username" value="<?= e($username) ?>" required maxlength="60" autocomplete="username"></label>
        <label class="field"><span>Password <em>(min 8 characters)</em></span><input type="password" name="password" required minlength="8" autocomplete="new-password"></label>
        <label class="field"><span>Confirm password</span><input type="password" name="confirm" required minlength="8" autocomplete="new-password"></label>
        <button class="btn btn-primary btn-block" type="submit">Create account <span aria-hidden="true">↗</span></button>
      </form>
    </div>
<?php admin_footer(); ?>
