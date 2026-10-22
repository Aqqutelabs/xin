<?php
declare(strict_types=1);
$workspaceHeaderTitle = trim((string)($workspaceHeaderTitle ?? 'Workspace')) ?: 'Workspace';
$workspaceHeaderSubtitle = trim((string)($workspaceHeaderSubtitle ?? ''));
$workspaceHeaderEscape = static fn ($value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
?>
<header class="main-header workspace-shell-header">
  <div class="header-title"><h1><?= $workspaceHeaderEscape($workspaceHeaderTitle) ?></h1><?php if ($workspaceHeaderSubtitle !== ''): ?><span><?= $workspaceHeaderEscape($workspaceHeaderSubtitle) ?></span><?php endif; ?></div>
  <div class="header-actions"><?php include __DIR__ . '/workspace-header-utilities.php'; ?></div>
</header>
<?php unset($workspaceHeaderTitle, $workspaceHeaderSubtitle, $workspaceHeaderEscape); ?>
