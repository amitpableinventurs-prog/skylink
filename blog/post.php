<?php
require __DIR__ . '/../php/blog.php';

$slug = trim((string)($_GET['slug'] ?? ''));
$post = null;
$related = [];
$isPreview = false;

// Logged-in admins can preview drafts and scheduled posts with ?preview=1.
if (isset($_GET['preview'], $_COOKIE['skl_admin'])) {
    blog_session_start();
    $isPreview = blog_is_admin();
}

try {
    $pdo = blog_db();
    if ($slug !== '') {
        $stmt = $pdo->prepare('SELECT * FROM posts WHERE slug = :slug' . ($isPreview ? '' : ' AND ' . blog_public_where()));
        $stmt->execute([':slug' => $slug] + ($isPreview ? [] : [':now' => blog_now()]));
        $post = $stmt->fetch() ?: null;
    }
    if ($post) {
        $stmt = $pdo->prepare('SELECT title, slug, excerpt, content, cover_image, category, author, published_at FROM posts
            WHERE ' . blog_public_where() . ' AND id != :id
            ORDER BY (category = :category AND category != \'\') DESC, published_at DESC LIMIT 3');
        $stmt->execute([':now' => blog_now(), ':id' => $post['id'], ':category' => $post['category']]);
        $related = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    $post = null;
}

$base = '../';
$isBlog = true;

if (!$post) {
    http_response_code(404);
    $pageTitle = 'Article not found | Skylink Media';
    $pageDescription = "The article you're looking for doesn't exist or has moved.";
    $pageRobots = 'noindex, follow';
    require __DIR__ . '/../includes/header.php';
    ?>
    <section class="service-hero section-pad">
      <p class="eyebrow"><span class="eyebrow-line"></span> Blog &mdash; 404</p>
      <h1>That article<br><em>wandered off</em>.</h1>
      <p class="service-intro">It may have been renamed or unpublished. The latest notes are on the blog.</p>
      <div class="hero-actions"><a class="button button-lime site-btn" href="./">Browse the blog <span>&#8599;</span></a></div>
    </section>
    <?php
    require __DIR__ . '/../includes/footer.php';
    exit;
}

$canonical = blog_post_canonical($post['slug']);
$description = $post['meta_description'] ?: blog_summary($post, 158);
$coverUrl = $post['cover_image'] ? BLOG_SITE_URL . $post['cover_image'] : BLOG_SITE_URL . 'images/logo-5.webp';
$isLive = $post['status'] === 'published' && $post['published_at'] <= blog_now();

$pageTitle = $post['meta_title'] ?: $post['title'] . ' | Skylink Media Blog';
$pageDescription = $description;
$pageCanonical = $canonical;
$pageOgImage = $coverUrl;
$pageOgType = 'article';
$pageRobots = $isLive ? 'index, follow, max-image-preview:large' : 'noindex, nofollow';
$pageBreadcrumbs = [['Home', BLOG_SITE_URL], ['Blog', BLOG_SITE_URL . 'blog/'], [$post['title'], $canonical]];
$pageSchemaExtra = [[
    '@type' => 'BlogPosting',
    '@id' => $canonical . '#article',
    'headline' => mb_substr($post['title'], 0, 110),
    'description' => $description,
    'image' => $coverUrl,
    'datePublished' => date('c', strtotime($post['published_at'])),
    'dateModified' => date('c', strtotime($post['updated_at'])),
    'author' => $post['author'] ? ['@type' => 'Person', 'name' => $post['author']] : ['@id' => BLOG_SITE_URL . '#organization'],
    'publisher' => ['@id' => BLOG_SITE_URL . '#organization'],
    'mainEntityOfPage' => $canonical,
    'isPartOf' => ['@id' => BLOG_SITE_URL . 'blog/#blog'],
    'articleSection' => $post['category'] ?: null,
    'wordCount' => str_word_count(blog_plain_text($post['content'])),
]];
$pageSchemaExtra[0] = array_filter($pageSchemaExtra[0], fn($v) => $v !== null);
require __DIR__ . '/../includes/header.php';

$shareUrl = rawurlencode($canonical);
$shareText = rawurlencode($post['title']);
?>
    <?php if (!$isLive): ?>
    <div class="blog-preview-bar">Preview &mdash; this post is <?= $post['status'] === 'draft' ? 'a draft' : 'scheduled for ' . htmlspecialchars(date('j M Y, g:i A', strtotime($post['published_at']))) ?> and not visible to the public. <a href="../admin/edit.php?id=<?= (int)$post['id'] ?>">Back to editor</a></div>
    <?php endif; ?>

    <article class="blog-article">
      <header class="service-hero section-pad blog-article-hero">
        <p class="eyebrow"><span class="eyebrow-line"></span> <a href="./">Blog</a><?php if ($post['category']): ?> &mdash; <a href="./?category=<?= rawurlencode($post['category']) ?>"><?= htmlspecialchars($post['category']) ?></a><?php endif; ?></p>
        <h1><?= htmlspecialchars($post['title']) ?></h1>
        <p class="blog-meta blog-article-meta">
          <time datetime="<?= date('c', strtotime($post['published_at'])) ?>"><?= blog_format_date($post['published_at']) ?></time>
          <span><?= blog_reading_time($post['content']) ?> min read</span>
          <?php if ($post['author']): ?><span>By <?= htmlspecialchars($post['author']) ?></span><?php endif; ?>
        </p>
      </header>

      <?php if ($post['cover_image']): ?>
      <figure class="blog-cover"><img src="../<?= htmlspecialchars($post['cover_image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>"></figure>
      <?php endif; ?>

      <div class="blog-body">
        <?= $post['content'] /* sanitized on save in admin/edit.php (blog_sanitize_html) */ ?>
      </div>

      <footer class="blog-share">
        <span class="mono">Share this</span>
        <a href="https://wa.me/?text=<?= $shareText ?>%20<?= $shareUrl ?>" target="_blank" rel="noopener">WhatsApp</a>
        <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= $shareUrl ?>" target="_blank" rel="noopener">LinkedIn</a>
        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $shareUrl ?>" target="_blank" rel="noopener">Facebook</a>
        <a href="https://twitter.com/intent/tweet?url=<?= $shareUrl ?>&amp;text=<?= $shareText ?>" target="_blank" rel="noopener">X</a>
      </footer>
    </article>

    <?php if ($related): ?>
    <section class="service-block blog-related">
      <h2 class="svc-h2 reveal">Keep <em>reading</em>.</h2>
      <div class="blog-grid">
        <?php foreach ($related as $item): $href = blog_post_href($item['slug']); ?>
        <article class="blog-card reveal">
          <a class="blog-card-media<?= $item['cover_image'] ? '' : ' is-empty' ?>" href="<?= htmlspecialchars($href) ?>" tabindex="-1" aria-hidden="true">
            <?php if ($item['cover_image']): ?><img src="../<?= htmlspecialchars($item['cover_image']) ?>" alt="" loading="lazy"><?php else: ?><span><?= htmlspecialchars($item['category'] ?: 'Skylink notes') ?></span><?php endif; ?>
          </a>
          <div class="blog-card-body">
            <p class="blog-meta"><?php if ($item['category']): ?><span class="blog-cat"><?= htmlspecialchars($item['category']) ?></span><?php endif; ?><time datetime="<?= date('c', strtotime($item['published_at'])) ?>"><?= blog_format_date($item['published_at']) ?></time></p>
            <h3 class="blog-card-title"><a href="<?= htmlspecialchars($href) ?>"><?= htmlspecialchars($item['title']) ?></a></h3>
            <p class="blog-card-excerpt"><?= htmlspecialchars(blog_summary($item, 120)) ?></p>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

    <section class="service-cta"><h2>Want results like this for your brand?</h2><a class="button button-ink site-btn" href="../contact.php">Let's talk <span>&#8599;</span></a></section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
