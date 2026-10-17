<?php
require_once __DIR__ . '/config.php';
session_start();
if (empty($_SESSION['user_id'])) { header('Location: signin.php'); exit; }
$userId = (int)$_SESSION['user_id'];
$userName = $_SESSION['user_name'] ?? 'User';
function e($value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function ai_initials(string $name): string { return strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name) ?: 'X', 0, 2)); }
$pdo = get_db_connection();
$pages = [];
$creditBalance = 0;
if ($pdo) {
    xinng_ensure_page_builder_tables($pdo);
    $stmt = $pdo->prepare('SELECT id, slug, title, description, bio, page_type, theme, layout FROM pages WHERE user_id = ? AND deleted_at IS NULL ORDER BY updated_at DESC, id DESC');
    $stmt->execute([$userId]);
    $pages = $stmt->fetchAll();
    $creditBalance = xinng_ensure_credit_balance($pdo, $userId);
}
$selectedPage = $pages[0] ?? null;
$csrf = csrf_token();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>AI Page Builder - <?= e($APP_NAME ?? 'xin.ng') ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="assets/css/dashboard.css">
  <link rel="stylesheet" href="assets/css/ai-page.css">
</head>
<body class="ai-workspace-page">
  <div class="dashboard">
    <aside class="sidebar" aria-label="Dashboard navigation">
      <div class="brand-slot"><a class="xinng-brand" href="<?= e(xinng_public_base_url()) ?>/index.php" aria-label="Xinng home"><img class="xinng-brand__image" src="<?= e(xinng_brand_logo_url(xinng_public_base_url())) ?>" alt="Xinng" width="1736" height="906"></a></div>
      <div class="account"><div class="account-main"><span class="avatar"><?= e(ai_initials($userName)) ?></span><span><?= e($selectedPage['slug'] ?? $userName) ?></span></div></div>
      <nav>
        <div class="nav-section">
          <div class="nav-heading"><span>Workspace</span><span><i class="fa-solid fa-chevron-up"></i></span></div>
          <a class="nav-item" href="dashboard.php"><span class="nav-icon"><i class="fa-solid fa-link"></i></span>URL Links</a>
          <a class="nav-item" href="pages.php"><span class="nav-icon"><i class="fa-regular fa-file-lines"></i></span>Pages</a>
          <a class="nav-item active" href="ai_page.php"><span class="nav-icon"><i class="fa-solid fa-wand-magic-sparkles"></i></span>AI Page Builder</a>
          <a class="nav-item" href="qr_codes.php"><span class="nav-icon"><i class="fa-solid fa-qrcode"></i></span>QR Codes</a>
          <a class="nav-item" href="insights.php"><span class="nav-icon"><i class="fa-solid fa-chart-line"></i></span>Insights</a>
        </div>
      </nav>
      <div class="setup-card"><div class="progress-ring" style="--completion: <?= $pages ? '60' : '25' ?>%;"><?= $pages ? '60' : '25' ?>%</div><strong>Build faster with AI</strong><p>Start with a clear brief and refine the draft in the editor.</p><a class="primary-btn" href="credits.php"><span class="label-icon"><i class="fa-solid fa-coins"></i></span>Get credits</a></div>
    </aside>

    <main class="main ai-main">
      <header class="main-header">
        <div class="header-title"><h1>AI Page Builder</h1><span>Turn a short brief into a page draft you can review and edit.</span></div>
        <div class="header-actions"><a class="icon-btn" href="pages.php" aria-label="Back to pages"><i class="fa-solid fa-arrow-left"></i></a><a class="icon-btn" href="logout.php" aria-label="Log out"><i class="fa-solid fa-arrow-right-from-bracket"></i></a></div>
      </header>

      <div class="ai-content">
          <section class="ai-hero"><div class="ai-kicker"><i class="fa-solid fa-sparkles"></i> Prompt workspace</div><h2>What should your page feel like?</h2><p>Describe your audience, offer, tone, and the actions you want visitors to take. The more specific the brief, the more useful the first draft.</p></section>
          <div class="ai-grid">
            <section class="prompt-card">
              <div class="profile-type-choice"><span class="card-eyebrow">First, choose a profile</span><div class="profile-type-options"><button type="button" class="profile-type-option" data-profile-type="creator" aria-pressed="false"><i class="fa-solid fa-user"></i><span><strong>Personal profile</strong><small>Creator, freelancer, coach, or individual</small></span></button><button type="button" class="profile-type-option" data-profile-type="corporate" aria-pressed="false"><i class="fa-solid fa-building"></i><span><strong>Company profile</strong><small>Business, team, event, or organisation</small></span></button></div></div>
              <div class="prompt-card-top"><div><span class="card-eyebrow">Create a draft for</span><label class="sr-only" for="ai-page-select">Page</label><select id="ai-page-select"><?php if (!$pages): ?><option value="" data-page-type="">New page from this prompt</option><?php endif; ?><?php foreach ($pages as $page): ?><option value="<?= (int)$page['id'] ?>" data-page-type="<?= e($page['page_type'] ?? 'creator') ?>" data-title="<?= e($page['title'] ?? '') ?>" data-description="<?= e($page['description'] ?? ($page['bio'] ?? '')) ?>" data-theme="<?= e($page['theme'] ?? 'default') ?>" data-layout="<?= e($page['layout'] ?? 'simple') ?>"><?= e($page['title'] ?: $page['slug']) ?></option><?php endforeach; ?></select></div><span class="credit-pill"><i class="fa-solid fa-bolt"></i> <?= number_format($creditBalance) ?> credits</span></div>
              <div class="prompt-box"><textarea id="ai-page-prompt" maxlength="<?= (int)AI_MAX_PROMPT_LENGTH ?>" placeholder="Build a page for..." autofocus aria-describedby="prompt-count"></textarea><div class="prompt-box-footer"><span id="prompt-count">0 / <?= (int)AI_MAX_PROMPT_LENGTH ?></span><button class="generate-btn" id="generate-ai-page" type="button"><i class="fa-solid fa-arrow-up"></i> Generate draft</button></div></div>
              <div class="prompt-controls"><label for="ai-page-provider">Model<select id="ai-page-provider"><option value="automatic">Automatic</option><option value="gemini">Gemini</option><option value="deepseek">DeepSeek</option></select></label><span><i class="fa-regular fa-clock"></i> Usually takes a few seconds</span></div>
              <div class="ai-status" id="ai-page-status" role="status" aria-live="polite"></div>
              <div class="prompt-examples"><span>Try a starting point</span><button type="button" data-prompt="Create a clean personal page for a Lagos wedding photographer. Include a warm introduction, portfolio, booking, Instagram, and contact links.">Wedding photographer</button><button type="button" data-prompt="Create a credible company page for a B2B technology consultancy. Include capabilities, case studies, a meeting booking CTA, and contact routing.">B2B consultancy</button><button type="button" data-prompt="Create an energetic creator page for a fitness coach selling a 6-week program. Include a strong CTA, testimonials, booking, and newsletter signup.">Fitness creator</button></div>
            </section>
            <aside class="draft-card" id="draft-card"><div class="draft-card-head"><div><span class="card-eyebrow">Output</span><h3 id="draft-title">Your draft will appear here</h3></div><span class="draft-status" id="draft-status" role="status">Waiting</span></div><div class="draft-preview" id="draft-preview"><i class="fa-solid fa-wand-magic-sparkles"></i><p>AI will suggest a title, description, style, and page sections based on your prompt.</p></div><button class="primary-btn open-builder-btn" id="open-builder" type="button" disabled><i class="fa-solid fa-pen"></i> Open in page builder</button></aside>
          </div>
      </div>
    </main>
  </div>
  <script>window.aiPageConfig = <?= json_encode(['csrf' => $csrf, 'maxPromptLength' => AI_MAX_PROMPT_LENGTH, 'generationCost' => AI_PAGE_GENERATION_COST], JSON_UNESCAPED_SLASHES) ?>;</script>
  <script src="assets/js/ai-page.js"></script>
</body>
</html>
