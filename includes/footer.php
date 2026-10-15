<?php
$base = $base ?? '../';
?>
  </main>
  <footer class="site-footer">
    <div class="footer-inner">
      <div class="footer-top">
        <div class="footer-lead">
          <a class="brand" href="<?= $base ?>" aria-label="Skylink Media home"><img class="brand-logo" src="<?= $base ?>images/logo-5.webp" alt="Skylink Media" width="50" height="38"></a>
          <p class="footer-tagline">Growth,<br>with a point of view.</p>
          <form class="newsletter-form" id="newsletter-form" action="<?= $base ?>php/subscribe.php" method="post" novalidate>
            <label for="newsletter-email">Get growth notes, monthly</label>
            <div class="newsletter-row">
              <input id="newsletter-email" type="email" name="email" placeholder="you@company.com" required maxlength="160">
              <button class="button button-ink site-btn" type="submit">Subscribe <span>↗</span></button>
            </div>
            <label class="hp-field" aria-hidden="true">Leave this empty<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
            <p class="form-status newsletter-status" role="status"></p>
          </form>
        </div>
        <nav class="footer-nav" aria-label="Footer">
          <div class="footer-col">
            <h3>Services</h3>
            <a href="<?= $base ?>seo-services-in-indore">SEO</a>
            <a href="<?= $base ?>social-media-marketing-services-in-indore">Social Media Marketing</a>
            <a href="<?= $base ?>meta-ads-services-in-indore">Meta Ads</a>
            <a href="<?= $base ?>google-ads-services-in-indore">Google Ads</a>
            <a href="<?= $base ?>web-design-development-services-in-indore">Web Design &amp; Development</a>
          </div>
          <div class="footer-col">
            <h3>Studio</h3>
            <a href="<?= $base ?>our-work">Our work</a>
            <a href="<?= $base ?>about.php">About us</a>
            <a href="<?= $base ?>blog/">Blog</a>
            <a href="<?= $base ?>contact.php">Contact us</a>
          </div>
          <div class="footer-col">
            <h3>Connect</h3>
            <a href="mailto:skylinkmedia.co@gmail.com">skylinkmedia.co@gmail.com</a>
            <a href="tel:+919669207207">+91 96692 07207</a>
            <span class="footer-loc">Indore, India</span>
          </div>
        </nav>
      </div>
      <div class="footer-bottom">
        <span class="mono">© 2026 SKYLINK MEDIA</span>
        <div class="footer-social">
          <a href="https://www.instagram.com/skylinkmedia.in/" target="_blank" rel="noopener" aria-label="Skylink Media on Instagram"><?= svc_icon('instagram', 'footer-social-ic') ?></a>
          <a href="https://www.facebook.com/skylinkmediaindia" target="_blank" rel="noopener" aria-label="Skylink Media on Facebook"><?= svc_icon('facebook', 'footer-social-ic') ?></a>
        </div>
        <a class="footer-back" href="<?= $base ?>#top">Back to top <span aria-hidden="true">↑</span></a>
      </div>
    </div>
  </footer>
  <script src="<?= $base ?>script.js"></script>
</body>
</html>
