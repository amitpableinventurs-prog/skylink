<?php

declare(strict_types=1);

/** CSV download of leads or subscribers (opens directly in Excel / Google Sheets). */

require __DIR__ . '/_bootstrap.php';
require_admin();

$type = $_GET['type'] ?? 'leads';

if ($type === 'subscribers') {
    $rows = $pdo->query('SELECT email, source_page, created_at FROM subscribers ORDER BY created_at DESC')->fetchAll();
    $header = ['Email', 'Signed up on page', 'Date'];
    $filename = 'skylink-subscribers-' . date('Y-m-d') . '.csv';
} else {
    $status = $_GET['status'] ?? '';
    $sql = 'SELECT created_at, name, email, phone, service, message, status, notes, source_page FROM leads';
    $params = [];
    if (isset(LEAD_STATUSES[$status])) {
        $sql .= ' WHERE status = :s';
        $params[':s'] = $status;
    }
    $stmt = $pdo->prepare($sql . ' ORDER BY created_at DESC');
    $stmt->execute($params);
    $rows = array_map(function ($r) {
        $r['status'] = LEAD_STATUSES[$r['status']] ?? $r['status'];
        return $r;
    }, $stmt->fetchAll());
    $header = ['Date', 'Name', 'Email', 'Phone', 'Service', 'Message', 'Status', 'Notes', 'Form on page'];
    $filename = 'skylink-leads' . (isset(LEAD_STATUSES[$status]) ? '-' . $status : '') . '-' . date('Y-m-d') . '.csv';
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF"); // BOM so Excel reads ₹, Hindi names etc. correctly
fputcsv($out, $header);
foreach ($rows as $row) {
    // Neutralise spreadsheet formulas typed into the public form (=, +, -, @).
    fputcsv($out, array_map(fn($v) => preg_match('/^[=+\-@\t\r]/', (string)$v) ? "'" . $v : $v, array_values($row)));
}
fclose($out);
