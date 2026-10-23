<?php
declare(strict_types=1);

$sidebarActive = (string)($sidebarActive ?? '');
$sidebarUserName = trim((string)($sidebarUserName ?? ($_SESSION['user_name'] ?? 'User'))) ?: 'User';
$sidebarDisplayName = $sidebarUserName;
$sidebarBottom = is_array($sidebarBottom ?? null) ? $sidebarBottom : [];
$sidebarEscape = static fn ($value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$sidebarWords = preg_split('/\s+/u', $sidebarUserName, -1, PREG_SPLIT_NO_EMPTY) ?: [];
$sidebarInitials = '';
foreach (array_slice($sidebarWords, 0, 2) as $sidebarWord) {
    $sidebarInitials .= function_exists('mb_substr') ? mb_substr($sidebarWord, 0, 1) : substr($sidebarWord, 0, 1);
}
$sidebarInitials = strtoupper($sidebarInitials ?: substr($sidebarUserName, 0, 2));
$sidebarUserId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT) ?: null;
$sidebarPdo = isset($pdo) && $pdo instanceof PDO ? $pdo : null;
$sidebarRole = xinng_workspace_navigation_role($sidebarPdo, $sidebarUserId, $sidebarUserName);
$sidebarItems = xinng_workspace_navigation_items($sidebarRole);
$sidebarSections = ['Dashboard' => ['overview'], 'Channels' => ['links', 'pages', 'qr'], 'Tools' => ['insights'], 'Finance' => ['credits']];
$sidebarPageChildren = ['events', 'ai'];
$sidebarWalletChildren = ['plans'];
?>
<style>
.workspace-sidebar .nav-subitems{margin:0 0 7px 20px;padding-left:10px;border-left:1px solid #e2d4cf}
.workspace-sidebar .nav-subitem{min-height:35px!important;font-size:12px!important}
.workspace-sidebar .nav-subitem .nav-icon{width:24px!important;height:24px!important;font-size:11px!important}
.workspace-sidebar .nav-item.has-active-child{color:#b75356!important}
body.workspace-sidebar-collapsed .workspace-sidebar .nav-subitems{margin:0;padding:0;border:0}
</style>
<link rel="stylesheet" href="assets/css/workspace-shell.css?v=20261019a">
<aside class="sidebar workspace-sidebar" aria-label="Dashboard navigation">
  <button class="workspace-sidebar-edge-toggle" type="button" aria-label="Collapse navigation" aria-expanded="true" title="Collapse navigation"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></button>
  <div class="brand-slot" aria-label="xin.ng brand">
    <a class="xinng-brand" href="index.php" aria-label="Xinng home"><img class="xinng-brand__image" src="<?= $sidebarEscape(xinng_brand_logo_url()) ?>" alt="Xinng" width="1736" height="906"></a>
  </div>
  <nav>
    <div class="nav-section">
      <div class="nav-heading"><span class="nav-heading-label">Dashboard</span></div>
      <?php foreach ($sidebarSections as $sidebarSectionLabel => $sidebarKeys): ?>
        <?php if ($sidebarSectionLabel !== 'Dashboard'): ?><div class="nav-heading"><span class="nav-heading-label"><?= $sidebarEscape($sidebarSectionLabel) ?></span></div><?php endif; ?>
        <?php foreach ($sidebarKeys as $sidebarKey): $sidebarItem = $sidebarItems[$sidebarKey]; if (!$sidebarItem['visible']) continue; ?>
        <a class="nav-item<?= $sidebarActive === $sidebarKey || ($sidebarKey === 'credits' && in_array($sidebarActive, $sidebarWalletChildren, true)) ? ' active' : '' ?><?= $sidebarKey === 'pages' && in_array($sidebarActive, $sidebarPageChildren, true) ? ' has-active-child' : '' ?>" href="<?= $sidebarEscape($sidebarItem['href']) ?>" title="<?= $sidebarEscape($sidebarItem['label']) ?>"<?= $sidebarActive === $sidebarKey || ($sidebarKey === 'credits' && in_array($sidebarActive, $sidebarWalletChildren, true)) ? ' aria-current="page"' : '' ?>><span class="nav-icon"><i class="<?= $sidebarEscape($sidebarItem['icon']) ?>" aria-hidden="true"></i></span><span class="nav-label"><?= $sidebarEscape($sidebarItem['label']) ?></span></a>
        <?php if ($sidebarKey === 'pages'): ?>
          <div class="nav-subitems" role="group" aria-label="Pages">
            <?php foreach ($sidebarPageChildren as $sidebarChildKey): $sidebarChild = $sidebarItems[$sidebarChildKey]; if (!$sidebarChild['visible']) continue; ?>
              <a class="nav-item nav-subitem<?= $sidebarActive === $sidebarChildKey ? ' active' : '' ?>" href="<?= $sidebarEscape($sidebarChild['href']) ?>" title="<?= $sidebarEscape($sidebarChild['label']) ?>"<?= $sidebarActive === $sidebarChildKey ? ' aria-current="page"' : '' ?>><span class="nav-icon"><i class="<?= $sidebarEscape($sidebarChild['icon']) ?>" aria-hidden="true"></i></span><span class="nav-label"><?= $sidebarEscape($sidebarChild['label']) ?></span></a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
        <?php if ($sidebarKey === 'credits'): ?>
          <div class="nav-subitems" role="group" aria-label="Wallet">
            <?php foreach ($sidebarWalletChildren as $sidebarChildKey): $sidebarChild = $sidebarItems[$sidebarChildKey]; if (!$sidebarChild['visible']) continue; ?>
              <a class="nav-item nav-subitem<?= $sidebarActive === $sidebarChildKey ? ' active' : '' ?>" href="<?= $sidebarEscape($sidebarChild['href']) ?>" title="<?= $sidebarEscape($sidebarChild['label']) ?>"<?= $sidebarActive === $sidebarChildKey ? ' aria-current="page"' : '' ?>><span class="nav-icon"><i class="<?= $sidebarEscape($sidebarChild['icon']) ?>" aria-hidden="true"></i></span><span class="nav-label"><?= $sidebarEscape($sidebarChild['label']) ?></span></a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      <?php endforeach; ?>
      <?php endforeach; ?>
    </div>
  </nav>
  <?php if ($sidebarBottom): ?>
    <div class="setup-card workspace-sidebar-message">
      <?php if (!empty($sidebarBottom['image'])): ?><img src="<?= $sidebarEscape($sidebarBottom['image']) ?>" alt="" width="90" height="90"><?php endif; ?>
      <?php if (isset($sidebarBottom['progress'])): ?><div class="progress-ring" style="--completion: <?= (int)$sidebarBottom['progress'] ?>%;"><?= (int)$sidebarBottom['progress'] ?>%</div><?php endif; ?>
      <?php if (!empty($sidebarBottom['eyebrow'])): ?><span class="sidebar-message-eyebrow"><?= $sidebarEscape($sidebarBottom['eyebrow']) ?></span><?php endif; ?>
      <?php if (!empty($sidebarBottom['title'])): ?><strong><?= $sidebarEscape($sidebarBottom['title']) ?></strong><?php endif; ?>
      <?php if (!empty($sidebarBottom['body'])): ?><p><?= $sidebarEscape($sidebarBottom['body']) ?></p><?php endif; ?>
      <?php if (!empty($sidebarBottom['action_label'])): ?>
        <?php if (($sidebarBottom['action_type'] ?? 'link') === 'button'): ?>
          <button class="primary-btn"<?= !empty($sidebarBottom['action_id']) ? ' id="' . $sidebarEscape($sidebarBottom['action_id']) . '"' : '' ?> type="button"><?= $sidebarEscape($sidebarBottom['action_label']) ?> <span aria-hidden="true">&rarr;</span></button>
        <?php else: ?>
          <a class="primary-btn" href="<?= $sidebarEscape($sidebarBottom['action_href'] ?? '#') ?>"><?= $sidebarEscape($sidebarBottom['action_label']) ?> <span aria-hidden="true">&rarr;</span></a>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</aside>
<script>
(function () {
  const key = 'xinng.sidebar.collapsed';
  const sidebar = document.currentScript.previousElementSibling;
  if (!sidebar || !sidebar.classList.contains('workspace-sidebar')) return;
  const buttons = [...sidebar.querySelectorAll('.workspace-sidebar-edge-toggle')];
  const apply = collapsed => {
    collapsed = collapsed && !window.matchMedia('(max-width: 900px)').matches;
    document.body.classList.toggle('workspace-sidebar-collapsed', collapsed);
    buttons.forEach(button => { button.setAttribute('aria-expanded', collapsed ? 'false' : 'true'); button.setAttribute('aria-label', collapsed ? 'Expand navigation' : 'Collapse navigation'); button.title = collapsed ? 'Expand navigation' : 'Collapse navigation'; });
  };
  apply(localStorage.getItem(key) === '1');
  buttons.forEach(button => button.addEventListener('click', () => {
    const collapsed = !document.body.classList.contains('workspace-sidebar-collapsed');
    apply(collapsed);
    localStorage.setItem(key, collapsed ? '1' : '0');
  }));
  document.addEventListener('xinng:mobile-navigation', event => {
    const open = Boolean(event.detail && event.detail.open);
    document.body.classList.toggle('workspace-mobile-nav-open', open);
    sidebar.setAttribute('aria-hidden', open ? 'false' : 'true');
  });
  sidebar.querySelectorAll('a').forEach(link => link.addEventListener('click', () => {
    if (window.matchMedia('(max-width: 900px)').matches) document.dispatchEvent(new CustomEvent('xinng:mobile-navigation', {detail: {open: false}}));
  }));
  window.matchMedia('(max-width: 900px)').addEventListener('change', () => apply(localStorage.getItem(key) === '1'));
})();
</script>
<?php
unset($sidebarActive, $sidebarUserName, $sidebarDisplayName, $sidebarBottom, $sidebarEscape, $sidebarWords, $sidebarInitials, $sidebarUserId, $sidebarPdo, $sidebarRole, $sidebarItems, $sidebarSections, $sidebarPageChildren, $sidebarWalletChildren, $sidebarWord, $sidebarKey, $sidebarSectionLabel, $sidebarKeys, $sidebarItem, $sidebarChildKey, $sidebarChild);
?>
