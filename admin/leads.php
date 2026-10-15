<?php

declare(strict_types=1);

require __DIR__ . '/_bootstrap.php';
require_admin();

const LEADS_PER_PAGE = 50;

$filter = $_GET['status'] ?? 'all';
if ($filter !== 'all' && !isset(LEAD_STATUSES[$filter])) {
    $filter = 'all';
}
$q = trim((string)($_GET['q'] ?? ''));
$page = max(1, (int)($_GET['page'] ?? 1));

$counts = ['all' => 0] + array_fill_keys(array_keys(LEAD_STATUSES), 0);
foreach ($pdo->query('SELECT status, COUNT(*) AS n FROM leads GROUP BY status') as $row) {
    $counts[$row['status']] = (int)$row['n'];
    $counts['all'] += (int)$row['n'];
}

$where = [];
$params = [];
if ($filter !== 'all') {
    $where[] = 'status = :status';
    $params[':status'] = $filter;
}
if ($q !== '') {
    $where[] = '(name LIKE :q1 OR email LIKE :q2 OR phone LIKE :q3 OR service LIKE :q4 OR message LIKE :q5)';
    foreach (['q1', 'q2', 'q3', 'q4', 'q5'] as $k) {
        $params[":$k"] = '%' . $q . '%';
    }
}
$whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';

$total = $pdo->prepare("SELECT COUNT(*) FROM leads$whereSql");
$total->execute($params);
$totalPages = max(1, (int)ceil((int)$total->fetchColumn() / LEADS_PER_PAGE));
$page = min($page, $totalPages);

$stmt = $pdo->prepare("SELECT id, name, email, phone, service, message, source_page, status, created_at FROM leads$whereSql ORDER BY created_at DESC, id DESC LIMIT :limit OFFSET :offset");
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(':limit', LEADS_PER_PAGE, PDO::PARAM_INT);
$stmt->bindValue(':offset', ($page - 1) * LEADS_PER_PAGE, PDO::PARAM_INT);
$stmt->execute();
$leads = $stmt->fetchAll();

$link = fn(array $extra) => '?' . http_build_query(array_filter(array_merge(['status' => $filter === 'all' ? null : $filter, 'q' => $q ?: null], $extra)));
$legacy = legacy_sqlite_pending();

admin_header('Leads', 'leads');
?>
    <div class="page-head">
      <div>
        <p class="eyebrow"><span class="eyebrow-line"></span> Contact form enquiries</p>
        <h1>Leads</h1>
      </div>
      <a class="btn btn-ghost" href="export.php?type=leads<?= $filter !== 'all' ? '&amp;status=' . e($filter) : '' ?>">Download CSV ↓</a>
    </div>

    <?php if ($legacy): ?>
    <form class="notice" method="post" action="import-legacy.php">
      <?= csrf_field() ?>
      <p><strong>Old enquiries found.</strong> <?= (int)$legacy['contacts'] ?> enquiries and <?= (int)$legacy['subscribers'] ?> subscribers from the previous form storage can be brought into this panel.</p>
      <button class="btn btn-primary btn-sm" type="submit">Import them now</button>
    </form>
    <?php endif; ?>

    <div class="toolbar">
      <nav class="tabs" aria-label="Filter leads">
        <a href="<?= e($link(['status' => null, 'page' => null])) ?>"<?= $filter === 'all' ? ' class="is-active"' : '' ?>>All <span><?= $counts['all'] ?></span></a>
        <?php foreach (LEAD_STATUSES as $key => $label): ?>
        <a href="<?= e($link(['status' => $key, 'page' => null])) ?>"<?= $filter === $key ? ' class="is-active"' : '' ?>><?= $label ?> <span><?= $counts[$key] ?></span></a>
        <?php endforeach; ?>
      </nav>
      <form class="search" method="get">
        <?php if ($filter !== 'all'): ?><input type="hidden" name="status" value="<?= e($filter) ?>"><?php endif; ?>
        <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search name, email, message…" aria-label="Search leads">
      </form>
    </div>

    <?php if (!$leads): ?>
    <div class="empty">
      <?php if ($counts['all'] === 0): ?>
      <h2>No leads yet.</h2>
      <p class="muted">Enquiries from the website's contact forms will appear here automatically.</p>
      <?php else: ?>
      <h2>Nothing matches.</h2>
      <p class="muted">Try a different filter or search.</p>
      <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="lead-list">
      <?php foreach ($leads as $lead): ?>
      <a class="lead-row<?= $lead['status'] === 'new' ? ' is-new' : '' ?>" href="lead.php?id=<?= (int)$lead['id'] ?>">
        <div class="lead-who">
          <strong><?= e($lead['name']) ?></strong>
          <span><?= e($lead['email']) ?><?= $lead['phone'] ? ' · ' . e($lead['phone']) : '' ?></span>
        </div>
        <div class="lead-what">
          <span class="lead-service"><?= e($lead['service']) ?></span>
          <span class="lead-msg"><?= e(mb_strimwidth(preg_replace('/\s+/', ' ', $lead['message']) ?? '', 0, 110, '…')) ?: '<em>No message</em>' ?></span>
        </div>
        <div class="lead-meta">
          <span class="badge badge-lead-<?= e($lead['status']) ?>"><?= e(LEAD_STATUSES[$lead['status']] ?? $lead['status']) ?></span>
          <time datetime="<?= e(date('c', strtotime($lead['created_at']))) ?>" title="<?= e(date('j M Y, g:i A', strtotime($lead['created_at']))) ?>"><?= e(admin_relative_time($lead['created_at'])) ?></time>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <?php if ($totalPages > 1): ?>
    <nav class="pager" aria-label="Pagination">
      <?php if ($page > 1): ?><a class="btn btn-ghost btn-sm" href="<?= e($link(['page' => $page - 1])) ?>">← Newer</a><?php endif; ?>
      <span class="mono">Page <?= $page ?> of <?= $totalPages ?></span>
      <?php if ($page < $totalPages): ?><a class="btn btn-ghost btn-sm" href="<?= e($link(['page' => $page + 1])) ?>">Older →</a><?php endif; ?>
    </nav>
    <?php endif; ?>
    <?php endif; ?>
<?php admin_footer(); ?>
