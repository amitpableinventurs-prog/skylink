<?php
$base = './';
$isAbout = true;
$pageTitle = 'About Skylink Media | Independent Growth Studio in Indore';
$pageDescription = 'Learn about Skylink Media, a digital marketing agency in Indore helping ambitious brands grow with strategy, creative, SEO, and performance marketing.';
$pageCanonical = 'https://skylinkmedia.co.in/about.php';
$pageOgImage = 'https://skylinkmedia.co.in/images/shashank.webp';
$pageBreadcrumbs = [['Home', 'https://skylinkmedia.co.in/'], ['About us', $pageCanonical]];
require __DIR__ . '/includes/header.php';
?>
    <script type="application/ld+json">
    <?= json_encode([
      '@context' => 'https://schema.org',
      '@type' => 'AboutPage',
      'name' => 'About Skylink Media',
      'description' => $pageDescription,
      'url' => $pageCanonical,
      'isPartOf' => ['@type' => 'WebSite', 'name' => 'Skylink Media', 'url' => 'https://skylinkmedia.co.in/'],
      'about' => ['@type' => 'Organization', 'name' => 'Skylink Media', 'url' => 'https://skylinkmedia.co.in/', 'email' => 'skylinkmedia.co@gmail.com', 'telephone' => '+91 96692 07207', 'address' => ['@type' => 'PostalAddress', 'addressLocality' => 'Indore', 'addressCountry' => 'IN']]
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
    </script>
    <section class="about-hero section-pad">
      <div class="about-hero-copy reveal">
        <p class="eyebrow"><span class="eyebrow-line"></span> About Skylink Media / Indore, India</p>
        <h1>Good growth<br>starts with a<br><em>clear point</em><span class="lime">.</span></h1>
        <p class="service-intro">We are an independent growth studio for ambitious brands. We bring strategy, expressive creative, and performance marketing together so your next move has somewhere to go.</p>
        <div class="hero-actions"><a class="button button-lime site-btn" href="./#contact">Start a project <span>&#8599;</span></a><a class="text-link site-btn" href="#approach">How we work <span>&#8595;</span></a></div>
      </div>
      <div class="about-hero-art reveal reveal-delay" aria-hidden="true"><div class="about-art-circle"><span>360°</span></div><div class="about-art-note">Strategy<br>meets action.</div><div class="about-art-ring"></div></div>
    </section>

    <section class="about-story section-pad" id="approach">
      <div class="section-heading reveal"><p class="eyebrow"><span class="eyebrow-line"></span> The Skylink way</p><h2>We see the <em>whole</em><br>picture.</h2></div>
      <div class="about-story-copy reveal reveal-delay"><p>Skylink Media is a digital marketing agency in Indore helping ambitious brands turn attention into measurable growth. Growth rarely comes from one channel, one campaign, or one lucky idea. It comes from connecting the dots.</p><p>That is why we work across the full journey: finding the right audience, saying something worth noticing, and building digital experiences that turn attention into action.</p></div>
    </section>

    <section class="about-principles section-pad">
      <div class="about-principles-heading reveal"><p class="eyebrow"><span class="eyebrow-line"></span> What guides us</p><h2>Simple ideas.<br><em>Serious</em> intent.</h2></div>
      <div class="principles about-principles-list reveal reveal-delay"><div><span>01</span><strong>Curious by default</strong><p>We ask better questions before we make big moves.</p></div><div><span>02</span><strong>Clear over clever</strong><p>Simple ideas travel further. Especially online.</p></div><div><span>03</span><strong>Outcomes first</strong><p>Beautiful is better when it performs.</p></div><div><span>04</span><strong>Partners, not vendors</strong><p>We stay close, communicate clearly, and care about the result.</p></div></div>
    </section>

    <section class="about-services service-block">
      <p class="eyebrow reveal"><span class="eyebrow-line"></span> One connected team</p><h2 class="svc-h2 reveal">From first thought<br>to <em>real momentum.</em></h2>
      <div class="about-service-list reveal"><a href="seo-services-in-indore"><span>01</span><strong>Search visibility</strong><b>&#8599;</b></a><a href="social-media-marketing-services-in-indore"><span>02</span><strong>Social presence</strong><b>&#8599;</b></a><a href="meta-ads-services-in-indore"><span>03</span><strong>Meta ads</strong><b>&#8599;</b></a><a href="google-ads-services-in-indore"><span>04</span><strong>Google ads</strong><b>&#8599;</b></a><a href="web-design-development-services-in-indore"><span>05</span><strong>Websites that work</strong><b>&#8599;</b></a></div>
    </section>

    <section class="about-team section-pad" id="team">
      <div class="section-heading reveal"><p class="eyebrow"><span class="eyebrow-line"></span> The people</p><h2>A small team,<br><em>wide</em> range.</h2></div>
      <div class="team-lead reveal reveal-delay">
        <figure class="team-lead-photo"><img src="images/shashank.webp" alt="Shashank Maheshwari, Founder and CEO of Skylink Media" loading="lazy"></figure>
        <div class="team-lead-copy">
          <span class="team-role">Founder &amp; CEO</span>
          <h3>Shashank Maheshwari</h3>
          <p>Shashank leads strategy and growth at Skylink Media, working closely with every client to turn ambitious goals into measurable outcomes &mdash; with a simple belief that good marketing is honest, accountable, and built for the long term.</p>
          <a class="text-link site-btn" href="./#contact">Work with Shashank <span>&#8599;</span></a>
        </div>
      </div>
      <div class="team-grid reveal">
        <article class="team-member">
          <figure class="team-photo"><img src="images/priyansh.webp" alt="Priyansh Pachori" loading="lazy"></figure>
          <h4>Priyansh Pachori</h4>
          <span>Senior Graphic Designer &amp; Video Editor</span>
        </article>
        <article class="team-member">
          <figure class="team-photo"><img src="images/ayushi.webp" alt="Ayushi Dharwa" loading="lazy"></figure>
          <h4>Ayushi Dharwa</h4>
          <span>Digital Marketing Executive</span>
        </article>
        <article class="team-member">
          <figure class="team-photo"><img src="images/priya.webp" alt="Priya Agarwal" loading="lazy"></figure>
          <h4>Priya Agarwal</h4>
          <span>Senior Graphic Designer &amp; Video Editor</span>
        </article>
      </div>
    </section>

    <section class="service-cta"><h2>Ready to make your next move matter?</h2><a class="button button-ink site-btn" href="./#contact">Let's talk <span>&#8599;</span></a></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
