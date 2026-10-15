<?php
require __DIR__ . '/../php/blog.php';

$category = trim((string)($_GET['category'] ?? ''));
$page = max(1, (int)($_GET['page'] ?? 1));
$posts = [];
$categories = [];
$totalPages = 1;
$dbError = false;

try {
    $pdo = blog_db();
    $categories = blog_categories($pdo);
    $where = blog_public_where() . ($category !== '' ? ' AND category = :category' : '');
    $params = [':now' => blog_now()] + ($category !== '' ? [':category' => $category] : []);

    $count = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE $where");
    $count->execute($params);
    $totalPages = max(1, (int)ceil((int)$count->fetchColumn() / BLOG_PER_PAGE));
    $page = min($page, $totalPages);

    $stmt = $pdo->prepare("SELECT title, slug, excerpt, content, cover_image, category, author, published_at FROM posts WHERE $where ORDER BY published_at DESC, id DESC LIMIT :limit OFFSET :offset");
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', BLOG_PER_PAGE, PDO::PARAM_INT);
    $stmt->bindValue(':offset', ($page - 1) * BLOG_PER_PAGE, PDO::PARAM_INT);
    $stmt->execute();
    $posts = $stmt->fetchAll();
} catch (PDOException $e) {
    $dbError = true;
}

$query = fn(array $extra) => '?' . http_build_query(array_filter(array_merge(['category' => $category, 'page' => null], $extra)));
$selfQuery = $query(['page' => $page > 1 ? $page : null]);

$base = '../';
$isBlog = true;
$pageTitle = ($category !== '' ? $category . ' articles' : 'Blog — Digital Marketing Insights') . ($page > 1 ? " (page $page)" : '') . ' | Skylink Media';
$pageDescription = 'Practical notes on SEO, social media, Meta Ads, Google Ads and websites from Skylink Media, a digital marketing agency in Indore.';
$pageCanonical = BLOG_SITE_URL . 'blog/' . ($selfQuery !== '?' ? $selfQuery : '');
$pageOgImage = BLOG_SITE_URL . 'images/logo-5.webp';
$pageBreadcrumbs = [['Home', BLOG_SITE_URL], ['Blog', BLOG_SITE_URL . 'blog/']];
$pageSchemaExtra = [[
    '@type' => 'Blog',
    '@id' => BLOG_SITE_URL . 'blog/#blog',
    'name' => 'Skylink Media Blog',
    'url' => BLOG_SITE_URL . 'blog/',
    'publisher' => ['@id' => BLOG_SITE_URL . '#organization'],
]];
require __DIR__ . '/../includes/header.php';

$featured = ($page === 1 && $category === '' && $posts) ? array_shift($posts) : null;

function blog_card(array $post, bool $featured = false): void
{
    $href = blog_post_href($post['slug']);
    ?>
        <article class="blog-card<?= $featured ? ' is-featured' : '' ?> reveal">
          <a class="blog-card-media<?= $post['cover_image'] ? '' : ' is-empty' ?>" href="<?= htmlspecialchars($href) ?>" tabindex="-1" aria-hidden="true">
            <?php if ($post['cover_image']): ?><img src="../<?= htmlspecialchars($post['cover_image']) ?>" alt="" loading="<?= $featured ? 'eager' : 'lazy' ?>"><?php else: ?><span><?= htmlspecialchars($post['category'] ?: 'Skylink notes') ?></span><?php endif; ?>
          </a>
          <div class="blog-card-body">
            <p class="blog-meta"><?php if ($post['category']): ?><span class="blog-cat"><?= htmlspecialchars($post['category']) ?></span><?php endif; ?><time datetime="<?= date('c', strtotime($post['published_at'])) ?>"><?= blog_format_date($post['published_at']) ?></time><span><?= blog_reading_time($post['content']) ?> min read</span></p>
            <h<?= $featured ? '2' : '3' ?> class="blog-card-title"><a href="<?= htmlspecialchars($href) ?>"><?= htmlspecialchars($post['title']) ?></a></h<?= $featured ? '2' : '3' ?>>
            <p class="blog-card-excerpt"><?= htmlspecialchars(blog_summary($post, $featured ? 220 : 140)) ?></p>
            <a class="text-link site-btn" href="<?= htmlspecialchars($href) ?>" aria-label="Read: <?= htmlspecialchars($post['title']) ?>">Read article <span>&#8599;</span></a>
          </div>
        </article>
    <?php
}
?>
    <section class="service-hero section-pad blog-hero">
      <p class="eyebrow"><span class="eyebrow-line"></span> Blog &mdash; Growth notes</p>
      <h1><?php if ($category !== ''): ?><?= htmlspecialchars($category) ?><span class="lime">.</span><?php else: ?>Notes on<br><em>growth</em>.<?php endif; ?></h1>
      <p class="service-intro">Practical ideas on SEO, social, ads and websites &mdash; the same thinking we bring to client work, written down.</p>
    </section>

    <section class="service-block is-tight blog-list">
      <?php if ($categories): ?>
      <nav class="blog-chips" aria-label="Blog categories">
        <a href="./"<?= $category === '' ? ' class="is-active" aria-current="page"' : '' ?>>All</a>
        <?php foreach ($categories as $cat): ?>
        <a href="<?= htmlspecialchars($query(['category' => $cat['category']])) ?>"<?= $category === $cat['category'] ? ' class="is-active" aria-current="page"' : '' ?>><?= htmlspecialchars($cat['category']) ?> <span><?= (int)$cat['total'] ?></span></a>
        <?php endforeach; ?>
      </nav>
      <?php endif; ?>

      <?php if ($featured): blog_card($featured, true); endif; ?>

      <?php if ($posts): ?>
      <div class="blog-grid">
        <?php foreach ($posts as $post) { blog_card($post); } ?>
      </div>
      <?php elseif (!$featured): ?>
      <div class="blog-empty reveal">
        <h2 class="svc-h2"><?= $dbError ? 'The blog is taking a <em>short break</em>.' : 'First notes <em>coming soon</em>.' ?></h2>
        <p><?= $dbError ? 'Please check back in a few minutes.' : "We're writing up what we've learned growing brands in Indore and beyond. Check back shortly." ?></p>
      </div>
      <?php endif; ?>

      <?php if ($totalPages > 1): ?>
      <nav class="blog-pagination" aria-label="Pagination">
        <?php if ($page > 1): ?><a href="<?= htmlspecialchars($page === 2 ? ($query([]) === '?' ? './' : $query([])) : $query(['page' => $page - 1])) ?>" rel="prev">&larr; Newer</a><?php endif; ?>
        <span class="mono">Page <?= $page ?> of <?= $totalPages ?></span>
        <?php if ($page < $totalPages): ?><a href="<?= htmlspecialchars($query(['page' => $page + 1])) ?>" rel="next">Older &rarr;</a><?php endif; ?>
      </nav>
      <?php endif; ?>
    </section>

    <section class="service-cta"><h2>Want these ideas working for your brand?</h2><a class="button button-ink site-btn" href="../contact.php">Let's talk <span>&#8599;</span></a></section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
