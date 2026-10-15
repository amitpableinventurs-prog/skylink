<?php
/** XML sitemap of published blog posts (listed in robots.txt next to the static sitemap.xml). */
require __DIR__ . '/../php/blog.php';

header('Content-Type: application/xml; charset=utf-8');

$posts = [];
try {
    $stmt = blog_db()->prepare('SELECT slug, updated_at, published_at FROM posts WHERE ' . blog_public_where() . ' ORDER BY published_at DESC');
    $stmt->execute([':now' => blog_now()]);
    $posts = $stmt->fetchAll();
} catch (PDOException $e) {
    // Serve an empty-but-valid sitemap rather than an error page.
}

$latest = $posts ? max(array_map(fn($p) => strtotime($p['updated_at']), $posts)) : time();

echo '<?xml version="1.0" encoding="UTF-8"?>', "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc><?= BLOG_SITE_URL ?>blog/</loc>
    <lastmod><?= date('Y-m-d', $latest) ?></lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.7</priority>
  </url>
<?php foreach ($posts as $post): ?>
  <url>
    <loc><?= htmlspecialchars(blog_post_canonical($post['slug']), ENT_XML1) ?></loc>
    <lastmod><?= date('Y-m-d', strtotime(max($post['updated_at'], $post['published_at']))) ?></lastmod>
    <priority>0.6</priority>
  </url>
<?php endforeach; ?>
</urlset>
