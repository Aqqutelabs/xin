<?php
declare(strict_types=1);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#14120f">
  <title>Xinng Product — Build Tracker</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="tracker.css?v=<?= (int) filemtime(__DIR__ . '/tracker.css') ?>">
  <link rel="stylesheet" href="tracker-trains.css?v=<?= (int) filemtime(__DIR__ . '/tracker-trains.css') ?>">
  <link rel="stylesheet" href="tracker-subtasks.css?v=<?= (int) filemtime(__DIR__ . '/tracker-subtasks.css') ?>">
</head>
<body>
  <aside class="sidebar">
    <a class="brand" href="../../dashboard.php" aria-label="Back to Xing">
      <span class="brand-mark">x</span><span>xing</span>
    </a>
    <nav aria-label="Tracker sections">
      <a class="nav-link active" href="#overview"><span>⌁</span> Overview</a>
      <a class="nav-link" href="#roadmap"><span>▦</span> Roadmap</a>
      <a class="nav-link" href="#workstreams"><span>◎</span> Workstreams</a>
      <a class="nav-link" href="#architecture"><span>◇</span> Architecture</a>
    </nav>
    <div class="sidebar-note">
      <span class="eyebrow">North star</span>
      <strong>Useful tools on the surface. Shared intelligence underneath.</strong>
      <small>14-train product portfolio</small>
    </div>
  </aside>

  <main>
    <header class="topbar">
      <div>
        <p class="breadcrumb">Xinng / Product / Delivery</p>
        <h1>Build tracker</h1>
      </div>
      <div class="header-actions">
        <button class="button ghost" id="exportButton" type="button">Export progress</button>
        <button class="button primary" id="focusButton" type="button">Show next actions</button>
      </div>
    </header>

    <section class="hero" id="overview">
      <div class="hero-copy">
        <span class="status-pill"><i></i> Product build · active</span>
        <h2>One workspace for<br><em>every connection.</em></h2>
        <p>A complete delivery map for Xinng across platform, presence, audience, communications, automation, distribution, intelligence, Events, creator tools and commerce.</p>
        <div class="hero-meta">
          <div><span>Scope</span><strong>14 delivery trains</strong></div>
          <div><span>Source</span><strong>XINNGPRDfull.md · v2.1</strong></div>
          <div><span>Updated</span><strong id="lastUpdated">17 Oct 2026</strong></div>
        </div>
      </div>
      <div class="readiness-card">
        <div class="ring" id="progressRing"><span><strong id="overallPercent">0%</strong><small>complete</small></span></div>
        <div class="readiness-copy"><span>Delivery readiness</span><strong id="readinessLabel">Foundation</strong><small id="doneSummary">0 of 153 tasks done</small></div>
      </div>
    </section>

    <section class="metric-grid" aria-label="Project metrics">
      <article><span>Total tasks</span><strong id="totalMetric">153</strong><small>Existing work plus Nano and Platform Finance</small></article>
      <article><span>In progress</span><strong id="activeMetric">0</strong><small>Work currently moving</small></article>
      <article><span>Blocked</span><strong id="blockedMetric">0</strong><small>Needs an unblock decision</small></article>
      <article><span>Critical path</span><strong id="criticalMetric">0</strong><small>High-priority open items</small></article>
    </section>

    <section class="control-panel" id="roadmap">
      <div class="section-heading">
        <div><span class="eyebrow">Execution plan</span><h2>Portfolio roadmap</h2></div>
        <div class="view-switch" role="group" aria-label="View">
          <button class="active" data-view="list" type="button">List</button>
          <button data-view="board" type="button">Board</button>
        </div>
      </div>
      <div class="train-tabs" id="trainTabs" role="tablist" aria-label="Delivery trains"></div>
      <div class="filters">
        <label class="search"><span>⌕</span><input id="searchInput" type="search" placeholder="Search task, module or owner…"></label>
        <select id="phaseFilter" aria-label="Filter by phase"><option value="all">All sprints</option></select>
        <select id="statusFilter" aria-label="Filter by status"><option value="all">All statuses</option><option>To do</option><option>In progress</option><option>Blocked</option><option>Review</option><option>Done</option></select>
        <select id="ownerFilter" aria-label="Filter by owner"><option value="all">All owners</option></select>
        <button class="filter-reset" id="resetFilters" type="button">Reset</button>
      </div>
      <div class="result-row"><span id="resultCount">153 tasks · 765 subtasks</span><span class="save-state" id="saveState">Saved locally</span></div>
      <div id="taskView" class="task-view"></div>
    </section>

    <section class="workstreams" id="workstreams">
      <div class="section-heading"><div><span class="eyebrow">Product topology</span><h2>How Xinng fits together</h2></div></div>
      <div class="stream-grid" id="streamGrid"></div>
    </section>

    <section class="architecture" id="architecture">
      <div class="architecture-copy"><span class="eyebrow">Shared spine</span><h2>Many product trains.<br>One Xinng platform.</h2><p>Each train owns a clear user outcome while reusing identity, organizations, contacts, permissions, media, links, notifications, commerce, analytics and audit history.</p></div>
      <div class="system-map">
        <div class="core-node">Shared<br><strong>Xinng</strong></div>
        <span class="node n1">Creator rules</span><span class="node n2">Calculators</span><span class="node n3">Events</span><span class="node n4">Share links</span><span class="node n5">Payments</span><span class="node n6">Media</span><span class="node n7">Analytics</span><span class="node n8">Identity</span>
      </div>
    </section>

    <footer>Built from <strong>XINNGPRDfull.md</strong> · Progress is stored in this browser · <button id="resetProgress" type="button">Reset progress</button></footer>
  </main>

  <dialog id="taskDialog">
    <form method="dialog" class="dialog-card">
      <button class="dialog-close" value="cancel" aria-label="Close">×</button>
      <span class="task-code" id="dialogCode"></span>
      <h2 id="dialogTitle"></h2>
      <p id="dialogDescription"></p>
      <dl id="dialogMeta"></dl>
      <section class="subtask-section" aria-labelledby="subtaskHeading">
        <div class="subtask-heading">
          <div><span class="eyebrow">Implementation plan</span><h3 id="subtaskHeading">Subtasks</h3></div>
          <strong id="subtaskProgress">0 / 5 complete</strong>
        </div>
        <div class="subtask-track"><i id="subtaskProgressBar"></i></div>
        <div id="dialogSubtasks" class="subtask-list"></div>
      </section>
      <label>Status<select id="dialogStatus"><option>To do</option><option>In progress</option><option>Blocked</option><option>Review</option><option>Done</option></select></label>
      <label>Notes<textarea id="dialogNotes" rows="4" placeholder="Add implementation notes, blockers or evidence…"></textarea></label>
      <button class="button primary" id="saveTask" value="cancel" type="button">Save task</button>
    </form>
  </dialog>

  <script src="tracker.js?v=<?= (int) filemtime(__DIR__ . '/tracker.js') ?>"></script>
</body>
</html>
