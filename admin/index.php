<?php

declare(strict_types=1);

require __DIR__ . '/_bootstrap.php';
require_admin();

$filter = $_GET['status'] ?? 'all';
$q = trim((string)($_GET['q'] ?? ''));
$now = blog_now();

$counts = $pdo->prepare("SELECT
    COUNT(*) AS total,
    SUM(status = 'published' AND published_at <= :now1) AS live,
    SUM(status = 'published' AND published_at > :now2) AS scheduled,
    SUM(status = 'draft') AS drafts
  FROM posts");
$counts->execute([':now1' => $now, ':now2' => $now]);
$counts = array_map('intval', $counts->fetch());

$where = [];
$params = [];
if ($filter === 'published') {
    $where[] = "status = 'published' AND published_at <= :now";
    $params[':now'] = $now;
} elseif ($filter === 'scheduled') {
    $where[] = "status = 'published' AND published_at > :now";
    $params[':now'] = $now;
} elseif ($filter === 'draft') {
    $where[] = "status = 'draft'";
} else {
    $filter = 'all';
}
if ($q !== '') {
    $where[] = '(title LIKE :q OR category LIKE :q2)';
    $params[':q'] = '%' . $q . '%';
    $params[':q2'] = '%' . $q . '%';
}
$sql = 'SELECT id, title, slug, category, status, cover_image, published_at, updated_at FROM posts'
    . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
    . ' ORDER BY published_at DESC, id DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$posts = $stmt->fetchAll();

$tabs = ['all' => ['All', $counts['total']], 'published' => ['Published', $counts['live']], 'scheduled' => ['Scheduled', $counts['scheduled']], 'draft' => ['Drafts', $counts['drafts']]];

admin_header('Posts', 'posts');
?>
    <div class="page-head">
      <div>
        <p class="eyebrow"><span class="eyebrow-line"></span> Blog</p>
        <h1>Posts</h1>
      </div>
      <a class="btn btn-primary" href="edit.php">+ New post</a>
    </div>

    <div class="toolbar">
      <nav class="tabs" aria-label="Filter posts">
        <?php foreach ($tabs as $key => [$label, $count]): ?>
        <a href="?status=<?= $key ?><?= $q !== '' ? '&q=' . urlencode($q) : '' ?>"<?= $filter === $key ? ' class="is-active"' : '' ?>><?= $label ?> <span><?= $count ?></span></a>
        <?php endforeach; ?>
      </nav>
      <form class="search" method="get">
        <input type="hidden" name="status" value="<?= e($filter) ?>">
        <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search posts…" aria-label="Search posts">
      </form>
    </div>

    <?php if (!$posts): ?>
    <div class="empty">
      <?php if ($counts['total'] === 0): ?>
      <h2>No posts yet.</h2>
      <p class="muted">Your blog is ready — write the first article.</p>
      <a class="btn btn-primary" href="edit.php">Write your first post</a>
      <?php else: ?>
      <h2>Nothing matches.</h2>
      <p class="muted">Try a different filter or search.</p>
      <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="post-list">
      <?php foreach ($posts as $post):
        $state = $post['status'] === 'draft' ? 'draft' : ($post['published_at'] > $now ? 'scheduled' : 'published');
        $viewUrl = '../blog/post.php?slug=' . rawurlencode($post['slug']) . ($state === 'published' ? '' : '&preview=1');
      ?>
      <article class="post-row">
        <a class="post-thumb" href="edit.php?id=<?= (int)$post['id'] ?>" tabindex="-1" aria-hidden="true">
          <?php if ($post['cover_image']): ?><img src="../<?= e($post['cover_image']) ?>" alt="" loading="lazy"><?php endif; ?>
        </a>
        <div class="post-row-main">
          <a class="post-row-title" href="edit.php?id=<?= (int)$post['id'] ?>"><?= e($post['title']) ?></a>
          <div class="post-row-meta">
            <span class="badge badge-<?= $state ?>"><?= ucfirst($state) ?></span>
            <?php if ($post['category']): ?><span><?= e($post['category']) ?></span><?php endif; ?>
            <span><?= $state === 'draft' ? 'Edited ' . e(blog_format_date($post['updated_at'])) : e(blog_format_date($post['published_at'])) ?></span>
            <span class="mono">/blog/<?= e($post['slug']) ?></span>
          </div>
        </div>
        <div class="post-row-actions">
          <a class="btn btn-ghost btn-sm" href="edit.php?id=<?= (int)$post['id'] ?>">Edit</a>
          <a class="btn btn-ghost btn-sm" href="<?= e($viewUrl) ?>" target="_blank" rel="noopener"><?= $state === 'published' ? 'View' : 'Preview' ?></a>
          <form action="delete.php" method="post" onsubmit="return confirm('Delete “<?= e(addslashes($post['title'])) ?>”? This cannot be undone.');">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int)$post['id'] ?>">
            <button class="btn btn-danger btn-sm" type="submit">Delete</button>
          </form>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
<?php admin_footer(); ?>
