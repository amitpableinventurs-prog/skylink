<?php
$base = './';
$isContact = true;
$pageLocalBusiness = true;
$pageTitle = 'Contact Us | Skylink Media — Digital Marketing Agency in Indore';
$pageDescription = 'Get in touch with Skylink Media for SEO, social media, Meta Ads, Google Ads, and web design services. Email, call, or send an enquiry — we reply within two working days.';
$pageCanonical = 'https://skylinkmedia.co.in/contact.php';
$pageOgImage = 'https://skylinkmedia.co.in/images/logo-5.webp';
$pageBreadcrumbs = [['Home', 'https://skylinkmedia.co.in/'], ['Contact us', $pageCanonical]];
$pageSchemaExtra = [
  [
    '@type' => 'ContactPage',
    'name' => 'Contact Skylink Media',
    'url' => $pageCanonical,
    'about' => ['@id' => 'https://skylinkmedia.co.in/#organization'],
  ],
];
require __DIR__ . '/includes/header.php';
?>
    <section class="service-hero section-pad">
      <p class="eyebrow"><span class="eyebrow-line"></span> Get in touch</p>
      <h1>Let's start<br>a <em>conversation</em>.</h1>
      <p class="service-intro">Tell us a little about your business and where you want it to go. Whether it's SEO, ads, social, or a full website rebuild &mdash; we'll get back to you within two working days.</p>
    </section>

    <section class="service-block is-tight">
      <div class="case-facts reveal">
        <div><span><?= svc_icon('mail', 'svc-ic') ?> Email</span><strong><a href="mailto:skylinkmedia.co@gmail.com">skylinkmedia.co@gmail.com</a></strong></div>
        <div><span><?= svc_icon('phone', 'svc-ic') ?> Phone</span><strong><a href="tel:+919669207207">+91 96692 07207</a></strong></div>
        <div><span><?= svc_icon('pin', 'svc-ic') ?> Location</span><strong>Indore, Madhya Pradesh</strong></div>
        <div><span><?= svc_icon('instagram', 'svc-ic') ?> Follow</span><strong><a href="https://www.instagram.com/skylinkmedia.in/" target="_blank" rel="noopener">@skylinkmedia.in</a></strong></div>
      </div>
    </section>

    <section class="contact section-pad" id="contact">
      <div class="contact-intro reveal">
        <p class="eyebrow"><span class="eyebrow-line"></span> Your next chapter</p>
        <h2>Tell us about<br><em>your project</em>.</h2>
        <p>Share a few details below. A real person on our team reads every enquiry and replies within two working days.</p>
        <div class="contact-details"><a href="mailto:skylinkmedia.co@gmail.com">skylinkmedia.co@gmail.com</a><a href="tel:+919669207207">+91 96692 07207</a></div>
      </div>
      <form class="contact-form reveal reveal-delay" id="contact-form" action="php/contact.php" method="post" novalidate>
        <label>Your name<input type="text" name="name" placeholder="Jane Smith" required maxlength="120"></label>
        <label>Work email<input type="email" name="email" placeholder="jane@company.com" required maxlength="160"></label>
        <label>Mobile number<input type="tel" name="phone" placeholder="+91 98765 43210" required maxlength="20" inputmode="tel" autocomplete="tel"></label>
        <div class="field-select"><span class="field-label" id="service-label">What can we help with?</span><select name="service" required aria-labelledby="service-label"><option value="" disabled selected>Select a service</option><option>Search visibility</option><option>Social presence</option><option>Performance marketing</option><option>Website / digital experience</option><option>Something else</option></select></div>
        <label>Tell us a little more<textarea name="message" rows="3" placeholder="The more context, the better..." maxlength="3000"></textarea></label>
        <label class="hp-field" aria-hidden="true">Leave this empty<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
        <button class="button button-lime site-btn" type="submit">Send enquiry <span>&#8599;</span></button>
        <p class="form-status" role="status"></p>
      </form>
    </section>
<?php require __DIR__ . '/includes/footer.php'; ?>
