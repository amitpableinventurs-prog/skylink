<?php

declare(strict_types=1);

header('Content-Type: application/json');
require __DIR__ . '/blog.php'; // MySQL connection (blog_db) — leads show up in /admin -> Leads

function respond(bool $success, string $message, int $code = 200): void
{
    http_response_code($code);
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Invalid request method.', 405);
}

// Honeypot: bots fill hidden fields humans never see.
if (trim($_POST['website'] ?? '') !== '') {
    respond(true, 'Thanks. We will be in touch within two working days.');
}

$name = trim((string)($_POST['name'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$service = trim((string)($_POST['service'] ?? ''));
$message = trim((string)($_POST['message'] ?? ''));
$phone = trim((string)($_POST['phone'] ?? ''));

if ($name === '' || $email === '' || $phone === '' || $service === '') {
    respond(false, 'Please fill in your name, email, mobile number, and service.', 422);
}
$phoneDigits = preg_replace('/\D+/', '', $phone);
if (!preg_match('/^[0-9+()\-\s]+$/', $phone) || strlen($phoneDigits) < 10 || strlen($phoneDigits) > 13) {
    respond(false, "That mobile number doesn't look right.", 422);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(false, "That email address doesn't look right.", 422);
}
if (mb_strlen($name) > 120 || mb_strlen($email) > 160 || mb_strlen($service) > 80 || mb_strlen($message) > 3000 || mb_strlen($phone) > 30) {
    respond(false, 'That submission is too long.', 422);
}

try {
    $page = (string)(parse_url((string)($_SERVER['HTTP_REFERER'] ?? ''), PHP_URL_PATH) ?? '');
    $now = blog_now();
    $stmt = blog_db()->prepare(
        'INSERT INTO leads (name, email, phone, service, message, source_page, status, notes, ip, created_at, updated_at)
         VALUES (:name, :email, :phone, :service, :message, :page, \'new\', \'\', :ip, :created_at, :updated_at)'
    );
    $stmt->execute([
        ':name' => mb_scrub($name, 'UTF-8'),
        ':email' => $email,
        ':phone' => mb_scrub($phone, 'UTF-8'),
        ':service' => mb_scrub($service, 'UTF-8'),
        ':message' => mb_scrub($message, 'UTF-8'),
        ':page' => mb_substr($page, 0, 255),
        ':ip' => substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45),
        ':created_at' => $now,
        ':updated_at' => $now,
    ]);
} catch (Throwable $e) {
    respond(false, 'Something went wrong on our end. Please try again shortly.', 500);
}

$to = 'skylinkmedia.co@gmail.com';
$subject = 'New enquiry from ' . $name;
$body = "Name: $name\nEmail: $email\n" . ($phone !== '' ? "Phone: $phone\n" : '')
    . "Service: $service\n\nMessage:\n$message\n\nAll leads: https://skylinkmedia.co.in/admin/leads.php";
$headers = "From: no-reply@skylinkmedia.local\r\nReply-To: $email";
@mail($to, $subject, $body, $headers);

respond(true, 'Thanks! We will be in touch within two working days.');
