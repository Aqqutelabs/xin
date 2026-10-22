<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
session_start();
if (empty($_SESSION['user_id'])) { header('Location: signin.php'); exit; }
if (!function_exists('e')) { function e($value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); } }
$pdo = get_db_connection();
$events = [];
if ($pdo) {
    xinng_ensure_page_builder_tables($pdo);
    $stmt = $pdo->prepare('SELECT p.*, pe.start_at, pe.end_at, pe.venue, pe.city, (SELECT COUNT(*) FROM event_ticket_types t WHERE t.page_id=p.id AND t.deleted_at IS NULL) ticket_types, (SELECT COALESCE(SUM(o.quantity),0) FROM event_orders o WHERE o.page_id=p.id AND o.payment_status IN ("free","paid")) attendees FROM pages p LEFT JOIN page_events pe ON pe.page_id=p.id WHERE p.user_id=? AND p.page_type="event" AND p.deleted_at IS NULL ORDER BY pe.start_at IS NULL, pe.start_at, p.updated_at DESC');
    $stmt->execute([(int)$_SESSION['user_id']]);
    $events = $stmt->fetchAll();
}
$previewEvent = $events[0] ?? null;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Events - Xinng</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="assets/css/dashboard.css?v=20261016-events-workspace">
  <link rel="stylesheet" href="assets/css/workspace-sidebar.css?v=20261019e">
  <link rel="stylesheet" href="assets/css/preview-pane.css?v=20261019f">
  <?php require __DIR__ . '/includes/google-analytics.php'; ?>
</head>
<body class="pages-screen events-screen workspace-shell-page">
<div class="dashboard">
  <?php $sidebarActive='events'; $sidebarUserName=$_SESSION['user_name'] ?? 'User'; $sidebarBottom=['eyebrow'=>'EVENTS','title'=>'Bring the room together.','body'=>'Create an event page, manage attendance and keep check-in moving.','action_label'=>'Create event','action_href'=>'pages.php?create=event']; include __DIR__ . '/includes/workspace-sidebar.php'; ?>
  <main class="main">
    <?php $workspaceHeaderTitle='Events'; $workspaceHeaderSubtitle='Create, sell tickets, register attendees, and run check-in.'; include __DIR__ . '/includes/workspace-shell-header.php'; ?>
    <div class="content"><div class="events-wrap">
      <section class="events-hero"><span>EVENTS WORKSPACE</span><h2>Everything behind your event, in one place.</h2><p>Every event is a Xinng Page with its own schedule, location, ticket inventory, registrations and check-in.</p></section>
      <div class="events-list">
        <?php if (!$events): ?><section class="events-empty"><i class="fa-solid fa-calendar-plus"></i><h2>No events yet</h2><p>Create an Event Page to get started.</p><a href="pages.php?create=event">Create your first event</a></section><?php endif; ?>
        <?php foreach ($events as $event): $where=trim(($event['venue'] ?? '') . (($event['venue'] ?? '') && ($event['city'] ?? '') ? ', ' : '') . ($event['city'] ?? '')); ?>
          <article class="event-row" tabindex="0" data-preview-open data-title="<?= e($event['title']) ?>" data-date="<?= e(!empty($event['start_at']) ? date('M j, Y · g:i A', strtotime($event['start_at'])) : 'Date and time required') ?>" data-location="<?= e($where ?: 'Location required') ?>">
            <div class="event-date"><strong><?= !empty($event['start_at']) ? e(date('d', strtotime($event['start_at']))) : '--' ?></strong><span><?= !empty($event['start_at']) ? e(strtoupper(date('M', strtotime($event['start_at'])))) : 'DATE' ?></span></div>
            <div class="event-row-copy"><span class="event-status"><?= e(ucfirst($event['status'] ?? 'draft')) ?></span><h3><?= e($event['title']) ?></h3><p><i class="fa-regular fa-clock"></i> <?= e(!empty($event['start_at']) ? date('M j, Y · g:i A', strtotime($event['start_at'])) : 'Date and time required') ?> <i class="fa-solid fa-location-dot"></i> <?= e($where ?: 'Location required') ?></p><div><span><?= number_format((int)$event['ticket_types']) ?> ticket types</span><span><?= number_format((int)$event['attendees']) ?> attendees</span></div></div>
            <div class="event-row-actions"><a href="page_builder.php?id=<?= (int)$event['id'] ?>">Edit event</a><a href="event_orders.php?page_id=<?= (int)$event['id'] ?>">Attendees</a><a href="<?= e(xinng_short_url($event['slug'])) ?>" target="_blank">View</a></div>
          </article>
        <?php endforeach; ?>
      </div>
    </div></div>
  </main>
  <aside class="preview event-preview-pane" data-preview-pane data-preview-label="event preview" aria-label="Event preview"><header class="preview-header"><strong>Event preview</strong></header><div class="preview-body"><div class="phone"><div class="phone-screen"><div class="phone-avatar">EV</div><h2 id="event-preview-title"><?= e($previewEvent['title'] ?? 'Select an event') ?></h2><p id="event-preview-date"><?= e(!empty($previewEvent['start_at']) ? date('M j, Y · g:i A', strtotime($previewEvent['start_at'])) : 'Choose an event card to preview it.') ?></p><div class="phone-link hero"><strong>Get tickets</strong></div><div class="phone-link" id="event-preview-location"><?= e(trim(($previewEvent['venue'] ?? '') . ' ' . ($previewEvent['city'] ?? '')) ?: 'Venue and details') ?></div><div class="phone-brand">xin.ng event preview</div></div></div></div></aside>
</div>
<script>document.querySelectorAll('.event-row').forEach(card=>card.addEventListener('click',event=>{if(event.target.closest('a'))return;document.getElementById('event-preview-title').textContent=card.dataset.title||'Event';document.getElementById('event-preview-date').textContent=card.dataset.date||'';document.getElementById('event-preview-location').textContent=card.dataset.location||'';}));</script>
<script src="assets/js/preview-pane.js?v=20261019c"></script>
</body></html>
