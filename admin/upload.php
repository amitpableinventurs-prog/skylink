<?php

declare(strict_types=1);

/** Image upload endpoint for images inserted inside the article editor (TinyMCE). */

require __DIR__ . '/_bootstrap.php';

header('Content-Type: application/json');

if (!blog_is_admin()) {
    http_response_code(401);
    exit(json_encode(['error' => 'Please log in again.']));
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['error' => 'Invalid request.']));
}
csrf_check();

try {
    $path = blog_store_upload($_FILES['file'] ?? []);
    // Both /admin and /blog sit one level below the site root, so this relative URL works in both.
    echo json_encode(['location' => '../' . $path]);
} catch (RuntimeException $ex) {
    http_response_code(422);
    echo json_encode(['error' => $ex->getMessage()]);
}
