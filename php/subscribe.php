<?php

declare(strict_types=1);

header('Content-Type: application/json');
require __DIR__ . '/blog.php'; // MySQL connection (blog_db) — subscribers show up in /admin -> Subscribers

function respond(bool $success, string $message, int $code = 200): void
{
    http_response_code($code);
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Invalid request method.', 405);
}

if (trim($_POST['website'] ?? '') !== '') {
    respond(true, "You're on the list.");
}

$email = trim((string)($_POST['email'] ?? ''));

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 160) {
    respond(false, "That email address doesn't look right.", 422);
}

try {
    $page = (string)(parse_url((string)($_SERVER['HTTP_REFERER'] ?? ''), PHP_URL_PATH) ?? '');
    $stmt = blog_db()->prepare('INSERT IGNORE INTO subscribers (email, source_page, created_at) VALUES (:email, :page, :created_at)');
    $stmt->execute([
        ':email' => $email,
        ':page' => mb_substr($page, 0, 255),
        ':created_at' => blog_now(),
    ]);
} catch (Throwable $e) {
    respond(false, 'Something went wrong on our end. Please try again shortly.', 500);
}

respond(true, "You're on the list — welcome aboard.");
