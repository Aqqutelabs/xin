<?php
require_once __DIR__ . '/config.php';
session_start();

$userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
if (!$userId || $userId <= 0) {
    header('Location: signin.php');
    exit;
}

function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$userName = (string)($_SESSION['user_name'] ?? 'User');
$pdo = get_db_connection();
$dbError = $pdo === null;
$channelCounts = ['links' => 0, 'pages' => 0, 'qr' => 0];
$engagement = ['views' => 0, 'clicks' => 0, 'scans' => 0];
$creditBalance = 0;
$creditsUsedTotal = 0;
$subscriptionName = null;
$subscriptionStatus = null;
$recentActivity = [];
$setupPage = null;
$socialCount = 0;

if ($pdo) {
    xinng_ensure_short_link_tables($pdo);
    xinng_ensure_qr_code_tables($pdo);
    xinng_ensure_credit_tables($pdo);

    $countQueries = [
        'links' => 'SELECT COUNT(*) FROM short_links WHERE user_id = ? AND deleted_at IS NULL',
        'pages' => 'SELECT COUNT(*) FROM pages WHERE user_id = ? AND deleted_at IS NULL',
        'qr' => 'SELECT COUNT(*) FROM qr_codes WHERE user_id = ? AND deleted_at IS NULL',
    ];
    foreach ($countQueries as $key => $sql) {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);
        $channelCounts[$key] = (int)$stmt->fetchColumn();
    }

    $stmt = $pdo->prepare('SELECT id, title, bio, theme FROM pages WHERE user_id = ? AND deleted_at IS NULL ORDER BY updated_at DESC, id DESC LIMIT 1');
    $stmt->execute([$userId]);
    $setupPage = $stmt->fetch() ?: null;
    if ($setupPage) {
        try {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM page_socials WHERE page_id = ?');
            $stmt->execute([(int)$setupPage['id']]);
            $socialCount = (int)$stmt->fetchColumn();
        } catch (PDOException $exception) {
            try {
                $stmt = $pdo->prepare('SELECT COUNT(*) FROM social_links WHERE page_id = ?');
                $stmt->execute([(int)$setupPage['id']]);
                $socialCount = (int)$stmt->fetchColumn();
            } catch (PDOException $ignored) {
                $socialCount = 0;
            }
        }
    }

    $metricQueries = [
        'views' => 'SELECT COUNT(*) FROM page_views pv JOIN pages p ON p.id = pv.page_id WHERE p.user_id = ? AND p.deleted_at IS NULL',
        'clicks' => 'SELECT (SELECT COUNT(*) FROM link_clicks lc JOIN pages p ON p.id = lc.page_id WHERE p.user_id = ? AND p.deleted_at IS NULL) + (SELECT COALESCE(SUM(click_count), 0) FROM short_links WHERE user_id = ? AND deleted_at IS NULL)',
        'scans' => 'SELECT COUNT(*) FROM qr_code_scans qs JOIN qr_codes q ON q.id = qs.qr_code_id WHERE q.user_id = ? AND q.deleted_at IS NULL',
    ];
    foreach ($metricQueries as $key => $sql) {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($key === 'clicks' ? [$userId, $userId] : [$userId]);
        $engagement[$key] = (int)$stmt->fetchColumn();
    }

    $stmt = $pdo->prepare('SELECT credit_balance, credits_used_total FROM users WHERE id = ? AND deleted_at IS NULL LIMIT 1');
    $stmt->execute([$userId]);
    $creditRow = $stmt->fetch();
    $creditBalance = max(0, (int)($creditRow['credit_balance'] ?? 0));
    $creditsUsedTotal = max(0, (int)($creditRow['credits_used_total'] ?? 0));

    // Subscriptions are part of the planned finance schema and may not exist in a live installation yet.
    try {
        $stmt = $pdo->prepare("SELECT p.name, s.status FROM subscriptions s JOIN plans p ON p.id = s.plan_id WHERE s.user_id = ? AND s.status IN ('active', 'trialing') AND (s.ends_at IS NULL OR s.ends_at > NOW()) ORDER BY s.starts_at DESC, s.id DESC LIMIT 1");
        $stmt->execute([$userId]);
        $subscription = $stmt->fetch();
        if ($subscription) {
            $subscriptionName = (string)$subscription['name'];
            $subscriptionStatus = (string)$subscription['status'];
        }
    } catch (PDOException $exception) {
        // Until the subscription train ships, the wallet still has a valid standalone balance.
    }
    $recentActivity = xinng_workspace_recent_activity($pdo, (int)$userId);
}

$pageBuilderHref = $setupPage ? 'page_builder.php?id=' . (int)$setupPage['id'] : 'pages.php';
$setupTasks = [
    ['label' => 'Create your first page', 'detail' => 'Give visitors a home for your links and content.', 'href' => 'pages.php', 'complete' => $channelCounts['pages'] > 0],
    ['label' => 'Write your page bio', 'detail' => 'Tell visitors who you are and what you offer.', 'href' => $pageBuilderHref, 'complete' => trim((string)($setupPage['bio'] ?? '')) !== ''],
    ['label' => 'Choose a page style', 'detail' => 'Personalise the look of your public page.', 'href' => $pageBuilderHref, 'complete' => $setupPage && !in_array((string)($setupPage['theme'] ?? ''), ['', 'default'], true)],
    ['label' => 'Add a social link', 'detail' => 'Connect at least one social profile.', 'href' => $pageBuilderHref, 'complete' => $socialCount > 0],
    ['label' => 'Create a short link', 'detail' => 'Make a memorable, trackable destination.', 'href' => 'short_links.php?create=1', 'complete' => $channelCounts['links'] > 0],
    ['label' => 'Create a QR code', 'detail' => 'Make one of your destinations scannable.', 'href' => 'qr_codes.php', 'complete' => $channelCounts['qr'] > 0],
];
$setupComplete = count(array_filter($setupTasks, static fn(array $task): bool => $task['complete']));
$setupTotal = count($setupTasks);
$setupProgress = $setupTotal ? (int)round(($setupComplete / $setupTotal) * 100) : 0;
$incompleteSetupTasks = array_values(array_filter($setupTasks, static fn(array $task): bool => !$task['complete']));
$nextSetupTask = $incompleteSetupTasks[0] ?? null;

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Den — <?= e($APP_NAME ?? 'Xinng') ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="assets/css/dashboard.css">
  <link rel="stylesheet" href="assets/css/workspace-sidebar.css?v=20261019e">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/dashboard-home.css?v=<?= filemtime(__DIR__ . '/assets/css/dashboard-home.css') ?>">
  <link rel="stylesheet" href="assets/css/dashboard-overview.css?v=<?= filemtime(__DIR__ . '/assets/css/dashboard-overview.css') ?>">
  <?php require __DIR__ . '/includes/google-analytics.php'; ?>
</head>
<body class="dashboard-home overview-screen workspace-screen workspace-shell-page">
  <a class="dashboard-skip" href="#workspace">Skip to Den</a>
  <div class="dashboard no-preview">
    <?php
      $sidebarActive = 'overview';
      $sidebarUserName = $userName;
      $sidebarBottom = ['progress' => $setupProgress, 'title' => 'Your setup checklist', 'body' => $setupComplete . ' of ' . $setupTotal . ' complete', 'action_label' => $nextSetupTask ? 'Finish setup' : 'Setup complete', 'action_href' => $nextSetupTask['href'] ?? '#setup-checklist'];
      include __DIR__ . '/includes/workspace-sidebar.php';
    ?>
    <main class="main" id="workspace" tabindex="-1">
      <?php $workspaceHeaderTitle = 'Den'; $workspaceHeaderSubtitle = 'Your Xinng activity at a glance.'; include __DIR__ . '/includes/workspace-shell-header.php'; ?>
      <div class="content"><div class="editor-column">
        <?php include __DIR__ . '/includes/api-token-notice.php'; ?>
        <?php if ($dbError): ?><div class="notice">Workspace data is temporarily unavailable.</div><?php endif; ?>
        <section class="workspace-welcome" aria-labelledby="welcome-heading"><div><p class="welcome-eyebrow">YOUR XINNG WORKSPACE</p><h2 id="welcome-heading">Welcome back, <?= e($userName) ?>.</h2><p>Manage your links, pages and QR codes from one place.</p></div><a href="insights.php">View insights <span aria-hidden="true">&nearr;</span></a></section>

        <section class="dashboard-overview" aria-labelledby="channels-heading"><div class="dashboard-section-heading"><div><span class="eyebrow">YOUR CHANNELS</span><h2 id="channels-heading">Ready to share</h2></div></div><div class="channel-grid">
          <a class="channel-card" href="short_links.php"><span class="channel-icon"><i class="fa-solid fa-link" aria-hidden="true"></i></span><span>Short links</span><strong><?= number_format($channelCounts['links']) ?></strong><small>Created destinations</small></a>
          <a class="channel-card" href="pages.php"><span class="channel-icon"><i class="fa-regular fa-file-lines" aria-hidden="true"></i></span><span>Pages</span><strong><?= number_format($channelCounts['pages']) ?></strong><small>Personal and event pages</small></a>
          <a class="channel-card" href="qr_codes.php"><span class="channel-icon"><i class="fa-solid fa-qrcode" aria-hidden="true"></i></span><span>QR codes</span><strong><?= number_format($channelCounts['qr']) ?></strong><small>Codes you can manage</small></a>
          <a class="channel-card channel-card-wallet" href="credits.php"><span class="channel-icon"><i class="fa-solid fa-wallet" aria-hidden="true"></i></span><span>Credits</span><strong><?= number_format($creditBalance) ?></strong><small><?= e($subscriptionName ?? 'No active subscription') ?></small></a>
        </div></section>

        <section class="dashboard-actions" aria-labelledby="quick-actions-heading"><div class="dashboard-section-heading"><div><span class="eyebrow">GET THINGS DONE</span><h2 id="quick-actions-heading">Quick actions</h2></div></div><div class="quick-action-grid">
          <a class="quick-action primary" href="short_links.php?create=1"><span class="quick-action-icon"><i class="fa-solid fa-link" aria-hidden="true"></i></span><strong>Create short link</strong><small>Give a destination a memorable address.</small></a>
          <a class="quick-action" href="pages.php"><span class="quick-action-icon"><i class="fa-regular fa-file-lines" aria-hidden="true"></i></span><strong>Create or edit a page</strong><small>Update what visitors see on your page.</small></a>
          <a class="quick-action" href="qr_codes.php"><span class="quick-action-icon"><i class="fa-solid fa-qrcode" aria-hidden="true"></i></span><strong>Create QR code</strong><small>Make a scannable link.</small></a>
          <a class="quick-action" href="insights.php"><span class="quick-action-icon"><i class="fa-solid fa-chart-line" aria-hidden="true"></i></span><strong>Check insights</strong><small>Review views, clicks and scans.</small></a>
          <a class="quick-action" href="credits.php"><span class="quick-action-icon"><i class="fa-solid fa-wallet" aria-hidden="true"></i></span><strong>Open wallet</strong><small>Review credits and purchases.</small></a>
          <a class="quick-action" href="wallet/plans"><span class="quick-action-icon"><i class="fa-solid fa-layer-group" aria-hidden="true"></i></span><strong>Choose a plan</strong><small>Compare subscriptions for your workspace.</small></a>
          <a class="quick-action" href="workspaces.php"><span class="quick-action-icon"><i class="fa-solid fa-users" aria-hidden="true"></i></span><strong>Manage workspace</strong><small>Switch organizations and manage members.</small></a>
        </div></section>

        <section class="dashboard-performance" aria-labelledby="performance-heading"><div class="dashboard-section-heading"><div><span class="eyebrow">WHAT PEOPLE DID</span><h2 id="performance-heading">Performance to date</h2></div><a href="insights.php">More insights <span aria-hidden="true">&nearr;</span></a></div><div class="performance-grid"><div><strong><?= number_format($engagement['views']) ?></strong><span>Page views</span></div><div><strong><?= number_format($engagement['clicks']) ?></strong><span>Link clicks</span></div><div><strong><?= number_format($engagement['scans']) ?></strong><span>QR scans</span></div></div></section>

        <section class="setup-checklist" id="setup-checklist" aria-labelledby="setup-checklist-heading">
          <div class="dashboard-section-heading"><div><span class="eyebrow">YOUR TODO LIST</span><h2 id="setup-checklist-heading">Finish setting up Xinng</h2></div><span class="setup-count"><?= $setupComplete ?> of <?= $setupTotal ?> complete</span></div>
          <div class="setup-progress" role="progressbar" aria-label="Setup progress" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= $setupProgress ?>"><span style="width:<?= $setupProgress ?>%"></span></div>
          <ul class="setup-task-list">
            <?php foreach ($setupTasks as $task): ?>
              <li class="<?= $task['complete'] ? 'is-complete' : '' ?>">
                <span class="setup-task-status" aria-hidden="true"><i class="fa-solid <?= $task['complete'] ? 'fa-check' : 'fa-arrow-right' ?>"></i></span>
                <span class="setup-task-copy"><strong><?= e($task['label']) ?></strong><small><?= e($task['detail']) ?></small></span>
                <?php if ($task['complete']): ?><span class="setup-task-done">Done</span><?php else: ?><a href="<?= e($task['href']) ?>">Continue <span aria-hidden="true">&nearr;</span></a><?php endif; ?>
              </li>
            <?php endforeach; ?>
          </ul>
        </section>

        <section class="recent-activity workspace-recent-activity" aria-labelledby="recent-activity-heading"><div class="dashboard-section-heading"><div><span class="eyebrow">RECENT ACTIVITY</span><h2 id="recent-activity-heading">Latest updates</h2></div></div><?php if ($recentActivity): ?><ul><?php foreach ($recentActivity as $activity): ?><li><span><small><?= e($activity['label']) ?></small><a href="<?= e($activity['href']) ?>"><?= e($activity['title']) ?></a></span><time datetime="<?= e($activity['at']) ?>"><?= e(date('M j', strtotime($activity['at']))) ?></time></li><?php endforeach; ?></ul><?php else: ?><p>Recent workspace updates will appear here.</p><?php endif; ?></section>
      </div></div>
    </main>
  </div>
</body>
</html>
