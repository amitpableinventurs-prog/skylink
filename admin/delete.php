<?php

declare(strict_types=1);

require __DIR__ . '/_bootstrap.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    admin_redirect('index.php');
}
csrf_check();

$id = (int)($_POST['id'] ?? 0);
$stmt = $pdo->prepare('SELECT title, cover_image FROM posts WHERE id = :id');
$stmt->execute([':id' => $id]);
$post = $stmt->fetch();

if ($post) {
    $pdo->prepare('DELETE FROM posts WHERE id = :id')->execute([':id' => $id]);

    // Remove the cover file too, unless another post still uses it.
    $cover = $post['cover_image'];
    if (str_starts_with($cover, BLOG_UPLOAD_URL . '/')) {
        $inUse = $pdo->prepare('SELECT COUNT(*) FROM posts WHERE cover_image = :c');
        $inUse->execute([':c' => $cover]);
        $file = BLOG_UPLOAD_DIR . '/' . basename($cover);
        if ((int)$inUse->fetchColumn() === 0 && is_file($file)) {
            @unlink($file);
        }
    }
    flash('Deleted “' . $post['title'] . '”.');
}

admin_redirect('index.php');
