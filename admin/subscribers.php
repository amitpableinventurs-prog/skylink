<?php

declare(strict_types=1);

require __DIR__ . '/_bootstrap.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $stmt = $pdo->prepare('SELECT email FROM subscribers WHERE id = :id');
    $stmt->execute([':id' => (int)($_POST['id'] ?? 0)]);
    if ($email = $stmt->fetchColumn()) {
        $pdo->prepare('DELETE FROM subscribers WHERE id = :id')->execute([':id' => (int)$_POST['id']]);
        flash("Removed $email from the list.");
    }
    admin_redirect('subscribers.php');
}

$q = trim((string)($_GET['q'] ?? ''));
$total = (int)$pdo->query('SELECT COUNT(*) FROM subscribers')->fetchColumn();
$thisMonth = $pdo->prepare('SELECT COUNT(*) FROM subscribers WHERE created_at >= :start');
$thisMonth->execute([':start' => date('Y-m-01 00:00:00')]);
$thisMonth = (int)$thisMonth->fetchColumn();

$stmt = $pdo->prepare('SELECT id, email, source_page, created_at FROM subscribers' . ($q !== '' ? ' WHERE email LIKE :q' : '') . ' ORDER BY created_at DESC, id DESC LIMIT 500');
$stmt->execute($q !== '' ? [':q' => '%' . $q . '%'] : []);
$subs = $stmt->fetchAll();

admin_header('Subscribers', 'subscribers');
?>
    <div class="page-head">
      <div>
        <p class="eyebrow"><span class="eyebrow-line"></span> Newsletter — <?= $total ?> total · <?= $thisMonth ?> this month</p>
        <h1>Subscribers</h1>
      </div>
      <a class="btn btn-ghost" href="export.php?type=subscribers">Download CSV ↓</a>
    </div>

    <div class="toolbar">
      <span class="muted">Emails from the “Get growth notes, monthly” form in the footer.</span>
      <form class="search" method="get"><input type="search" name="q" value="<?= e($q) ?>" placeholder="Search email…" aria-label="Search subscribers"></form>
    </div>

    <?php if (!$subs): ?>
    <div class="empty">
      <h2><?= $total === 0 ? 'No subscribers yet.' : 'Nothing matches.' ?></h2>
      <p class="muted"><?= $total === 0 ? 'Sign-ups from the website footer will appear here.' : 'Try a different search.' ?></p>
    </div>
    <?php else: ?>
    <div class="sub-list">
      <?php foreach ($subs as $sub): ?>
      <div class="sub-row">
        <a href="mailto:<?= e($sub['email']) ?>"><?= e($sub['email']) ?></a>
        <span class="muted"><?= e($sub['source_page']) ?></span>
        <time class="muted" title="<?= e(date('j M Y, g:i A', strtotime($sub['created_at']))) ?>"><?= e(date('j M Y', strtotime($sub['created_at']))) ?></time>
        <form method="post" onsubmit="return confirm('Remove this subscriber?');">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= (int)$sub['id'] ?>">
          <button class="btn btn-danger btn-sm" type="submit">Remove</button>
        </form>
      </div>
      <?php endforeach; ?>
    </div>
    <?php if (count($subs) === 500): ?><p class="hint">Showing the latest 500 — download the CSV for the full list.</p><?php endif; ?>
    <?php endif; ?>
<?php admin_footer(); ?>
