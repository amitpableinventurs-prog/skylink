<?php

declare(strict_types=1);

require __DIR__ . '/_bootstrap.php';
require_admin();

$stmt = $pdo->prepare('SELECT * FROM admins WHERE id = :id');
$stmt->execute([':id' => $_SESSION['admin_id']]);
$admin = $stmt->fetch();
if (!$admin) {
    $_SESSION = [];
    admin_redirect('login.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name = trim((string)($_POST['name'] ?? ''));
    $current = (string)($_POST['current'] ?? '');
    $new = (string)($_POST['new'] ?? '');
    $confirm = (string)($_POST['confirm'] ?? '');

    if (!password_verify($current, $admin['password_hash'])) {
        $errors[] = 'Your current password is not correct.';
    }
    if ($new !== '' && mb_strlen($new) < 8) {
        $errors[] = 'New password must be at least 8 characters.';
    }
    if ($new !== $confirm) {
        $errors[] = "The new passwords don't match.";
    }

    if (!$errors) {
        $hash = $new !== '' ? password_hash($new, PASSWORD_DEFAULT) : $admin['password_hash'];
        $pdo->prepare('UPDATE admins SET name = :n, password_hash = :h WHERE id = :id')
            ->execute([':n' => mb_substr($name, 0, 120), ':h' => $hash, ':id' => $admin['id']]);
        $_SESSION['admin_name'] = $name ?: $admin['username'];
        session_regenerate_id(true);
        flash($new !== '' ? 'Account updated and password changed.' : 'Account updated.');
        admin_redirect('account.php');
    }
    $admin['name'] = $name;
}

admin_header('Account', 'account');
?>
    <div class="page-head">
      <div>
        <p class="eyebrow"><span class="eyebrow-line"></span> Signed in as <?= e($admin['username']) ?></p>
        <h1>Account</h1>
      </div>
    </div>
    <?php foreach ($errors as $error): ?><div class="flash flash-error" role="alert"><?= e($error) ?></div><?php endforeach; ?>
    <form method="post" class="panel narrow stack">
      <?= csrf_field() ?>
      <label class="field"><span>Display name <em>(default author on new posts)</em></span><input type="text" name="name" value="<?= e($admin['name']) ?>" maxlength="120"></label>
      <label class="field"><span>New password <em>(leave empty to keep the current one)</em></span><input type="password" name="new" minlength="8" autocomplete="new-password"></label>
      <label class="field"><span>Confirm new password</span><input type="password" name="confirm" minlength="8" autocomplete="new-password"></label>
      <label class="field"><span>Current password <em>(required to save)</em></span><input type="password" name="current" required autocomplete="current-password"></label>
      <button class="btn btn-primary" type="submit">Save changes</button>
    </form>
<?php admin_footer(); ?>
