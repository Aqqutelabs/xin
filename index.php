<?php
require_once __DIR__ . '/config.php';
session_start();
$isSignedIn = !empty($_SESSION['user_id']);
$pageAction = $isSignedIn ? 'pages.php' : 'signup.php';
$qrError = $_SESSION['qr_flash'] ?? '';
unset($_SESSION['qr_flash']);
$faqs = [
 ['What is Xinng?', 'Xinng is a digital home for you and your business. Bring your profile, work, services and important links together on a page, then share it through your Xinng address and QR code. It is for professionals, founders, businesses, creators, public figures, organisations and anyone with more to show.'],
 ['How is a Xinng Page different from a link-in-bio page?', 'You can keep your page simple and elegant or build a detailed professional profile, portfolio or company presence. Your page gives you room for detailed information and organised sections alongside your links, with space to add more whenever you need it.'],
 ['Can my business use Xinng as a corporate page?', 'Yes. Bring your company introduction, services, projects, documents and contact information together in a structured page.'],
 ['What can I add to my page?', 'Introduce yourself, describe your services, share projects and media, and organise your contact details and important links. Available sections depend on your page type.'],
 ['Do I get my own Xinng address?', 'Yes. Choose an available address such as xin.ng/yourname or xin.ng/yourbusiness when you sign up.'],
 ['Can I share my page through a QR code?', 'Yes. Create a QR code pointing to your page and use it on business cards, packaging, presentations or other materials. Your QR code can also point to another website.'],
];
function home_icon(string $name): string {
 $paths = [
 'arrow'=>'<path d="M5 12h14m-6-6 6 6-6 6"/>',
 'page'=>'<rect x="5" y="3" width="14" height="18" rx="2"/><path d="M9 8h6M9 12h6M9 16h3"/>',
 'qr'=>'<path d="M3 3h6v6H3zM15 3h6v6h-6zM3 15h6v6H3zM15 15h2v2h-2zM21 14v4h-3v3M14 21h2"/>',
 'home'=>'<path d="m3 10 9-7 9 7v10H3zM9 20v-7h6v7"/>',
 'work'=>'<rect x="3" y="7" width="18" height="14" rx="2"/><path d="M8 7V3h8v4M3 12c6 3 12 3 18 0M12 12v4"/>',
 'link'=>'<path d="m10 14 4-4M8 16l-2 2a4 4 0 0 1-5-5l5-5a4 4 0 0 1 5 0m2 0 2-2a4 4 0 0 1 5 5l-5 5a4 4 0 0 1-5 0"/>',
 'spark'=>'<path d="m12 3 2.4 6.6L21 12l-6.6 2.4L12 21l-2.4-6.6L3 12l6.6-2.4z"/>',
 'play'=>'<path d="m9 5 11 7-11 7z"/>',
 'check'=>'<path d="m5 12 4 4L19 6"/>',
 ];
 return '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($paths[$name] ?? $paths['page']).'</svg>';
}
?>
<!doctype html>
<html lang="en">
<head>
 <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
 <title>Xinng — A home for you and your business</title>
 <meta name="description" content="Build a professional page for your story, work and business. Share it anywhere with your Xinng address, clean short links and QR code.">
 <meta name="theme-color" content="#4a1012">
 <meta property="og:title" content="Xinng is home. For the different sides of you and your business.">
 <meta property="og:description" content="Your story. Your work. Your business. One address to bring it all together.">
 <meta property="og:type" content="website">
 <link rel="icon" href="assets/images/logo-icon.svg" type="image/svg+xml">
 <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
 <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;450;500;600;650;700;750;800&display=swap" rel="stylesheet">
 <link rel="stylesheet" href="assets/css/ember-tokens.css"><link rel="stylesheet" href="assets/css/brand.css">
 <link rel="stylesheet" href="assets/css/home.css?v=<?= filemtime(__DIR__.'/assets/css/home.css') ?>">
 <script src="assets/js/home.js?v=<?= filemtime(__DIR__.'/assets/js/home.js') ?>" defer></script>
</head>
<body>
<a class="skip-link" href="#main">Skip to content</a>
<header class="site-header">
 <div class="wrap nav-row">
  <a class="xinng-brand" href="index.php" aria-label="Xinng home"><img class="xinng-brand__image" src="assets/images/logo.svg" alt="Xinng" width="1736" height="906"></a>
  <button class="menu-toggle" aria-expanded="false" aria-controls="main-nav">Menu <span aria-hidden="true">☰</span></button>
  <nav id="main-nav" aria-label="Main navigation">
   <details class="product-menu"><summary>Products <span aria-hidden="true">⌄</span></summary><div class="dropdown"><a href="#pages"><?= home_icon('page') ?><span><strong>Xinng Pages</strong><small>A home for your different sides</small></span></a><a href="#qr"><?= home_icon('qr') ?><span><strong>Xinng QR</strong><small>Create a QR code for any web link</small></span></a></div></details>
   <details class="product-menu"><summary>Tools <span aria-hidden="true">&#8964;</span></summary><div class="dropdown"><a href="tools/teleprompter/" data-track="teleprompter"><?= home_icon('play') ?><span><strong>Teleprompter</strong><small>Follow your script at your own pace</small></span></a><a href="tools/creator-calculator/x/" data-track="creator-calculator"><?= home_icon('work') ?><span><strong>Creator Calculator</strong><small>Check your progress toward X monetization</small></span></a></div></details><a href="#learn">Learn</a><a href="pricing.php">Pricing</a>
   <div class="nav-account"><?php if (!$isSignedIn): ?><a href="signin.php">Sign in</a><?php endif; ?><a class="button small" href="<?= $pageAction ?>" data-track="pages"><?= $isSignedIn ? 'My pages' : 'Get started' ?> <?= home_icon('arrow') ?></a></div>
  </nav>
 </div>
</header>
<main id="main">
<section class="hero">
 <div class="wrap hero-grid">
  <div class="hero-copy"><p class="eyebrow light"><span class="tiny-dot"></span> YOUR WORLD. ONE ADDRESS.</p>
   <h1>Xinng is <span>home.</span></h1>
   <p class="hero-lead">For the different sides of<br class="desktop-break"> you and your business.</p>
   <p class="hero-description">Bring your story, services, projects and important links together on one professional page. Share it anywhere with your Xinng address and QR code.</p>
   <?php if (!$isSignedIn): ?>
   <form class="claim-form" action="signup.php" method="get" data-claim data-reserved="<?= e(json_encode(xinng_reserved_back_halves())) ?>">
    <label for="home-slug">Your address starts here</label><div class="claim-row"><div class="address-input"><span>xin.ng/</span><input id="home-slug" name="slug" placeholder="yourname" minlength="3" maxlength="64" pattern="[a-zA-Z0-9][a-zA-Z0-9_-]{1,62}[a-zA-Z0-9]" aria-describedby="claim-status" required autocomplete="off" spellcheck="false"></div><button class="button" type="submit">Claim your page <?= home_icon('arrow') ?></button></div>
    <p class="claim-status" id="claim-status" aria-live="polite">Choose your address. Make yourself at home.</p>
   </form>
   <?php else: ?><a class="button" href="pages.php" data-track="pages">Build your Xinng Page <?= home_icon('arrow') ?></a><?php endif; ?>
   <a class="text-link light-link" href="#showcase">See what you can build <span aria-hidden="true">↗</span></a>
  </div>
  <div class="hero-art">
   <div class="art-label"><?= home_icon('spark') ?> A little more you. All in one place.</div>
   <a class="browser-preview" href="https://xin.ng/PHIF" aria-label="Explore the PHIF page"><div class="browser-bar"><span class="browser-dots" aria-hidden="true">● ● ●</span><span>xin.ng/PHIF</span><span aria-hidden="true">↗</span></div><img src="assets/images/PHIF-page-example.png" alt="PHIF page preview" width="1024" height="1536" fetchpriority="high"></a>
   <div class="floating-address"><?= home_icon('link') ?><div><small>ONE LINK. YOUR WHOLE WORLD.</small><strong>xin.ng/PHIF</strong></div><span class="green-check"><?= home_icon('check') ?></span></div>
   <a class="hero-qr" href="https://xin.ng/PHIF" aria-label="Visit PHIF at xin.ng/PHIF"><span class="hero-qr-label">SCAN. CONNECT.</span><img src="assets/images/phif-qr.svg" width="144" height="144" alt="QR code linking to https://xin.ng/PHIF"><strong>Your page, one scan away.</strong><span>xin.ng/PHIF</span></a>
   <div class="side-label"><span>YOUR STORY</span><span>YOUR WORK</span><span>YOUR BUSINESS</span></div>
  </div>
 </div>
 <div class="wrap hero-bottom"><span>A place for everyone with more to show.</span><div><span>Professionals</span><span>Founders</span><span>Businesses</span><span>Creators</span><span>Organisations</span></div></div>
</section>
<section class="section idea-section">
 <div class="wrap split">
  <div><p class="eyebrow">THE XINNG IDEA</p><h2>You’re more than<br>a list of links.</h2><p class="body-large">We are building a nest for your different sides.</p><p>You have work to showcase, ideas to share and people to connect with. You might run a business, lead a project and build a personal brand—all at once.</p><p>Give those different sides a place to belong, so people can understand what you do and find what matters to them.</p><strong class="closing-line">Your story. Your work. Your business. Your home.</strong></div>
  <div class="nest" aria-label="Your story, work, ideas and business together in one home"><div class="nest-ring ring-one"></div><div class="nest-ring ring-two"></div><div class="nest-home"><?= home_icon('home') ?><strong>All of you.</strong><span>One home.</span></div><span class="nest-chip chip-one"><?= home_icon('work') ?> Your business</span><span class="nest-chip chip-two"><?= home_icon('page') ?> Your story</span><span class="nest-chip chip-three"><?= home_icon('spark') ?> Your ideas</span><span class="nest-chip chip-four"><?= home_icon('link') ?> Your work</span></div>
 </div>
</section>
<section class="section pages-section" id="pages">
 <div class="wrap"><div class="section-heading"><div><p class="eyebrow">MEET XINNG PAGES</p><h2>More room for<br>what you do.</h2></div><p>A short introduction. A detailed portfolio.<br>A comprehensive company profile.<br>Make space for the full picture.</p></div>
 <div class="pages-layout"><div class="page-outline"><span class="label">A PLACE FOR EVERY PART OF YOUR STORY</span><div class="outline-title"><?= home_icon('page') ?><strong>Your Xinng Page</strong></div><?php foreach (['01' => ['Introduce yourself', 'Your story, in your own words'], '02' => ['Show what you do', 'Services, expertise and capabilities'], '03' => ['Let your work speak', 'Projects, portfolios and selected work'], '04' => ['Make the connection', 'Contact details and important links']] as $n => $item): ?><div class="outline-row"><span><?= $n ?></span><div><strong><?= $item[0] ?></strong><small><?= $item[1] ?></small></div><?= home_icon('check') ?></div><?php endforeach; ?><div class="outline-footer">Made to grow with you. <?= home_icon('spark') ?></div></div>
 <div class="pages-slider" id="showcase" role="region" aria-roledescription="carousel" aria-label="Xinng page inspiration">
  <div class="pages-slider-stage">
   <?php $pageExamples = [
    ['Aquila', 'home-page-example.png', 'Aquila professional page with biography, expertise, contact options and portfolio'],
    ['Professional', 'AMO-page-example.png', 'Amara Okafor professional page with an introduction, services and insights'],
    ['Business', 'micony-page-example.png', 'Micony business page with company information and core capabilities'],
    ['Event', 'FNF-page-example.png', 'Form and Future event page with a programme, speakers and resources'],
    ['Coffee shop', 'MRW-page-example.png', 'Morrow Coffee link page with menu, ordering, location and contact links'],
    ['Creator', 'TWA-page-example.png', 'Tomi Waves creator link page with music, videos, shows and booking links'],
   ]; foreach ($pageExamples as $i => [$category, $filename, $description]):
    $imageSize = getimagesize(__DIR__ . '/assets/images/' . $filename);
    $slot = $i > floor((count($pageExamples) - 1) / 2) ? $i - count($pageExamples) : $i;
   ?>
   <a class="pages-slide" data-slot="<?= $slot ?>" data-title="<?= e($category) ?>" href="<?= $category === 'Aquila' ? 'aquila/' : 'assets/images/' . e($filename) ?>" target="_blank" rel="noopener" data-track="showcase" aria-label="<?= e($description) ?> (opens in a new tab)" <?= $i !== 0 ? 'tabindex="-1" aria-hidden="true"' : '' ?>>
    <img src="assets/images/<?= e($filename) ?>" alt="<?= e($description) ?>" width="<?= $imageSize[0] ?>" height="<?= $imageSize[1] ?>" loading="lazy" decoding="async">
   </a>
   <?php endforeach; ?>
  </div>
  <div class="pages-slider-controls" hidden>
   <button type="button" data-slide-prev aria-label="Previous page">&larr;</button>
   <span class="pages-slider-status" aria-live="polite" aria-atomic="true">Aquila <span>01 / <?= str_pad((string) count($pageExamples), 2, '0', STR_PAD_LEFT) ?></span></span>
   <button type="button" data-slide-next aria-label="Next page">&rarr;</button>
   <button type="button" data-slide-play aria-label="Pause slideshow">Pause</button>
  </div>
  <a class="text-link pages-slider-cta" href="<?= $pageAction ?>" data-track="pages">Explore Xinng Pages <?= home_icon('arrow') ?></a>
 </div></div>
 <div class="short-link-note"><?= home_icon('link') ?><strong>Shorter links. Easier sharing.</strong><p>Turn long web addresses into clean xin.ng links for messages, campaigns and documents.</p><span>xin.ng/your-next-thing ↗</span></div></div>
</section>
<section class="section qr-section" id="qr"><div class="wrap split"><div><p class="eyebrow">MEET XINNG QR</p><h2>Turn any web link<br>into a QR code.</h2><p class="body-large">Create a scannable code for your Xinng Page, website, online menu or registration form.</p><p>Paste your destination URL, generate your QR code and preview it. Download it as a PNG image or an SVG file for print. When someone scans the code with their phone camera, it opens your link.</p><div class="qr-uses"><span><?= home_icon('check') ?> Business cards & introductions</span><span><?= home_icon('check') ?> Packaging & storefronts</span><span><?= home_icon('check') ?> Events, presentations & print</span></div></div>
 <div class="qr-generator"><div class="generator-heading"><span class="icon-tile"><?= home_icon('qr') ?></span><div><h3>Create your QR code</h3><p>Paste the web link you want people to open.</p></div></div><form action="create_qr.php" method="post" data-track-form="qr"><label for="qr-destination">Destination URL</label><input id="qr-destination" type="url" name="destination" placeholder="https://xin.ng/yourname" required><input type="hidden" name="qr_type" value="Website"><input type="hidden" name="style" value="Standard"><?php if ($qrError): ?><p class="form-error" role="alert"><?= e($qrError) ?></p><?php endif; ?><button class="button" type="submit">Create a QR code <?= home_icon('arrow') ?></button></form><div class="generator-note"><?= home_icon('qr') ?><p>Preview and download your QR code as PNG or SVG.<br>No account needed. Sign up to save it to your account.</p></div></div>
</div></section>
<section class="analytics-note wrap" aria-labelledby="analytics-heading"><div><p class="eyebrow">TRACKING &amp; ANALYTICS</p><h2 id="analytics-heading">See what happens after you share.</h2><p>Your Insights dashboard brings page views, link clicks and tracked QR scans together. Review activity over the last seven days and see which links and QR codes get the most attention.</p><p class="analytics-detail">For scan tracking, create a QR code in your account that uses a Xinng tracking link. Codes that point directly to an external website do not report scans to Xinng.</p><a class="text-link" href="<?= $isSignedIn ? 'insights.php' : 'signup.php' ?>" data-track="analytics"><?= $isSignedIn ? 'View your insights' : 'Get started with tracking' ?> <?= home_icon('arrow') ?></a></div><ul class="analytics-signals"><li><?= home_icon('page') ?><div><strong>Page views</strong><span>See visits to your page.</span></div></li><li><?= home_icon('link') ?><div><strong>Link clicks</strong><span>Find the links people open.</span></div></li><li><?= home_icon('qr') ?><div><strong>QR scans</strong><span>Measure activity on tracked codes.</span></div></li></ul></section>
<section class="section" id="how-it-works"><div class="wrap"><div class="center-heading"><p class="eyebrow">MAKE YOURSELF AT HOME</p><h2>From introduction to connection.</h2><p>Three simple steps. A whole new place for your world.</p></div><div class="steps"><article><span class="step-number">01</span><h3>Claim your address.</h3><p>Choose an available xin.ng address for yourself or your business.</p><div class="step-visual">xin.ng/<strong>yourname</strong> <?= home_icon('check') ?></div></article><article><span class="step-number">02</span><h3>Build your home.</h3><p>Add your introduction, services, work and the information people need.</p><div class="step-visual"><?= home_icon('page') ?> Your story <span class="plus">+</span> Your work</div></article><article><span class="step-number">03</span><h3>Share it anywhere.</h3><p>In your bio, your messages or in person. One link and a QR code open the door.</p><div class="step-visual"><?= home_icon('link') ?> Online <span class="plus">+</span> <?= home_icon('qr') ?> In person</div></article></div></div></section>
<section class="why-section"><div class="wrap"><p class="eyebrow light">WHY XINNG</p><div class="section-heading"><h2>Simple to start.<br>Room for more.</h2><p>Start with what matters today.<br>Make room for what comes next.</p></div><div class="why-grid"><article><h3>Space for the full picture.</h3><p>Give your work and business the detail they deserve.</p></article><article><h3>One home. Different roles.</h3><p>Help visitors see how your expertise, projects and businesses connect.</p></article><article><h3>An address worth sharing.</h3><p>A memorable xin.ng link connects your online and offline worlds.</p></article><article><h3>Ready to grow with you.</h3><p>Update your information and expand your page as your work changes.</p></article></div></div></section>
<section class="section learn-section" id="learn"><div class="wrap"><div class="section-heading"><div><p class="eyebrow">A LITTLE GUIDANCE</p><h2>Make more of your digital home.</h2></div><p>Start with the essentials.<br>Build something that feels like you.</p></div><div class="learn-grid"><article><span>01 / YOUR PAGE</span><h3>Start with a clear introduction.</h3><p>Tell visitors who you are, what you do and who you help. Give them one clear next step.</p><a class="text-link" href="#pages">Explore page sections ↗</a></article><article><span>02 / YOUR ADDRESS</span><h3>Make yourself easy to find.</h3><p>Choose a short, recognisable name. Use the same address in your bio, email signature and messages.</p><a class="text-link" href="#main">Choose your address ↗</a></article><article><span>03 / YOUR QR CODE</span><h3>Connect the offline moments.</h3><p>Add a QR code to printed materials. Check its destination and test a scan before you print.</p><a class="text-link" href="#qr">Create your QR code ↗</a></article></div></div></section>
<section class="section faq-section" id="faq"><div class="wrap faq-layout"><div><p class="eyebrow">GOOD QUESTIONS</p><h2>A few things you<br>might be wondering.</h2><p>Getting to know your new digital home.</p><a class="text-link" href="pricing.php">Explore pricing <?= home_icon('arrow') ?></a><div class="faq-dragon" data-faq-dragon><img src="assets/images/drag-xinng/stare.png" alt="Xinng the dragon" width="1254" height="1254" loading="lazy" decoding="async" draggable="false"></div></div><div class="faq-list"><?php foreach ($faqs as $faq): ?><details><summary><?= e($faq[0]) ?><span aria-hidden="true">+</span></summary><p><?= e($faq[1]) ?></p></details><?php endforeach; ?></div></div></section>
<section class="final-cta"><div class="wrap"><p class="eyebrow light">THERE’S NO PLACE LIKE YOURS</p><h2>Your different sides deserve<br>one place to call home.</h2><p>Bring your story, work and business together.<br>Give people one address to discover what you do.</p><div class="final-address">xin.ng/<span>yourname</span></div><div class="cta-actions"><a class="button white-button" href="<?= $pageAction ?>" data-track="pages">Claim your Xinng Page <?= home_icon('arrow') ?></a><a class="text-link light-link" href="#qr">Create a QR code ↗</a></div></div></section>
</main>
<footer class="site-footer"><div class="wrap footer-grid"><div><a class="xinng-brand" href="index.php" aria-label="Xinng home"><img class="xinng-brand__image" src="assets/images/logo.svg" alt="Xinng" width="1736" height="906" loading="lazy"></a><h3>Xinng is home.</h3><p>For the different sides of<br>you and your business.</p></div><div><h3>Products</h3><a href="#pages">Xinng Pages</a><a href="#qr">Xinng QR</a><a href="pricing.php">Pricing</a></div><div><h3>Tools</h3><a href="tools/teleprompter/" data-track="teleprompter">Teleprompter</a><a href="tools/creator-calculator/x/" data-track="creator-calculator">Creator Calculator</a></div><div><h3>Resources</h3><a href="#learn">Learn</a><a href="#faq">Questions & answers</a><a href="<?= $pageAction ?>">Get started</a></div></div><div class="wrap footer-bottom"><span>© <?= date('Y') ?> Xinng. A home for your different sides.</span><a href="#main">Back to top ↑</a></div></footer>
</body></html>
