<?php
$base = './';
$isWork = true;
$pageTitle = 'Our Work | Case Studies by Skylink Media — Digital Marketing Agency in Indore';
$pageDescription = 'See how Skylink Media helps brands like Palladio City, Krishna Heights, SA Majestic Realty, and Makoons Preschool grow with brand, SEO, social, and performance marketing.';
$pageCanonical = 'https://skylinkmedia.co.in/our-work';
$pageOgImage = 'https://skylinkmedia.co.in/images/proj-1.jpg';
$pageBreadcrumbs = [['Home', 'https://skylinkmedia.co.in/'], ['Our Work', $pageCanonical]];
$pageSchemaExtra = [
  [
    '@type' => 'CollectionPage',
    'name' => 'Our Work — Skylink Media',
    'url' => $pageCanonical,
    'about' => ['@id' => 'https://skylinkmedia.co.in/#organization'],
  ],
];
require __DIR__ . '/includes/header.php';
?>
    <section class="service-hero section-pad">
      <h1 class="eyebrow hero-kicker"><span class="eyebrow-line"></span> Our Work &mdash; Digital Marketing Case Studies</h1>
      <p class="service-title">Good work gets<br><em>noticed</em>.</p>
      <p class="service-intro">A look at the brands we've helped launch, grow, and get found &mdash; from Indore real estate to early education. Every project pairs a clear strategy with creative and performance that pull in the same direction.</p>
      <div class="hero-actions"><a class="button button-lime site-btn" href="./#contact">Start your case study <span>&#8599;</span></a></div>
    </section>

    <section class="work section-pad" id="work">
      <div class="work-heading"><div class="section-heading reveal"><p class="eyebrow"><span class="eyebrow-line"></span> Selected work</p><h2>Case studies<br>that <em>speak</em>.</h2></div><a class="text-link site-btn" href="./#contact">Start your case study <span>↗</span></a></div>
      <div class="project-grid">
        <a class="project project-large reveal" href="work/palladio-city.php"><div class="project-image duotone-coral" style="background-image:url('images/proj-1.jpg')"><span class="image-label">PALLADIO<br>GROUP</span><span class="image-stamp">EST. 1998</span></div><div class="project-meta"><div><span class="project-type">Case Study &mdash; Brand + Digital</span><h3>Making a legacy feel like tomorrow.</h3></div><span class="project-arrow">↗</span></div></a>
        <a class="project project-small reveal reveal-delay" href="work/krishna-heights.php"><div class="project-image duotone-lime" style="background-image:url('images/proj-2.jpg')"><span class="image-label">KRISHNA<br>HEIGHTS</span><span class="image-stamp">LIVE / WORK</span></div><div class="project-meta"><div><span class="project-type">Case Study &mdash; Performance</span><h3>More eyes on the view.</h3></div><span class="project-arrow">↗</span></div></a>
        <a class="project project-large reveal" href="work/majestic-realty.php"><div class="project-image duotone-lime" style="background-image:url('images/proj-3.jpg')"><span class="image-label">MAJESTIC<br>REALTY</span><span class="image-stamp">SINCE 2010</span></div><div class="project-meta"><div><span class="project-type">Case Study &mdash; Brand + Performance</span><h3>Selling the skyline, not just the square footage.</h3></div><span class="project-arrow">↗</span></div></a>
        <a class="project project-small reveal reveal-delay" href="work/makoons-preschool.php"><div class="project-image duotone-coral" style="background-image:url('images/proj-4.jpg')"><span class="image-label">MAKOONS<br>PRESCHOOL</span><span class="image-stamp">EARLY ED.</span></div><div class="project-meta"><div><span class="project-type">Case Study &mdash; Social + Growth</span><h3>Curious parents, into enrolled families.</h3></div><span class="project-arrow">↗</span></div></a>
      </div>
    </section>

    <section class="clients section-pad" id="clients">
      <div class="section-heading reveal"><p class="eyebrow"><span class="eyebrow-line"></span> Brands we've grown</p><h2>Trusted by names<br>that <em>matter</em>.</h2></div>
      <div class="client-grid">
        <a class="client-card reveal" href="work/palladio-city.php"><img class="is-wide" src="images/LOGO-PALLADO-trim.webp" alt="Palladio City" loading="lazy"></a>
        <a class="client-card reveal reveal-delay" href="work/krishna-heights.php"><img src="images/KH_LOGO.webp" alt="Krishna Heights" loading="lazy"></a>
        <a class="client-card reveal" href="work/majestic-realty.php"><img src="images/LOGO-MAJESTIC.webp" alt="SA Majestic Realty" loading="lazy"></a>
        <a class="client-card reveal reveal-delay" href="work/makoons-preschool.php"><img class="is-wide" src="images/Makoons-trim.webp" alt="Makoons Preschool" loading="lazy"></a>
        <div class="client-card reveal"><img src="images/Kidzee.webp" alt="Kidzee" loading="lazy"></div>
        <div class="client-card reveal reveal-delay"><img src="images/Solar.webp" alt="Eagles Solar" loading="lazy"></div>
      </div>
    </section>

    <section class="service-cta"><h2>Want to be our next case study?</h2><a class="button button-ink site-btn" href="./#contact">Let's talk <span>&#8599;</span></a></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
