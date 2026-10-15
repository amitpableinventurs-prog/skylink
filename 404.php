<?php
// Served for missing pages (ErrorDocument) and for retired old-site URLs (see .htaccess), at any depth —
// so link back to the site root with an absolute path instead of './'.
http_response_code(404);
header('X-Robots-Tag: noindex, follow');
$base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/404.php')), '/') . '/';
$pageTitle = 'Page Not Found | Skylink Media';
$pageDescription = "The page you're looking for doesn't exist or has moved.";
$pageCanonical = '';
$pageRobots = 'noindex, follow';
require __DIR__ . '/includes/header.php';
?>
    <section class="service-hero section-pad">
      <p class="eyebrow"><span class="eyebrow-line"></span> 404</p>
      <h1>That page<br><em>wandered off</em>.</h1>
      <p class="service-intro">The page you're looking for doesn't exist or has moved. Try the homepage, or one of the links below.</p>
      <div class="hero-actions">
        <a class="button button-lime site-btn" href="<?= $base ?>">Back to homepage <span>&#8599;</span></a>
        <a class="text-link site-btn" href="<?= $base ?>contact.php">Contact us <span>&#8599;</span></a>
      </div>
    </section>
<?php require __DIR__ . '/includes/footer.php'; ?>
