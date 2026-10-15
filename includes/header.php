<?php
/**
 * Shared head + header for every PHP page. $base is the path back to the site root ('./' or '../').
 * Expects $pageTitle, $pageDescription, and optionally $activeService to be set
 * by the including page before this file is required.
 */
$base = $base ?? '../';
$activeService = $activeService ?? '';
$isAbout = $isAbout ?? false;
$isContact = $isContact ?? false;
$isBlog = $isBlog ?? false;
$isWork = $isWork ?? false;
$pageOgType = $pageOgType ?? 'website';
$pageCanonical = $pageCanonical ?? '';
$pageOgImage = $pageOgImage ?? '';
$pageBreadcrumbs = $pageBreadcrumbs ?? [];
$pageSchemaExtra = $pageSchemaExtra ?? [];
$pageRobots = $pageRobots ?? 'index, follow';
$pageLocalBusiness = $pageLocalBusiness ?? false;
require_once __DIR__ . '/icons.php';

$services = [
    'seo' => ['label' => 'SEO Services', 'href' => 'seo-services-in-indore'],
    'social-media-marketing' => ['label' => 'Social Media Marketing', 'href' => 'social-media-marketing-services-in-indore'],
    'meta-ads' => ['label' => 'Meta Ads', 'href' => 'meta-ads-services-in-indore'],
    'web-design-development' => ['label' => 'Web Design & Development', 'href' => 'web-design-development-services-in-indore'],
    'google-ads' => ['label' => 'Google Ads', 'href' => 'google-ads-services-in-indore'],
];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-MBHRMZGN');</script>
<!-- End Google Tag Manager -->
  <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>">
  <meta property="og:description" content="<?= htmlspecialchars($pageDescription) ?>">
  <meta property="og:type" content="<?= htmlspecialchars($pageOgType) ?>">
  <meta property="og:site_name" content="Skylink Media">
  <meta name="twitter:card" content="summary">
  <meta name="twitter:title" content="<?= htmlspecialchars($pageTitle) ?>">
  <meta name="twitter:description" content="<?= htmlspecialchars($pageDescription) ?>">
  <meta name="robots" content="<?= htmlspecialchars($pageRobots) ?>">
<?php if ($pageCanonical !== ''): ?><link rel="canonical" href="<?= htmlspecialchars($pageCanonical) ?>">
<?php endif; ?>
<?php if ($pageOgImage !== ''): ?><meta property="og:image" content="<?= htmlspecialchars($pageOgImage) ?>">
<?php endif; ?>
  <link rel="icon" type="image/webp" href="<?= $base ?>images/logo-5.webp">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $base ?>styles.css?v=20260928">
  <?php
  $ldGraph = [
    [
      '@type' => 'Organization',
      '@id' => 'https://skylinkmedia.co.in/#organization',
      'name' => 'Skylink Media',
      'url' => 'https://skylinkmedia.co.in/',
      'logo' => 'https://skylinkmedia.co.in/images/logo-5.webp',
      'email' => 'skylinkmedia.co@gmail.com',
      'telephone' => '+91 96692 07207',
      'address' => ['@type' => 'PostalAddress', 'addressLocality' => 'Indore', 'addressRegion' => 'Madhya Pradesh', 'addressCountry' => 'IN'],
      'areaServed' => ['@type' => 'City', 'name' => 'Indore'],
      'sameAs' => ['https://www.instagram.com/skylinkmedia.in/', 'https://www.facebook.com/skylinkmediaindia'],
    ],
    [
      '@type' => 'WebSite',
      '@id' => 'https://skylinkmedia.co.in/#website',
      'name' => 'Skylink Media',
      'url' => 'https://skylinkmedia.co.in/',
      'publisher' => ['@id' => 'https://skylinkmedia.co.in/#organization'],
    ],
  ];
  if ($pageBreadcrumbs) {
    $ldGraph[] = [
      '@type' => 'BreadcrumbList',
      'itemListElement' => array_map(function ($crumb, $i) {
        return ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $crumb[0], 'item' => $crumb[1]];
      }, $pageBreadcrumbs, array_keys($pageBreadcrumbs)),
    ];
  }
  if ($pageLocalBusiness) {
    $ldGraph[] = [
      '@type' => 'LocalBusiness',
      '@id' => 'https://skylinkmedia.co.in/#localbusiness',
      'name' => 'Skylink Media',
      'description' => 'Digital marketing agency in Indore offering SEO, social media marketing, Meta Ads, Google Ads, and web design & development.',
      'url' => 'https://skylinkmedia.co.in/',
      'image' => 'https://skylinkmedia.co.in/images/logo-5.webp',
      'logo' => 'https://skylinkmedia.co.in/images/logo-5.webp',
      'email' => 'skylinkmedia.co@gmail.com',
      'telephone' => '+91 96692 07207',
      'priceRange' => '₹₹',
      'address' => ['@type' => 'PostalAddress', 'addressLocality' => 'Indore', 'addressRegion' => 'Madhya Pradesh', 'addressCountry' => 'IN'],
      'areaServed' => [['@type' => 'City', 'name' => 'Indore'], ['@type' => 'Country', 'name' => 'India']],
      'sameAs' => ['https://www.instagram.com/skylinkmedia.in/', 'https://www.facebook.com/skylinkmediaindia'],
      'parentOrganization' => ['@id' => 'https://skylinkmedia.co.in/#organization'],
      'hasOfferCatalog' => [
        '@type' => 'OfferCatalog',
        'name' => 'Digital marketing services',
        'itemListElement' => array_map(function ($service) {
          return ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => $service['label'], 'url' => 'https://skylinkmedia.co.in/' . $service['href']]];
        }, array_values($services)),
      ],
    ];
  }
  foreach ($pageSchemaExtra as $extraNode) {
    $ldGraph[] = $extraNode;
  }
  ?>
  <script type="application/ld+json"><?= json_encode([
    '@context' => 'https://schema.org',
    '@graph' => $ldGraph,
  ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
</head>
<body>
    <!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MBHRMZGN"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
  <div class="noise"></div>
  <header class="site-header">
    <a class="brand" href="<?= $base ?>" aria-label="Skylink Media home"><img class="brand-logo" src="<?= $base ?>images/logo-5.webp" alt="Skylink Media" width="45" height="34"></a>
    <nav class="main-nav" aria-label="Main navigation">
      <div class="nav-item has-dropdown">
        <a href="<?= $base ?>#services">Services</a>
        <button class="dropdown-toggle" aria-label="Toggle services menu" aria-expanded="false">▾</button>
        <div class="dropdown-menu">
          <?php foreach ($services as $slug => $service): ?>
          <a href="<?= $base . $service['href'] ?>"<?= $activeService === $slug ? ' class="is-active"' : '' ?>><?= htmlspecialchars($service['label']) ?></a>
          <?php endforeach; ?>
        </div>
      </div>
      <a href="<?= $base ?>our-work"<?= $isWork ? ' class="is-current"' : '' ?>>Our work</a>
      <a href="<?= $base ?>about.php"<?= $isAbout ? ' class="is-current"' : '' ?>>About us</a>
      <a href="<?= $base ?>blog/"<?= $isBlog ? ' class="is-current"' : '' ?>>Blog</a>
      <a href="<?= $base ?>contact.php"<?= $isContact ? ' class="is-current"' : '' ?>>Contact us</a>
    </nav>
    <a class="header-cta site-btn" href="<?= $base ?>#contact">Let's talk <span>↗</span></a>
    <button class="menu-toggle" aria-label="Toggle menu" aria-expanded="false"><span></span><span></span></button>
  </header>

  <main>
