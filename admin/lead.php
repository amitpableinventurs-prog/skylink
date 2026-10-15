<?php

declare(strict_types=1);

require __DIR__ . '/_bootstrap.php';
require_admin();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM leads WHERE id = :id');
$stmt->execute([':id' => $id]);
$lead = $stmt->fetch();
if (!$lead) {
    flash('That lead no longer exists.', 'error');
    admin_redirect('leads.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (($_POST['action'] ?? '') === 'delete') {
        $pdo->prepare('DELETE FROM leads WHERE id = :id')->execute([':id' => $id]);
        flash('Lead from ' . $lead['name'] . ' deleted.');
        admin_redirect('leads.php');
    }
    $status = (string)($_POST['status'] ?? $lead['status']);
    if (!isset(LEAD_STATUSES[$status])) {
        $status = $lead['status'];
    }
    $notes = trim(mb_scrub((string)($_POST['notes'] ?? ''), 'UTF-8'));
    $pdo->prepare('UPDATE leads SET status = :s, notes = :n, updated_at = :u WHERE id = :id')
        ->execute([':s' => $status, ':n' => mb_substr($notes, 0, 5000), ':u' => blog_now(), ':id' => $id]);
    flash('Lead updated.');
    admin_redirect('lead.php?id=' . $id);
}

// Other enquiries from the same person, so repeat leads are easy to spot.
$others = $pdo->prepare('SELECT id, service, status, created_at FROM leads WHERE email = :email AND id != :id ORDER BY created_at DESC LIMIT 10');
$others->execute([':email' => $lead['email'], ':id' => $id]);
$others = $others->fetchAll();

$replySubject = rawurlencode('Re: your enquiry to Skylink Media' . ($lead['service'] ? ' — ' . $lead['service'] : ''));
$replyBody = rawurlencode("Hi {$lead['name']},\n\nThanks for reaching out to Skylink Media.\n\n");
$waPhone = preg_replace('/\D+/', '', $lead['phone']);
if ($waPhone !== '' && strlen($waPhone) === 10) {
    $waPhone = '91' . $waPhone;
}

admin_header('Lead: ' . $lead['name'], 'leads');
?>
    <div class="page-head">
      <div>
        <p class="eyebrow"><span class="eyebrow-line"></span> <a href="leads.php">Leads</a> / #<?= (int)$lead['id'] ?></p>
        <h1><?= e($lead['name']) ?></h1>
      </div>
      <div class="head-actions">
        <a class="btn btn-primary" href="mailto:<?= e($lead['email']) ?>?subject=<?= $replySubject ?>&amp;body=<?= $replyBody ?>">Reply by email ↗</a>
        <?php if ($waPhone !== ''): ?><a class="btn btn-ghost" href="https://wa.me/<?= e($waPhone) ?>" target="_blank" rel="noopener">WhatsApp ↗</a><?php endif; ?>
      </div>
    </div>

    <div class="editor-grid">
      <div class="editor-main">
        <section class="panel">
          <dl class="lead-facts">
            <div><dt>Email</dt><dd><a href="mailto:<?= e($lead['email']) ?>"><?= e($lead['email']) ?></a></dd></div>
            <div><dt>Phone</dt><dd><?= $lead['phone'] ? '<a href="tel:' . e($lead['phone']) . '">' . e($lead['phone']) . '</a>' : '<span class="muted">—</span>' ?></dd></div>
            <div><dt>Service</dt><dd><?= e($lead['service']) ?: '<span class="muted">—</span>' ?></dd></div>
            <div><dt>Received</dt><dd><?= e(date('j M Y, g:i A', strtotime($lead['created_at']))) ?></dd></div>
            <div><dt>Form on page</dt><dd><?= e($lead['source_page']) ?: '<span class="muted">—</span>' ?></dd></div>
          </dl>
        </section>
        <section class="panel">
          <h2>Message</h2>
          <div class="lead-message"><?= $lead['message'] !== '' ? nl2br(e($lead['message'])) : '<span class="muted">No message was written.</span>' ?></div>
        </section>
        <?php if ($others): ?>
        <section class="panel">
          <h2>Earlier enquiries from this email</h2>
          <ul class="lead-history">
            <?php foreach ($others as $o): ?>
            <li><a href="lead.php?id=<?= (int)$o['id'] ?>"><?= e(date('j M Y', strtotime($o['created_at']))) ?> — <?= e($o['service']) ?></a> <span class="badge badge-lead-<?= e($o['status']) ?>"><?= e(LEAD_STATUSES[$o['status']] ?? $o['status']) ?></span></li>
            <?php endforeach; ?>
          </ul>
        </section>
        <?php endif; ?>
      </div>

      <aside class="editor-side">
        <form class="panel" method="post">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= (int)$lead['id'] ?>">
          <h2>Follow-up</h2>
          <div class="field"><span>Status</span>
            <div class="status-pick">
              <?php foreach (LEAD_STATUSES as $key => $label): ?>
              <label><input type="radio" name="status" value="<?= $key ?>"<?= $lead['status'] === $key ? ' checked' : '' ?>><span class="badge badge-lead-<?= $key ?>"><?= $label ?></span></label>
              <?php endforeach; ?>
            </div>
          </div>
          <label class="field"><span>Notes <em>(only visible here)</em></span>
            <textarea name="notes" rows="6" maxlength="5000" placeholder="Called on Monday, sending proposal…"><?= e($lead['notes']) ?></textarea></label>
          <div class="panel-actions"><button class="btn btn-primary btn-block" type="submit">Save</button></div>
        </form>
        <form class="panel" method="post" onsubmit="return confirm('Delete this lead permanently?');">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= (int)$lead['id'] ?>">
          <input type="hidden" name="action" value="delete">
          <button class="btn btn-danger btn-block" type="submit">Delete lead</button>
        </form>
      </aside>
    </div>
<?php admin_footer(); ?>
