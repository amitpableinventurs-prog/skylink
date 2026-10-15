<?php

declare(strict_types=1);

/**
 * One-time import of enquiries/subscribers saved by the old SQLite form storage (data/skylink.sqlite)
 * into MySQL. Safe to run twice — duplicates are skipped. The SQLite file is kept, renamed as a backup.
 */

require __DIR__ . '/_bootstrap.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !legacy_sqlite_pending()) {
    admin_redirect('leads.php');
}
csrf_check();

$old = new PDO('sqlite:' . LEGACY_SQLITE_PATH, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
// The old storage saved times in UTC; the panel uses India time.
$ist = fn(string $utc) => date('Y-m-d H:i:s', strtotime($utc . ' UTC') ?: time());
$leads = 0;
$subs = 0;

$pdo->beginTransaction();
try {
    $exists = $pdo->prepare('SELECT COUNT(*) FROM leads WHERE email = :e AND created_at = :c');
    $insert = $pdo->prepare("INSERT INTO leads (name, email, phone, service, message, source_page, status, notes, ip, created_at, updated_at)
        VALUES (:n, :e, '', :s, :m, '', 'new', 'Imported from old form storage', '', :c, :c2)");
    foreach ($old->query('SELECT name, email, service, message, created_at FROM contacts') as $row) {
        $created = $ist($row['created_at']);
        $exists->execute([':e' => $row['email'], ':c' => $created]);
        if ((int)$exists->fetchColumn() > 0) {
            continue;
        }
        $insert->execute([
            ':n' => mb_scrub(mb_substr((string)$row['name'], 0, 120), 'UTF-8'), ':e' => mb_substr((string)$row['email'], 0, 160),
            ':s' => mb_scrub(mb_substr((string)$row['service'], 0, 80), 'UTF-8'), ':m' => mb_scrub((string)$row['message'], 'UTF-8'),
            ':c' => $created, ':c2' => $created,
        ]);
        $leads++;
    }

    $insertSub = $pdo->prepare("INSERT IGNORE INTO subscribers (email, source_page, created_at) VALUES (:e, '', :c)");
    foreach ($old->query('SELECT email, created_at FROM subscribers') as $row) {
        $insertSub->execute([':e' => mb_substr((string)$row['email'], 0, 160), ':c' => $ist($row['created_at'])]);
        $subs += $insertSub->rowCount();
    }
    $pdo->commit();
} catch (Throwable $ex) {
    $pdo->rollBack();
    flash('Import failed: ' . $ex->getMessage(), 'error');
    admin_redirect('leads.php');
}

$old = null; // release the SQLite file handle before renaming (Windows)
@rename(LEGACY_SQLITE_PATH, LEGACY_SQLITE_PATH . '.imported-' . date('Ymd-His'));
flash("Imported $leads old enquiries and $subs subscribers.");
admin_redirect('leads.php');
